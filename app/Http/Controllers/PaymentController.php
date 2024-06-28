<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use GuzzleHttp\Client;
use Yajra\DataTables\DataTables;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ClientException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function showPaymentPage()
    {
        return view('payment');
    }

    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $api = new Api(config('razorpay.key'), config('razorpay.secret'));

        try {
            $order = $api->order->create([
                'receipt' => 'order_rcptid_' . uniqid(),
                'amount' => $request->amount * 100,
                'currency' => 'INR'
            ]);

            $orderId = $order['id'];

            Payment::create([
                'name' => $request->name,
                'email' => $request->email,
                'order_id' => $orderId,
                'amount' => $request->amount,
                'status' => false,
                'invoice_downloaded' => false,
            ]);

            return view('payment', ['orderId' => $orderId, 'amount' => $request->amount]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create order. ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request)
    {
        $signatureStatus = $this->signatureVerify(
            $request->razorpay_signature,
            $request->razorpay_payment_id,
            $request->razorpay_order_id
        );

        $payment = Payment::where('order_id', $request->razorpay_order_id)->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        try {
            if ($signatureStatus) {
                $payment->update([
                    'payment_id' => $request->razorpay_payment_id,
                    'signature' => $request->razorpay_signature,
                    'status' => true,
                ]);
                return response()->json(['success' => true, 'message' => 'Payment successful.']);
            } else {
                $payment->update([
                    'payment_id' => $request->razorpay_payment_id,
                    'signature' => $request->razorpay_signature,
                ]);
                return response()->json(['success' => false, 'message' => 'Payment verification failed.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to verify payment. ' . $e->getMessage()], 500);
        }
    }

    private function signatureVerify($_signature, $_paymentId, $_orderId)
    {
        try {
            $api = new Api(config('razorpay.key'), config('razorpay.secret'));
            $attributes  = [
                'razorpay_signature' => $_signature,
                'razorpay_payment_id' => $_paymentId,
                'razorpay_order_id' => $_orderId,
            ];
            $api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function index()
    {
        return view('payments.index');
    }

    public function getPayments(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('payments')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if ($row->invoice_downloaded) {
                        return '<button type="button" data-id="'.$row->id.'" class="btn btn-danger btn-sm download-invoice">Download Invoice</button>';
                    } elseif ($row->status) {
                        $invoiceButton = '<button type="button" data-id="'.$row->id.'" class="btn btn-danger btn-sm download-invoice">Download Invoice</button>';
                        return $invoiceButton;
                    } else {
                        $verifyButton = '<button type="button" data-id="'.$row->id.'" class="btn btn-info btn-sm check-status">Verify Status</button>';
                        $invoiceButton = '<button type="button" data-id="'.$row->id.'" class="btn btn-danger btn-sm download-invoice" style="display:none;">Download Invoice</button>';
                        return $verifyButton . ' ' . $invoiceButton;
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('payments.index');
    }

    public function getPaymentStatus($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $razorpayPaymentId = $payment->payment_id;

        try {
            $client = new Client();
            $response = $client->request('GET', "https://api.razorpay.com/v1/payments/{$razorpayPaymentId}", [
                'auth' => [config('razorpay.key'), config('razorpay.secret')],
                'query' => ['expand[]' => 'card']
            ]);

            $paymentDetails = json_decode($response->getBody(), true);
            $message = $paymentDetails['status'] == 'captured' ? 'Payment Successful' : 'Payment Failed';
            $cardDetails = $paymentDetails['card'];

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment_details' => $paymentDetails,
                'card_details' => $cardDetails,
            ]);

        } catch (ClientException $e) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch payment status.'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPaymentInvoice($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }
    
        $razorpayPaymentId = $payment->payment_id;
        try {
            $client = new Client();
            $response = $client->request('GET', "https://api.razorpay.com/v1/payments/{$razorpayPaymentId}", [
                'auth' => [config('razorpay.key'), config('razorpay.secret')]
            ]);
    
            $paymentDetails = json_decode($response->getBody(), true);
            $pdf = Pdf::loadView('invoice', ['paymentDetails' => $paymentDetails, 'payment' => $payment]);
            $payment->invoice_downloaded = true;
            $payment->save();
    
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="invoice.pdf"');
    
        } catch (ClientException $e) {
            Log::error('ClientException: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to fetch payment details.'], 500);
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markInvoiceDownloaded($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $payment->invoice_downloaded = true;
        $payment->save();

        return response()->json(['success' => true, 'message' => 'Invoice marked as downloaded.']);
    }

    public function test()
    {
        return 'Test route is working';
    }
}
