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
                    <h6 class="page-title">Vendors ledger</h6>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card p-4">

                            <form id="ledgerSearchForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="fw-bold">Select Vendors</label>
                                        <select id="Vendor" class="form-control">
                                            <option value="">-- Select Vendors --</option>
                                            @foreach($Vendors as $Vendor)
                                            <option value="{{ $Vendor->id }}">{{ $Vendor->name }}</option>
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
                                    <div class="ledger-header">Vendor LEDGER</div>
                                    <div class="ledger-info">
                                        <span><strong>Vendor:</strong> <span id="VendorName"></span></span>
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
                var VendorId = $('#Vendor').val();
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                if (!VendorId) {
                    alert('Please select a Vendor.');
                    return;
                }

                $.ajax({
                    url: "{{ route('fetch-Vendor-ledger-report') }}",
                    type: "GET",
                    data: {
                        Vendor_id: VendorId,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        $('#ledgerResult').show();
                        $('#VendorName').text($('#Vendor option:selected').text());
                        $('#startDate').text(formatDate(response.startDate));
                        $('#endDate').text(formatDate(response.endDate));

                        let openingBalance = parseFloat(response.opening_balance) || 0;
                        let balance = openingBalance;
                        let totalDebit = 0,
                            totalCredit = 0;
                        let ledgerHTML = "";

                        // Opening Balance Entry
                        ledgerHTML += `
                        <tr>
                            <td>${formatDate(response.startDate)}</td>
                            <td>-</td>
                            <td class="fw-bold">Opening Balance</td>
                            <td>-</td>
                            <td>-</td>
                            <td class="fw-bold text-primary">Rs. ${balance.toFixed(2)}</td>
                        </tr>`;

                        // Combine and sort entries
                        let allEntries = [];

                        response.local_sales.forEach(entry => {
                            allEntries.push({
                                date: entry.sale_date,
                                type: 'sale',
                                invoice_number: entry.id,
                                amount: parseFloat(entry.total) || 0,
                                description: 'Bill Created'
                            });
                        });

                        response.recoveries.forEach(entry => {
                            allEntries.push({
                                date: entry.date,
                                type: 'recovery',
                                amount: parseFloat(entry.amount_paid) || 0,
                                description: 'Payment To Vendor'
                            });
                        });

                        allEntries.sort((a, b) => new Date(a.date) - new Date(b.date));

                        // Display Entries
                        allEntries.forEach(entry => {
                            if (entry.type === 'sale') {
                                balance += entry.amount;
                                totalDebit += entry.amount;
                                ledgerHTML += `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>${entry.invoice_number}</td>
                                    <td>${entry.description}</td>
                                    <td>Rs. ${entry.amount.toFixed(2)}</td>
                                    <td>-</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                            } else if (entry.type === 'recovery') {
                                balance -= entry.amount;
                                totalCredit += entry.amount;
                                ledgerHTML += `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>-</td>
                                    <td>${entry.description}</td>
                                    <td>-</td>
                                    <td>Rs. ${entry.amount.toFixed(2)}</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                            }
                        });

                        $('#ledgerData').html(ledgerHTML);
                        $('#openingBalance').text(`Rs. ${openingBalance.toFixed(2)}`);
                        $('#totalDebit').text(`Rs. ${totalDebit.toFixed(2)}`);
                        $('#totalCredit').text(`Rs. ${totalCredit.toFixed(2)}`);
                        $('#closingBalance').text(`Rs. ${balance.toFixed(2)}`);
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
                pdf.save("Vendor_ledger .pdf");
            });
        });

        // Show PDF button only when result appears
        $('#searchLedger').click(function() {
            setTimeout(() => {
                $('#downloadPdf').removeClass('d-none');
            }, 500);
        });
    </script>