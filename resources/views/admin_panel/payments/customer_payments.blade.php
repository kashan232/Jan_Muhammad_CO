@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Customer Payments</h6>
                </div>

                <div class="alert alert-success">
                    <strong>Success!</strong> {{ session('success') }}.
                </div>
                @endif
                <div class="container bg-white p-4 shadow rounded">
                    <h4 class="text-center mb-4">Customer Payment Form</h4>

                    <form action="{{ route('customer.payment.store') }}" method="POST">
                        @csrf
                        {{-- Customer and Payment Amount --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer" class="form-label text-dark">Received From <span class="text-danger">*</span></label>
                                <select id="customer" name="customer_id" class="form-select">
                                    <option selected disabled>Select Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
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
                            Customer Balance: <span id="customer_balance" class="text-dark">PKR 0</span>
                        </div>


                        <div class="table-responsive mb-4">
                            <label class="form-label text-dark d-block mb-2">Previous Payments</label>
                            <table class="table table-bordered" id="sales_table">
                                <thead class="table-success">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount (PKR)</th>
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
                {{-- Customer Payment Form End --}}
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        const routeTemplate = "{{ route('get.customer.balance', ['id' => 'CUSTOMER_ID']) }}";

        document.getElementById('customer').addEventListener('change', function() {
            let customerId = this.value;
            let url = routeTemplate.replace('CUSTOMER_ID', customerId);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('customer_balance').innerText = 'PKR ' + data.balance;

                    let tbody = document.querySelector('#sales_table tbody');
                    tbody.innerHTML = '';

                    if (data.sales && data.sales.length > 0) {
                        data.sales.forEach(sale => {
                            tbody.innerHTML += `
                            <tr>
                                <td>${sale.sale_date}</td>
                                <td>${sale.total}</td>
                            </tr>
                        `;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="2">No Sales Found</td></tr>';
                    }
                });
        });
    </script>
</body>