<!doctype html>
<html lang="en">

<head>
    <title>Invoice</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="css/style.css" media="print">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
</head>

<body>

    <div class="container invoice-container">
        <div class="row">
            <div class="col-md-12">

            </div>
        </div>
        <div class="row">
            <div class="col-md-4 text-center ">
                <h1 class="font-weight-bold">INVOICE</h1>
                <hr />
            </div>
            <div class="col-md-4 d-flex">

                <center> <img src="images/papaya.png" alt="" height="40" width="80"></center>
                <center>
                    <h3>Papaya Coders Pvt. Ltd.</h3>
                </center>
                <hr />
                <div class="text-left">
                    <p><b>Address:</b> 123, 1st Main, 1st Cross,</p>
                    <p>Matiyari, Lucknow</p>
                    <p><b>Phone:</b> 9087654321</p>
                    <p><b>Email:</b> papayacoders@gmail.com</p>
                </div>
            </div>

            <div class="col-md-4 text-left">
                <p><b>Invoice No:</b> 123456</p>
                <p><b>Date:</b> {{ date('d/m/Y') }}</p>
                <p><b>Billed To:</b> {{ $payment->name }}</p>
            </div>

        </div>

        <div class="row ">

            <div class="table-responsive">
                <table class="table  ">
                    <tbody>
                        <tr>
                            <td class="font-weight-bold">User Email : </td>
                            <td>{{ $payment->email }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Order Id :</td>
                            <td>{{ $paymentDetails['order_id'] }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Payment Id :</td>
                            <td>{{ $paymentDetails['id'] }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Amount :</td>
                            <td>{{ $paymentDetails['amount'] / 100 }} {{ $paymentDetails['currency'] }}</td>
                        </tr>
                        @if (isset($paymentDetails['card']))
                            <tr>
                                <td class="font-weight-bold">Card Details :</td>
                                <td>{{ $paymentDetails['card']['network'] }} {{ $paymentDetails['card']['last4'] }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="font-weight-bold ml-5">Status :</td>
                            <td>{{ $paymentDetails['status'] }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="row">
            <div class="col-md-6 ">
                <h5 class=" ">Note To Buyer : </h5>
                <hr />
                <p class="">It was wonderful doing business with you, and look forward to working with you again.
                    Thank you for being an amazing client.</p>

            </div>
            <div class="col-md-6">
                <img class=" " src="images/sign.png" alt="" height="80" width="190">
                <hr />
                <h5 class=" ">Owner Signature : </h5>

            </div>

        </div>

    </div>






</body>
