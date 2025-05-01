@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary">Customer Sale Ledger</h4>
                </div>

                <!-- Customer Selection Form -->
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <form id="customerSaleForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select Customer</label>
                                    <select class="form-control" id="customer">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" data-number="{{ $customer->customer_phone }}">
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
                    </div>
                </div>

                <!-- Sale Details Table -->
                <div id="salesLedger" class="mt-4"></div>
                
                <div class="mt-3 text-end">
                    <h4 class="fw-bold">Grand Total: <span id="grandTotal">0</span> PKR</h4>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <!-- AJAX Script -->
    <script>
        $(document).ready(function() {
            $("#filterSales").click(function() {
                var customerId = $("#customer").val();
                var startDate = $("#start_date").val();
                var endDate = $("#end_date").val();

                if (!customerId) {
                    alert("Please select a customer.");
                    return;
                }

                $.ajax({
                    url: "{{ route('customer.lots') }}",
                    type: "GET",
                    data: { 
                        customer_id: customerId,
                        start_date: startDate,
                        end_date: endDate
                    },
                   success: function(response) {
    var groupedSales = {};
    var grandTotal = 0;

    // Group sales by date
    response.sales.forEach(function(sale) {
        if (!groupedSales[sale.sale_date]) {
            groupedSales[sale.sale_date] = { sales: [], subtotal: 0 };
        }
        groupedSales[sale.sale_date].sales.push(sale);
        groupedSales[sale.sale_date].subtotal += parseFloat(sale.total);
        grandTotal += parseFloat(sale.total);
    });

    // Build Sales Ledger UI
    var ledgerHtml = "";
    Object.keys(groupedSales).forEach(function(date) {
        var dailySales = groupedSales[date];
        ledgerHtml += `
            <div class="card shadow-lg mb-4">
                <div class="card-body">
                    <h5 class="text-secondary border-bottom pb-2">Sales on ${date}</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Truck Number</th>
                                    <th>Driver Name</th>
                                    <th>Category</th>
                                    <th>Variety</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>`;
        
        dailySales.sales.forEach(function(sale) {
            ledgerHtml += `<tr>
                <td>${sale.truck_number}</td>
                <td>${sale.driver_name}</td>
                <td>${sale.category}</td>
                <td>${sale.variety}</td>
                <td>${sale.unit}</td>
                <td>${sale.quantity}</td>
                <td>${sale.price}</td>
                <td>${sale.total}</td>
            </tr>`;
        });

        ledgerHtml += `</tbody></table></div>
                        <h5 class="fw-bold text-end">Subtotal: ${dailySales.subtotal} PKR</h5>
                    </div>
                </div>`;
    });

    // Receipt Data Calculation
    var previousBalance = parseFloat(response.previous_balance || 0);
    var recoveryAmount = parseFloat(response.total_recovery || 0);
    var total = previousBalance + grandTotal;
    var netAmount = total - recoveryAmount;

    ledgerHtml += `
        <div class="card shadow-lg">
            <div class="card-body">
                <h5 class="fw-bold">Receipt Summary</h5>
                <p>Previous Balance: <strong>${previousBalance} PKR</strong></p>
                <p>Sales Total: <strong>${grandTotal} PKR</strong></p>
                <p>Total (Balance + Sales): <strong>${total} PKR</strong></p>
                <p>Recovery Received: <strong>${recoveryAmount} PKR</strong></p>
                <p class="text-primary fs-5">Net Amount: <strong>${netAmount} PKR</strong></p>
            </div>
        </div>`;

    $("#salesLedger").html(ledgerHtml);
    $("#grandTotal").text(grandTotal);
}
,
                    error: function() {
                        alert("Error fetching data");
                    }
                });
            });
        });
    </script>
</body>
