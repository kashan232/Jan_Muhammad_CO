<!-- meta tags and other links -->
@include('admin_panel.include.header_include')
<style>
    .ledger-container {
        border: 2px solid black;
        padding: 10px;
        max-width: 900px;
        margin: 20px auto;
        background: #fff;
    }

    .ledger-header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    .ledger-info {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }

    thead th {
        background: #f2f2f2;
    }

    .opening-balance {
        text-align: right;
        font-weight: bold;
        padding: 8px;
        border: 1px solid black;
    }
</style>

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
                    <h6 class="page-title">Customer ledger</h6>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card p-4">

                            <form id="ledgerSearchForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold">Select Customer</label>
                                        <select id="Customer" class="select2-basic form-control">

                                            <option value="">-- Select Customer --</option>
                                            @foreach($Customers as $Customer)
                                            <option value="{{ $Customer->id }}">{{ $Customer->customer_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold">Start Date</label>
                                        <input type="date" id="start_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold">End Date</label>
                                        <input type="date" id="end_date" class="form-control">
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="button" id="searchLedger" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                            <div class="text-end mt-2">
                                <button id="downloadPdf" class="btn btn-danger">
                                    Download PDF
                                </button>
                            </div>
                            <div id="ledgerResult" class="mt-4" style="display: none;">
                                <div class="ledger-container">
                                    <div class="ledger-header">CUSTOMER LEDGER</div>
                                    <div class="ledger-info">
                                        <span><strong>Customer:</strong> <span id="CustomerName"></span></span>
                                        <span><strong>Duration:</strong> From <span id="startDate"></span> To <span id="endDate"></span></span>
                                    </div>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>INV-No</th>
                                                <th>Description</th>
                                                <th>Debit</th>
                                                <th>Credit</th>
                                                <th>Balance</th>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="opening-balance">Opening Balance:</td>
                                                <td id="openingBalance">Rs. 0</td>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerData"></tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3"><strong>Totals:</strong></td>
                                                <td id="totalDebit">0</td>
                                                <td id="totalCredit">0</td>
                                                <td id="closingBalance">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin_panel.include.footer_include')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        $(document).ready(function() {
            $('#searchLedger').click(function() {
                var CustomerId = $('#Customer').val();
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                if (!CustomerId) {
                    alert('Please select a customer.');
                    return;
                }

                $.ajax({
                    url: "{{ route('fetch-Customer-ledger') }}",
                    type: "GET",
                    data: {
                        Customer_id: CustomerId,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        $('#ledgerResult').show();
                        $('#CustomerName').text($('#Customer option:selected').text());
                        $('#startDate').text(formatDate(response.startDate));
                        $('#endDate').text(formatDate(response.endDate));

                        let openingBalance = parseFloat(response.opening_balance) || 0;
                        let totalDebit = 0,
                            totalCredit = 0;
                        let ledgerHTML = "";

                        // Opening Balance Row in <thead>
                        $('#openingBalance').text(`Rs. ${Math.round(openingBalance)}`);

                        let balance = openingBalance; // Initialize balance with opening balance

                        // Iterate over ledger data
                        response.ledger_data.forEach(entry => {
                            let debitAmount = parseFloat(entry.debit) || 0;
                            let creditAmount = parseFloat(entry.credit) || 0;

                            if (debitAmount) {
                                balance += debitAmount; // Add debit to balance
                                totalDebit += debitAmount;
                                ledgerHTML += `
                    <tr>
                        <td>${formatDate(entry.date)}</td>
                        <td>-</td>
                        <td>Sale</td>
                        <td>Rs. ${Math.round(debitAmount)}</td>
                        <td>-</td>
                        <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${Math.round(balance)}</td>
                    </tr>`;
                            }

                            if (creditAmount) {
                                balance -= creditAmount; // Subtract credit from balance
                                totalCredit += creditAmount;
                                ledgerHTML += `
                    <tr>
                        <td>${formatDate(entry.date)}</td>
                        <td>-</td>
                        <td>Recovery</td>
                        <td>-</td>
                        <td>Rs. ${Math.round(creditAmount)}</td>
                        <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${Math.round(balance)}</td>
                    </tr>`;
                            }
                        });

                        // Update the table with the generated HTML
                        $('#ledgerData').html(ledgerHTML);
                        $('#totalDebit').text(`Rs. ${Math.round(totalDebit)}`);
                        $('#totalCredit').text(`Rs. ${Math.round(totalCredit)}`);
                        $('#closingBalance').text(`Rs. ${Math.round(balance)}`);
                    }
                });
            });
        });
    </script>
    <script>
        document.getElementById("downloadPdf").addEventListener("click", function() {
            const element = document.querySelector(".ledger-container");

            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jspdf.jsPDF("p", "mm", "a4");

                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
                pdf.save("Customer-Ledger.pdf");
            });
        });

        // Show PDF button only when result appears
        $('#searchLedger').click(function() {
            setTimeout(() => {
                $('#downloadPdf').removeClass('d-none');
            }, 500);
        });
    </script>