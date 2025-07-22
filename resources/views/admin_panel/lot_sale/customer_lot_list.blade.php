@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    table th,
    table td {
        border: 1px solid #000;
        padding: 3px;
        text-align: center;
        font-weight: bold;
    }

    @media print {
        body {
            margin: 0;
            padding: 0;
        }

        #receiptArea {
            padding: 20mm 10mm 20mm 10mm !important;
            margin: 0 auto !important;
            width: 90% !important;
        }

        .invoice-box {
            page-break-inside: avoid;
            padding: 1mm !important;
            border: 1px solid #000;
        }

        /* Remove unnecessary elements on print */
        .page-wrapper,
        .navbar,
        .sidebar,
        .body-wrapper> :not(#receiptArea):not(#printReceipt) {
            display: none !important;
        }

        #printReceipt {
            display: none !important;
        }
    }
</style>

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary">Customer Sale Ledger</h4>
                </div>

                <!-- Form -->
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <form id="customerSaleForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Select Customer</label>
                                    <select name="category" class="select2-basic form-control" id="customer" required>
                                        <option value="">Select Customer</option>
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
                    </div>
                </div>

                <!-- Ledger -->
                <div id="salesLedger" class="mt-4"></div>
                <div class="mt-3 text-end">
                    <h4 class="fw-bold">Grand Total: <span id="grandTotal">0</span> PKR</h4>
                </div>

                <!-- Receipt Output -->
                <button id="printReceipt" class="btn btn-danger">Print Receipt</button>

                <div id="receiptArea" class="mt-5" style="margin-top: 20px;"></div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script>
        $(document).ready(function() {
            $("#filterSales").click(function() {
                var customerId = $("#customer").val();
                var startDate = $("#start_date").val();
                var endDate = $("#end_date").val();
                var customerName = $("#customer option:selected").data("name-urdu");

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

                        response.sales.forEach(function(sale) {
                            if (!groupedSales[sale.sale_date]) {
                                groupedSales[sale.sale_date] = {
                                    sales: [],
                                    subtotal: 0
                                };
                            }
                            groupedSales[sale.sale_date].sales.push(sale);
                            groupedSales[sale.sale_date].subtotal += parseFloat(sale.total);
                            grandTotal += parseFloat(sale.total);
                        });

                        // Ledger HTML Generation (same as before)
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
                                                <th>Weight</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                            dailySales.sales.forEach(function(sale) {
                                ledgerHtml += `
                                        <tr>
                                            <td>${sale.truck_number}</td>
                                            <td>${sale.driver_name}</td>
                                            <td>${sale.category}</td>
                                            <td>${sale.variety}</td>
                                            <td>${sale.unit}</td>
                                            <td>${sale.quantity}</td>
                                            <td>${sale.weight}</td>
                                            <td>${sale.price}</td>
                                            <td>${sale.total}</td>
                                        </tr>`;
                            });

                            ledgerHtml += `</tbody></table></div>
                                <h5 class="fw-bold text-end">Subtotal: ${dailySales.subtotal.toFixed(2)} PKR</h5>
                            </div>
                        </div>`;
                        });
                        $("#salesLedger").html(ledgerHtml);
                        $("#grandTotal").text(grandTotal.toFixed(2));

                        // Urdu Receipt Section (Modified to use final_balance)
                        var previousBalance = parseFloat(response.previous_balance || 0);
                        var recoveryAmount = parseFloat(response.total_recovery || 0);
                        var finalBalance = parseFloat(response.final_balance || 0); // Get final balance
                        var closingBalance = parseFloat(response.closing_balance || 0);
                        var receiptHtml = `
<div class="invoice-box" style="direction: rtl; text-align: right; border: 2px solid #000; max-width: 360px; margin: 0 auto; padding: 10px; font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', sans-serif;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="d-flex flex-column text-start">
            <span style="font-weight: bold;">03223014221</span>
            <span style="font-weight: bold;">03213022033</span>
        </div>
        <img src="logo-bill.png" alt="Logo" style="max-height: 75px;">
        <div class="d-flex flex-column text-end">
            <span style="font-weight: bold;">03213061917</span>
            <span style="font-weight: bold;">03118661606</span>
        </div>
    </div>

    <div class="header-info row">
        <div class="col">نام: ${customerName}</div>
        <div class="col text-start">
            <div>سے: ${startDate}</div>
            <div>تک: ${endDate}</div>
        </div>
    </div>
    <div style="background: #e9ecef; padding: 5px; text-align: center; border: 1px solid #000; font-weight: bold;">
        فروخت کا خلاصہ
    </div>`;

                        Object.keys(groupedSales).forEach(function(date) {
                            var dailySales = groupedSales[date];
                            receiptHtml += `
    <div style="margin-top: 10px;">
        <div style="font-weight: bold; text-decoration: underline;">تاریخ: ${date}</div>
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 13px; margin-top: 5px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000;">نگ</th>
                    <th style="border: 1px solid #000;">وزن</th>
                    <th style="border: 1px solid #000;">ریٹ</th>
                    <th style="border: 1px solid #000;">رقم</th>
                </tr>
            </thead>
            <tbody>`;

                            dailySales.sales.forEach(function(sale) {
                                let weightDisplay = sale.weight !== null ? sale.weight : "";
                                receiptHtml += `
            <tr>
                <td style="border: 1px solid #000;">${sale.quantity}</td>
                <td style="border: 1px solid #000;">${weightDisplay}</td>
                <td style="border: 1px solid #000;">${sale.price}</td>
                <td style="border: 1px solid #000;">${parseFloat(sale.total).toFixed(2)}</td>
            </tr>`;
                            });

                            receiptHtml += `
            <tr>
                <td colspan="3" style="border: 1px solid #000; text-align: center;"><strong>ٹوٹل</strong></td>
                <td style="border: 1px solid #000;"><strong>${dailySales.subtotal.toFixed(2)}</strong></td>
            </tr>
        </tbody>
        </table>
    </div>`;
                        });
                        var totalLots = 0;
                        Object.values(groupedSales).forEach(function(daily) {
                            daily.sales.forEach(function(sale) {
                                totalLots += parseFloat(sale.quantity || 0);
                            });
                        });
                        receiptHtml += `
    <table style="width:100%; font-size: 13px; margin-top: 10px;">
        <tr class="totals">
            <td colspan="2">کل نگ: ${totalLots}</td>
            <td>ٹوٹل نامہ رقم</td>
            <td>${grandTotal.toFixed(2)}</td>
        </tr>
        <tr class="totals">
            <td colspan="2"></td>
            <td>سابقہ بیلنس</td>
            <td>${previousBalance.toFixed(2)}</td>
        </tr>
        <tr class="totals">
            <td colspan="2"></td>
            <td>ٹوٹل</td>
            <td>${(previousBalance + grandTotal).toFixed(2)}</td>
        </tr>
        <tr class="totals">
            <td colspan="2"></td>
            <td>وصُولی</td>
            <td>${recoveryAmount.toFixed(2)}</td>
        </tr>
        <tr class="totals" style="background-color: #d1e7dd;">
            <td colspan="2"></td>
            <td><strong>بقایا بیلنس</strong></td>
            <td><strong>${closingBalance.toFixed(2)}</strong></td>
        </tr>
    </table>
    <div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid black; font-size: 12px; text-align: center;">
    <strong> Designed & Developed by ProWave Software Solutions</strong> +92 317 3836223 | +92 317 3859647
</div>

</div>`;
                        $("#receiptArea").html(receiptHtml);
                    },
                    error: function() {
                        alert("Error fetching data");
                    }
                });
            });

            $("#printReceipt").click(function() {
                var printContents = document.getElementById("receiptArea").innerHTML;
                var originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
            });
        });
    </script>



</body>