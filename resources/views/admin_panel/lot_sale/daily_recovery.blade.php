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
        margin-bottom: 10px;
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
        margin-bottom: 5px;
    }

    .print-date-range {
        text-align: center;
        font-size: 14px;
        margin-bottom: 10px;
    }

    @media print {
        .print-section {
            display: block !important;
        }
        body, html {
            margin: 0;
            padding: 0;
        }
        #daily-recovery-div, #print-btn-recovery, #daily-recovery-head, .navbar-wrapper {
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
                <h6 class="page-title mt-2 mb-2" id="daily-recovery-head">Daily Recoveries</h6>
                <div class="card shadow-lg p-4" id="daily-recovery-div">
                    <div class="card-body">
                        <form id="customerRecoveryForm">
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
                                    <button type="button" class="btn btn-dark w-100" id="filterRecoveries">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <button onclick="window.print()" class="btn btn-danger mt-2 mb-2" id="print-btn-recovery">Print</button>

                <div class="print-section" id="printSection">
                    <div class="container">
                        <div class="print-header">Daily Customer Recoveries Payment</div>
                        <div class="print-date-range" id="printDateRange"></div>
                        <table class="print-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody id="printContentRow"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total Recoveries:</strong></td>
                                    <td id="printTotalRecoveryAmount"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        $('#filterRecoveries').on('click', function() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();

            if (!start || !end) {
                alert('Please select both start and end dates.');
                return;
            }

            $.ajax({
                url: '{{ route("daily.recovery") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    start_date: start,
                    end_date: end
                },
                success: function(response) {
                    let totalRecoveryAmount = 0;
                    let content = '';
                    let printContent = '';

                    response.forEach(item => {
                        content += `<tr>
                            <td>${item.customer}</td>
                            <td>${item.date}</td>
                            <td>${item.description}</td>
                            <td>${item.payment_method}</td>
                            <td>${parseFloat(item.amount).toFixed(2)}</td>
                        </tr>`;

                        printContent += `<tr>
                            <td>${item.customer}</td>
                            <td>${item.date}</td>
                            <td>${item.description}</td>
                            <td>${item.payment_method}</td>
                            <td>${parseFloat(item.amount).toFixed(2)}</td>
                        </tr>`;

                        totalRecoveryAmount += parseFloat(item.amount);
                    });

                    $('#recoveryTableBody').html(content);
                    $('#printContentRow').html(printContent);

                    $('#totalRecoveryAmount').text(totalRecoveryAmount.toFixed(2));
                    $('#printTotalRecoveryAmount').text(totalRecoveryAmount.toFixed(2));
                    $('#printDateRange').text(`Start Date: ${start} - End Date: ${end}`);
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    </script>
</body>
