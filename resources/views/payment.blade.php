<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <div class="card col-sm-6 mx-auto mt-3  border-success border-12">
        <div class=" text-center mt-4">
           <img class="mb-3" src="images/payment.png" height="45" width="45" alt=""><span class="text-info ml-3 font-weight-bold" style="font-size: 40px;">Payment Here</span>
           <hr/>
        </div>
        <div class="card-body">
    <form action="{{ route('create.order') }}" method="POST" >
        @csrf

        <div class="form-group col-sm-8 mx-auto mt-1">
        <label for="name" class="text-info font-weight-bold">Name:</label>
        <input class="form-control border-success"  type="text" id="name" name="name" placeholder="Enter Name.......">
        </div>
        <div class="form-group col-sm-8 mx-auto mt-5">
        <label for="email" class="text-info font-weight-bold">Email :</label>
        <input class="form-control  border-success"  type="email" id="email" name="email" placeholder="Enter Email.......">
        </div>
        <div class="form-group col-sm-8 mx-auto mt-5 ">
        <label for="amount" class="text-info font-weight-bold">Payble Amount:</label>
        <input class="form-control border-success"  type="float" id="amount" name="amount" placeholder="Enter Amount......">
        </div>
        <div class="card-footer form-group col-sm-8  mx-auto mt-5 mb-5">
        <button class=" btn btn-lg btn-success col-sm-12" type="submit">Pay Now</button>
        
        </div>
        <hr/>
    </form>
</div>
</div>

    @isset($orderId)
    <script>
        var options = {
            "key": "{{ config('razorpay.key') }}",
            "amount": "{{ $amount * 100 }}", 
            "currency": "INR",
            "name": "Papaya Coders",
            "description": "Test Transaction",
            "order_id": "{{ $orderId }}",
            "image": "https://papayacoders.in/wp-content/uploads/2023/06/papayacoders-logo-e1686920327207.png.webp",
            "handler": function (response) {
                $.ajax({
                    url: "{{ route('payment.success') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    },
                    success: function (data) {
                        if (data.success) {
                            Swal.fire({
                                title: 'Payment Successful',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('payments.index') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Payment Failed',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('payments.index') }}";
                                }
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Payment Failed',
                            text: 'There was an error processing your payment.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('payments.index') }}";
                            }
                        });
                    }
                });
            },
            "modal": {
                "ondismiss": function() {
                    Swal.fire({
                        title: 'Payment Cancelled',
                        text: 'You have cancelled the payment.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('payments.index') }}";
                        }
                    });
                }
            }
        };

        var rzp1 = new Razorpay(options);
        rzp1.open();
    </script>
    @endisset
</body>
</html>
