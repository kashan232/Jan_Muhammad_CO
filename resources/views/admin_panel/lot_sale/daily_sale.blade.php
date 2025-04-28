@include('admin_panel.include.header_include')
<style>
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
        margin-bottom: 20px;
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

    @media print {
        .print-section {
            display: block !important;
        }

        body,
        html {
            margin: 0;
            padding: 0;
        }

        #daily-sale-div,
        #print-btn-sale {
            display: none;
        }

        #daily-sale-head {
            display: none;
        }

        .col-print-6 {
            width: 50%;
            float: left;
            padding: 5px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .navbar-wrapper {
            display: none !important;
        }

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

                <button onclick="window.print()" class="btn btn-primary my-3" id="print-btn-sale">Print</button>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover shadow-sm rounded-3" style="width:100%">
                        <tbody id="salesTableBody" class="text-center">
                        </tbody>
                    </table>
                </div>

                <div class="print-section d-none" id="printSection">
                    <div class="container mt-3">
                        <div class="print-header">Daily Customer Sales Report</div>
                        <div class="text-center mb-3" id="printDateRange" style="font-size: 14px;"></div>

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
                        $('#salesTableBody').html('<tr><td colspan="10">No sales found for selected dates.</td></tr>');
                        return;
                    }

                    let grouped = {};
                    let grandLots = 0;
                    let grandAmount = 0;

                    response.forEach(sale => {
                        let customer = sale.customer;
                        if (!grouped[customer]) grouped[customer] = [];
                        grouped[customer].push(sale);
                    });

                    let printContent = '';

                    Object.keys(grouped).forEach((customer, index) => {
                        let customerSales = grouped[customer];
                        let lotsHtml = '';
                        let cashHtml = '';
                        let totalLots = 0;
                        let totalCashAmount = 0;
                        let totalLotAmount = 0;

                        customerSales.forEach(item => {
                            if (item.type === 'cash') {
                                cashHtml += `
                    <tr>
                        <td>${item.date}</td>
                        <td>${item.description}</td>
                        <td>${parseFloat(item.amount).toFixed(2)}</td>
                    </tr>
                `;
                                totalCashAmount += parseFloat(item.amount);
                            } else {
                                lotsHtml += `
                    <tr>
                        <td>${item.quantity}</td>
                        <td>${item.unit} (${item.unit_in})</td>
                        <td>${item.price}</td>
                        <td>${item.total}</td>
                    </tr>
                `;
                                totalLots += parseFloat(item.quantity);
                                totalLotAmount += parseFloat(item.total);
                            }
                        });

                        let totalAmount = totalCashAmount + totalLotAmount;

                        grandLots += totalLots;
                        grandAmount += totalAmount;

                        let printCard = `
            <div class="col-print-6">
                <div class="customer-name">${customer}</div>
                ${lotsHtml ? `
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>Lots</th>
                                <th>U.In</th>
                                <th>Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>${lotsHtml}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">Total Lots:</td>
                                <td>${totalLots.toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Total Amount:</td>
                                <td>${totalAmount.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>` : ''
                }

                ${cashHtml ? `
                    <div class="customer-name">Cash</div>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>${cashHtml}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total Amount:</td>
                                <td>${totalCashAmount.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>` : ''
                }
            </div>
        `;

                        printContent += printCard;
                    });

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