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
        <!-- Recovery Modal -->
        <div class="modal fade" id="recoveryModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="recoveryModalLabel">Add Recovery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="recoveryForm">
                            @csrf
                            <input type="hidden" id="ledger_id" name="ledger_id">
                            <div class="mb-3">
                                <label for="closing_balance" class="form-label">Closing Balance</label>
                                <input type="text" class="form-control" id="closing_balance" name="closing_balance" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Amount Paid</label>
                                <input type="number" class="form-control" id="amount_paid" name="amount_paid" required>
                            </div>
                            <div class="mb-3">
                                <label for="salesman" class="form-label">Salesman</label>
                                <input type="text" class="form-control" id="salesman" name="salesman" required>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <button type="submit" class="btn btn-success">Save Recovery</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="body-wrapper">
            <div class="bodywrapper__inner">

                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Customers Ledgers</h6>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table id="example" class="display  table table--light" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Phone Number</th>
                                                <th>Previous Balance</th>
                                                <th>Closing Balance</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($CustomerLedgers as $ledger)
                                            <tr>
                                                <td>{{ $ledger->customer_id }}</td>
                                                <td>{{ $ledger->Customer->customer_name }}</td>
                                                <td>{{ $ledger->Customer->customer_phone }}</td>
                                                <td>{{ number_format($ledger->previous_balance, 0) }}</td>
                                                <td id="closing_balance_{{ $ledger->id }}">{{ number_format($ledger->closing_balance, 0) }}</td>
                                                <td>{{ $ledger->updated_at->format('Y-m-d H:i:s') }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recoveryModal"
                                                        data-id="{{ $ledger->id }}"
                                                        data-closing-balance="{{ $ledger->closing_balance }}">
                                                        Add Recovery
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>
    @include('admin_panel.include.footer_include')
    
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var recoveryModal = document.getElementById('recoveryModal');
        recoveryModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ledgerId = button.getAttribute('data-id');
            var closingBalance = button.getAttribute('data-closing-balance');

            document.getElementById('ledger_id').value = ledgerId;
            document.getElementById('closing_balance').value = closingBalance;
        });

        document.getElementById('recoveryForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            fetch("", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    var ledgerId = document.getElementById('ledger_id').value;
                    var newClosingBalance = data.new_closing_balance;
                    document.getElementById('closing_balance_' + ledgerId).innerText = newClosingBalance;
                    var recoveryModal = bootstrap.Modal.getInstance(document.getElementById('recoveryModal'));
                    recoveryModal.hide();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
</script>
