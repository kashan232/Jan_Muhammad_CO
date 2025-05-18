@include('admin_panel.include.header_include')
<style>
    /* ====== Common Styles (Screen + Print) ====== */

    .print-section .container {
        padding: 10px 15px;
    }

    .customer-name {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        line-height: 1.2;
        margin-bottom: 10px;
        /* Less space between tables */
    }

    .print-table th,
    .print-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
    }

    .print-header {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }

    /* ====== Print Specific Styles ====== */
    @media print {

        /* Show the print section */
        .print-section {
            display: block !important;
        }

        /* Remove margins/paddings of body and html */
        body,
        html {
            margin: 0;
            padding: 0;
        }

        /* Hide unwanted elements while printing */
        #daily-sale-div,
        #print-btn-sale,
        #daily-sale-head,
        .navbar-wrapper {
            display: none !important;
        }

        /* Layout for rows and columns */
        .print-section .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 0;
            /* No extra space between rows */
        }

        .print-section .col-print-6 {
            width: 48%;
            flex: 0 0 48%;
            padding: 5px;
            box-sizing: border-box;
            page-break-inside: avoid;
            margin-right: 2%;
            display: flex;
            flex-direction: column;
        }

        /* Remove right margin for every 2nd column */
        .print-section .row .col-print-6:nth-child(2n) {
            margin-right: 0;
        }

        /* If only one column exists in the row, make it full width */
        .print-section .row:has(.col-print-6:only-child) .col-print-6 {
            width: 100%;
            margin-right: 0;
        }

        /* Page settings */
        @page {
            size: auto;
            margin: 10mm;
        }
    }
</style>


<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title" id="daily-sale-head">Daily Sale</h6>
                </div>

                <div class="card shadow-lg p-4" id="daily-sale-div">
                    <div class="card-body">
                        <form id="customerSaleForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-dark w-100" id="filterSales">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <button onclick="window.print()" class="btn btn-danger" id="print-btn-sale">Print</button>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover shadow-sm rounded-3" style="width:100%">
                        <tbody id="salesTableBody" class="text-center">
                        </tbody>
                    </table>
                </div>

                <div class="print-section d-none" id="printSection">
                    <div class="container">
                        <div class="print-header">Daily Customer Sales Report</div>
                        <div class="text-center" id="printDateRange" style="font-size: 14px;"></div>

                        <div class="row" id="printContentRow">
                            <!-- Dynamic columns will be injected here -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script>
        $('#filterSales').on('click', function() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();

            if (!start || !end) {
                alert('Please select both start and end dates.');
                return;
            }

            $.ajax({
                url: '{{ route("daily.sales") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    start_date: start,
                    end_date: end
                },
                success: function(response) {
                    if (!response || response.length === 0) {
                        $('#salesTableBody').html('<tr><td colspan="11">No sales found for selected dates.</td></tr>');
                        return;
                    }

                    let grouped = {};
                    let grandLots = 0;
                    let grandWeight = 0;
                    let grandAmount = 0;

                    response.forEach(sale => {
                        let customer = sale.customer;
                        if (!grouped[customer]) grouped[customer] = [];
                        grouped[customer].push(sale);
                    });

                    let printContent = '';
                    let customerKeys = Object.keys(grouped);

                    for (let i = 0; i < customerKeys.length; i += 2) {
                        printContent += '<div class="row">';

                        let processCustomer = (customer) => {
                            let customerSales = grouped[customer];
                            let lotsHtml = '';
                            let totalLots = 0;
                            let totalWeight = 0;
                            let totalLotAmount = 0;

                            customerSales.forEach(item => {
                                lotsHtml += `
                                    <tr>
                                        <td>${item.quantity}</td>
                                        <td>${item.weight ? item.weight : '-'}</td>
                                        <td>${item.unit} (${item.unit_in})</td>
                                        <td>${item.price}</td>
                                        <td>${item.total}</td>
                                    </tr>
                                `;
                                totalLots += parseFloat(item.quantity);
                                if (item.weight) totalWeight += parseFloat(item.weight);
                                totalLotAmount += parseFloat(item.total);
                            });

                            grandLots += totalLots;
                            grandWeight += totalWeight;
                            grandAmount += totalLotAmount;

                            printContent += `
                                <div class="col-print-6">
                                    <div class="customer-name">${customer}</div>
                                    <table class="print-table">
                                        <thead>
                                            <tr>
                                                <th>Lots</th>
                                                <th>Weight</th>
                                                <th>U.In</th>
                                                <th>Rate</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>${lotsHtml}</tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end">Total Lots:</td>
                                                <td>${totalLots.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">Total Weight:</td>
                                                <td>${totalWeight.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">Total Amount:</td>
                                                <td>${totalLotAmount.toFixed(2)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `;
                        };

                        let customer1 = customerKeys[i];
                        let customer2 = customerKeys[i + 1];

                        if (customer1) processCustomer(customer1);
                        if (customer2) processCustomer(customer2);

                        printContent += '</div>';
                    }

                    printContent += `
                        <div class="col-print-12 mt-4">
                            <h5>Grand Totals</h5>
                            <table class="print-table">
                                <tfoot>
                                    <tr>
                                        <td><strong>Grand Total Lots:</strong></td>
                                        <td>${grandLots.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total Weight:</strong></td>
                                        <td>${grandWeight.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total Amount:</strong></td>
                                        <td>${grandAmount.toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;

                    $('#printContentRow').html(printContent);
                    $('#printDateRange').text(`From ${start} to ${end}`);
                    $('#printSection').removeClass('d-none');
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    </script>

</body>