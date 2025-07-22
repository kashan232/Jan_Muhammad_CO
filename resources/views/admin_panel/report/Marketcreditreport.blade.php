<!-- meta tags and other links -->
@include('admin_panel.include.header_include')

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Market Credit Report</h6>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card p-4">

                            <form id="creditSearchForm">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Select Customer</label>
                                        <select name="category" class="select2-basic form-control" id="customer" required>
                                            <option value="All">All</option>
                                            @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                data-name="{{ $customer->customer_name }}"
                                                data-name-urdu="{{ $customer->customer_name_urdu }}">
                                                {{ $customer->customer_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date">
                                    </div>
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary w-100" id="filterSales">Search</button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-end mt-2">
                                <button id="downloadPdf" class="btn btn-danger">
                                    Download PDF
                                </button>
                            </div>
                            <div id="customer-summary-table" class="mt-4"></div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin_panel.include.footer_include')
    <Script>
        $('#filterSales').click(function() {
            var customerId = $('#customer').val();
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            $.ajax({
                type: 'POST',
                url: "{{ route('get.customer.ledger.summary') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    customer_id: customerId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    if (response.success) {
                        let rows = '';
                        let grandTotal = 0;

                        response.data.forEach(customer => {
                            rows += `
                        <tr>
                            <td>${customer.customer_name}</td>
                            <td>${customer.customer_name_urdu}</td>
                            <td>Rs. ${customer.closing_balance}</td>
                        </tr>
                    `;
                            grandTotal += customer.closing_balance;
                        });

                        let tableHtml = `
                    <table class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Customer Name Urdu</th>
                                <th>Closing Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Grand Total</th>
                                <th>Rs. ${grandTotal}</th>
                            </tr>
                        </tfoot>
                    </table>
                `;

                        $('#customer-summary-table').html(tableHtml);
                    } else {
                        alert(response.message || 'Error occurred.');
                    }
                },
                error: function() {
                    alert('AJAX Error!');
                }
            });
        });
    </Script>