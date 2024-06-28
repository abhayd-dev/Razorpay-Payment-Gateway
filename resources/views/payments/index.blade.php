<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>
    <div class="container-fluid mt-2">
        <div class="col-sm-12 text-center">
            <h2 class="text-danger d-inline">Payments</h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <a class="btn btn-primary mb-3 ml-3" href="{{ url('/') }}">Back To Payment</a>
            <table class="table table-bordered yajra-datatable table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        {{-- <th>Email</th> --}}
                        <th>Order ID</th>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="paymentStatusModal" tabindex="-1" role="dialog"
        aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bolder text-success" id="paymentStatusModalLabel">Payment Status
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body text-success" id="paymentStatusContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('.yajra-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('payments.get') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'order_id',
                        name: 'order_id'
                    },
                    {
                        data: 'payment_id',
                        name: 'payment_id'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            var actionHtml = '';
                            if (full.invoice_downloaded) {
                                actionHtml += '<span class="badge badge-success">Verified</span>';
                            } else {
                                actionHtml += '<button type="button" id="vbtn" data-id="' + full
                                    .id +
                                    '" class="btn btn-sm check-status"><i class="fas fa-check mr-2"></i>Verify Status</button>';
                            }
                            actionHtml += ' <button type="button" id="dbtn" data-id="' + full.id +
                                '" class="btn btn-sm download-invoice" ' + (full
                                    .invoice_downloaded ? '' : 'style="display:none;"') +
                                '><i class="fas fa-download mr-2"></i>Download Invoice</button>';
                            return actionHtml;
                        }
                    }
                ]
            });

            $(document).on('click', '.check-status', function() {
                var paymentId = $(this).data('id');
                var button = $(this);
                $.ajax({
                    url: "{{ route('payment.status', ':id') }}".replace(':id', paymentId),
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Verified',
                                text: 'This Payment is Successfully paid. Now you can download your invoice',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            button.hide();
                            button.next('.download-invoice').show();
                        } else {
                            Swal.fire({
                                title: 'Failed',
                                text: 'This Payment is Failed And Not Able to fetch payment status.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Oops! Payment failed..',
                            text: 'This Payment has failed. You can pay again.',
                            icon: 'error',
                            confirmButtonText: 'Close',
                            showCloseButton: true,
                            html: '<a href="{{ route('payment.page') }}" class="btn btn-success  border-0">Pay Again</a>'
                        });
                    }
                });
            });

            $(document).on('click', '.download-invoice', function() {
                var paymentId = $(this).data('id');
                var button = $(this);
                $.ajax({
                    url: "{{ route('payment.invoice', ':id') }}".replace(':id', paymentId),
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        var blob = new Blob([data], {
                            type: 'application/pdf'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'invoice.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        Swal.fire({
                            title: 'Invoice Downloaded',
                            text: 'Invoice PDF Downloaded Successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            var row = table.row(button.closest('tr'));
                            row.data().invoice_downloaded = true;
                            row.invalidate().draw(false);
                        });

                        $.ajax({
                            url: "{{ route('payment.markInvoiceDownloaded', ':id') }}"
                                .replace(':id', paymentId),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (!response.success) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Unable to mark invoice as downloaded.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            }
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Failed',
                            text: 'Failed to download invoice. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>


</body>

</html>
