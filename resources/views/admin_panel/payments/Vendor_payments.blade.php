@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Vendor Payments</h6>
                </div>
                @if (session()->has('success'))
                <div class="alert alert-success">
                    <strong>Success!</strong> {{ session('success') }}.
                </div>
                @endif

                <div class="container bg-white p-4 shadow rounded">
                    <h4 class="text-center mb-4">Vendor Payment Form</h4>

                    <form action="{{ route('vendor-payment-store') }}" method="POST">
                        @csrf

                        {{-- Vendor and Payment Amount --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="supplier" class="form-label text-dark">Received From <span class="text-danger">*</span></label>
                                <select id="supplier" name="supplier_id" class="form-select">
                                    <option selected disabled>Select Vendor</option>
                                    @foreach($Suppliers as $Supplier)
                                    <option value="{{ $Supplier->id }}">{{ $Supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label text-dark">Payment Amount (PKR) <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" placeholder="Enter payment amount">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label text-dark">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="bank" class="form-label text-dark">Bank <span class="text-danger">*</span></label>
                                <input type="text" id="bank" name="bank" class="form-control" placeholder="Enter bank or transaction reference">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="detail" class="form-label text-dark">Payment Method Details (e.g. JazzCash, EasyPaisa)</label>
                            <input type="text" id="detail" name="detail" class="form-control" placeholder="Enter additional payment details">
                        </div>

                        <div class="text-end fw-bold text-secondary mb-3">
                            Vendor Balance: <span id="supplier_balance" class="text-dark">PKR 0</span>
                        </div>

                        <div class="table-responsive mb-4">
                            <label class="form-label text-dark d-block mb-2">Vendor Past Payments</label>
                            <table class="table table-bordered" id="sales_table">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount Paid (PKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- AJAX will populate this --}}
                                </tbody>
                            </table>
                        </div>




                        {{-- Buttons --}}
                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success">Save & Close</button>
                            <button type="submit" class="btn btn-primary">Save & Add New</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script>
        const routeTemplate = "{{ route('get-Vendor-balance', ['id' => 'SUPPLIER_ID']) }}";
        document.addEventListener('DOMContentLoaded', function() {
            const routeTemplate = "{{ route('get-Vendor-balance', ['id' => 'SUPPLIER_ID']) }}";

            document.getElementById('supplier').addEventListener('change', function() {
                let supplierId = this.value;
                let url = routeTemplate.replace('SUPPLIER_ID', supplierId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('supplier_balance').innerText = 'PKR ' + data.balance;

                        // ✅ Populate bills table (Corrected selector)
                        let billsTbody = document.querySelector('#sales_table tbody');
                        if (!billsTbody) {
                            console.error('Sales table body not found');
                            return;
                        }

                        billsTbody.innerHTML = '';

                        if (data.bills && data.bills.length > 0) {
                            data.bills.forEach(bill => {
                                billsTbody.innerHTML += `
                                <tr>
                                    <td>${new Date(bill.created_at).toLocaleDateString()}</td>
                                    <td>${bill.net_pay}</td>
                                </tr>
                            `;
                            });
                        } else {
                            billsTbody.innerHTML = '<tr><td colspan="2">No Past Payments Found</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching supplier data:', error);
                    });
            });
        });
    </script>
</body>