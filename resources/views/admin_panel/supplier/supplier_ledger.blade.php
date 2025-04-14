@include('admin_panel.include.header_include')

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">

        <!-- sidebar start -->

        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Vendors Ledger</h6>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table id="example" class="display  table table--light style--two bg--white" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Vendor Name</th>
                                                <th>Opening Balance</th>
                                                <th>Previous Balance</th>
                                                <th>Closing Balance</th>
                                                <th>Action</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($SupplierLedgers as $ledger)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $ledger->supplier->name ?? 'N/A' }}</td>
                                                <td>{{ $ledger->opening_balance }}</td>
                                                <td>{{ $ledger->previous_balance }}</td>
                                                <td>{{ $ledger->closing_balance }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary paymentBtn"
                                                        data-supplier-id="{{ $ledger->supplier_id }}"
                                                        data-closing="{{ $ledger->closing_balance }}"
                                                        data-name="{{ $ledger->supplier->name }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentModal">
                                                        Payment
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>


                                    </table><!-- table end -->
                                </div>
                            </div>
                        </div><!-- card end -->
                    </div>
                </div>
            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>


    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="supplierPaymentForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Make Payment to <span id="supplierName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="supplier_id" id="supplierId">
                        <div class="mb-2">
                            <label>Closing Balance</label>
                            <input type="text" class="form-control" id="closingBalance" readonly>
                        </div>
                        <div class="mb-2">
                            <label>Amount Paid</label>
                            <input type="number" class="form-control" name="amount_paid" id="amountPaid" required>
                        </div>
                        <div class="mb-2">
                            <label>Date</label>
                            <input type="date" class="form-control" name="payment_date" id="paymentDate" required>
                        </div>
                        <div class="mb-2">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" id="paymentDescription"></textarea>
                        </div>
                        <div class="mb-2">
                            <label>Remaining Balance</label>
                            <input type="text" class="form-control" id="remainingBalance" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit Payment</button>
                    </div>
                </div>
            </form>

        </div>
    </div>


    @include('admin_panel.include.footer_include')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.paymentBtn').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.dataset.supplierId;
                const closing = parseFloat(this.dataset.closing);
                const name = this.dataset.name;

                document.getElementById('supplierId').value = supplierId;
                document.getElementById('closingBalance').value = closing;
                document.getElementById('supplierName').textContent = name;

                // Reset values
                document.getElementById('amountPaid').value = '';
                document.getElementById('remainingBalance').value = '';
            });
        });

        document.getElementById('amountPaid').addEventListener('input', function() {
            const paid = parseFloat(this.value) || 0;
            const closing = parseFloat(document.getElementById('closingBalance').value) || 0;
            const remaining = closing - paid;
            document.getElementById('remainingBalance').value = remaining.toFixed(2);
        });
    </script>

    <script>
        $('#supplierPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).text('Processing...');

            const formData = {
                _token: '{{ csrf_token() }}',
                supplier_id: $('#supplierId').val(),
                amount_paid: $('#amountPaid').val(),
                payment_date: $('#paymentDate').val(),
                description: $('#paymentDescription').val(),
            };

            $.ajax({
                url: '{{ route("supplier-payment-store") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful',
                        text: response.message,
                        timer: 5000,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didClose: () => {
                            // SweetAlert band hone ke baad modal band ho
                            $('#paymentModal').modal('hide');
                            setTimeout(() => {
                                location.reload();
                            }, 1000); // 1 sec baad page reload
                        }
                    });
                },
                error: function(xhr) {
                    let msg = 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                    });
                    $btn.prop('disabled', false).text('Submit Payment');
                }
            });
        });
    </script>