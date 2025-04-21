@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&display=swap" rel="stylesheet">

<style>
    /* Common Table Styles */
    .main-table th,
    .main-table td,
    .expense-table th,
    .expense-table td {
        white-space: nowrap;
        font-size: 14px;
        padding: 6px 10px;
        border: 1px solid #ddd;
    }

    .main-table th {
        background-color: #f7f7f7;
        text-transform: uppercase;
        font-weight: bold;
        color: #333;
    }

    .lot-total-row,
    .expense-total-row {
        font-weight: bold;
        background-color: #f0f0f0;
    }

    .net-row {
        font-weight: bold;
        background-color: #d8f9e5;
        border-top: 2px solid #4ba064;
    }

    .expense-table th {
        background-color: #e9e9e9;
        text-align: left;
    }

    .expense-table td {
        text-align: left;
    }

    .expense-total {
        font-weight: bold;
        background-color: #fdfdfd;
        border-top: 2px solid #999;
    }

    .section-title {
        font-weight: bold;
        text-transform: uppercase;
        margin: 30px 0 10px;
        font-size: 16px;
        color: #4ba064;
        border-left: 4px solid #4ba064;
        padding-left: 10px;
    }

    .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    @media print {
        .contact-info {
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
            font-size: 11px !important;
        }

        .contact-info div {
            text-align: left !important;
            padding-left: 0 !important;
            margin: 0 !important;
        }
    }


    /* PRINT STYLES */
    @media print {

        /* Hide unwanted elements */
        .no-print,
        .navbar,
        .sidebar,
        button,
        .btn,
        .print-hide,
        .action-buttons,
        .header,
        .footer,
        .page-title,
        .navbar-wrapper.bg--dark {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
        }

        /* Remove card styling */
        .card,
        .card-body,
        .card-header,
        .card-footer {
            background: none !important;
            box-shadow: none !important;
            border: none !important;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .content,
        .table-responsive {
            width: 100% !important;
            margin: 0 auto !important;
        }

        @page {
            size: auto;
            margin: 0.5in;
        }

        .d-flex.mb-4.flex-wrap.gap-3.justify-content-between.align-items-center {
            display: none !important;
        }
    }

    @media print {

        body,
        table,
        th,
        td,
        div,
        span,
        strong {
            font-size: 11px !important;
            /* smaller font */
            line-height: 1.2 !important;
            color: #000 !important;
            font-family: 'Noto Nastaliq Urdu', 'Arial', sans-serif !important;
        }

        .main-table th,
        .main-table td,
        .expense-table th,
        .expense-table td {
            padding: 4px 6px !important;
            /* smaller padding */
        }
    }
</style>



<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <!-- Language Toggle Button -->
                <div class="d-flex justify-content-end mb-3">
                    <button id="toggle-lang" class="btn btn-sm btn-success">Translate to Urdu</button>
                </div>

                <!-- Print Buttons -->
                <div class="print-btns d-flex justify-content-end gap-2 no-print">
                    <button onclick="printDocument('blank')" class="btn btn-outline-secondary">Blank Print</button>
                    <button onclick="printDocument('color')" class="btn btn-primary">Color Print</button>
                </div>

                <div class="header-bar-space mb-3"></div>

                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center no-print">
                    <h4 class="fw-bold text-primary mb-4" data-en="Bill Book" data-ur="بل بک">Bill Book</h4>
                </div>

                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        @php
                        $lot_ids = json_decode($bill->lot_id ?? '[]');
                        $units_in = json_decode($bill->unit_in ?? '[]');
                        $sale_units = json_decode($bill->sale_units ?? '[]');
                        $rates = json_decode($bill->rate ?? '[]');
                        $amounts = json_decode($bill->amount ?? '[]');

                        $categories = json_decode($bill->category ?? '[]');
                        $values = json_decode($bill->value ?? '[]');
                        $finals = json_decode($bill->final_amount ?? '[]');
                        @endphp

                        <!-- Top Header Banner -->
                        <div class="top-header" style="background-color: #FFFBD4; padding: 10px 20px; border-bottom: 2px solid #EC1E1E;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">

                                <!-- Left Contact Info -->
                                <div class="contact-info" style="font-size: 12px; color: #000; display: flex; flex-direction: column; gap: 2px; min-width: 200px;">
                                    <div style="text-align: left;"><strong>Haji Anwar</strong> - 0322-3014221</div>
                                    <div style="text-align: left;"><strong>Umair Anwar</strong> - 0321-3022033</div>
                                    <div style="text-align: left;"><strong>Faizan Anwar</strong> - 0321-3061917</div>
                                    <div style="text-align: left;"><strong>Ahmed Anwar</strong> - 0311-8661606</div>
                                </div>

                                <!-- Center Title -->
                                <div class="center-title" style="text-align: center;">
                                    <div style="background-color: #EC1E1E; color: #FFEC0D; padding: 5px 15px; font-weight: bold; font-size: 24px; border-radius: 4px;">
                                        Jan Muhammad and CO
                                    </div>
                                    <div style="background-color: #2E3094; color: white; padding: 5px 10px; font-size: 14px;">
                                        Alu • Piyaz • Hari Mirch • Lassan Commission Agent
                                    </div>
                                    <div style="background-color: #02A64F; color: white; padding: 5px 10px; font-size: 13px;">
                                        Shop Number 209 and 218, New Sabzi Mandi, Halanaka Hyderabad
                                    </div>
                                </div>

                                <!-- Right Logo -->
                                <div>
                                    <img src="{{ asset('logo_white.png') }}" alt="Logo" style="height: 80px;">
                                </div>
                            </div>
                        </div>


                        <!-- Vendor Info Table -->
                        <table class="info-table" style="width: 100%; margin-top: 10px;">
                            <tr>
                                <td style="text-align: left;"><strong data-en="Date:" data-ur="تاریخ:">Date:</strong> {{ \Carbon\Carbon::parse($bill->created_at)->format('Y-m-d') }}</td>
                                <td style="text-align: ;"><strong data-en="Vendor Name:" data-ur="وینڈر کا نام:">Vendor Name:</strong> {{ $vendorName }}</td>
                                <td style="text-align: right;"><strong data-en="Truck No:" data-ur="ٹرک نمبر:">Truck No:</strong> {{ $bill->trucknumber }}</td>
                            </tr>
                        </table>

                        <div class="section-title" data-en="Lot Details" data-ur="لاٹ کی تفصیلات">Lot Details</div>
                        <div class="table-responsive">
                            <table class="main-table table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th data-en="Lot" data-ur="لاٹ">Lot</th>
                                        <th data-en="Unit In" data-ur="یونٹ">Unit In</th>
                                        <th data-en="Category" data-ur="زمرہ">Category</th>
                                        <th data-en="Variety" data-ur="اقسام">Variety</th>
                                        <th data-en="Unit" data-ur="اکائی">Unit</th>
                                        <th data-en="Rate" data-ur="شرح">Rate</th>
                                        <th data-en="Total" data-ur="کل">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalSaleUnits = 0; @endphp
                                    @foreach ($lot_ids as $index => $lotId)
                                    @php
                                    $unit = (int)($sale_units[$index] ?? 0);
                                    $totalSaleUnits += $unit;
                                    $lot = $lotEntries[$lotId] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $unit }}</td>
                                        <td>{{ $units_in[$index] ?? '' }}</td>
                                        <td>{{ $lot->category ?? '' }}</td>
                                        <td>{{ $lot->variety ?? '' }}</td>
                                        <td>{{ $lot->unit ?? '' }}</td>
                                        <td>{{ number_format($rates[$index] ?? 0) }}</td>
                                        <td>{{ number_format($amounts[$index] ?? 0) }}</td>
                                    </tr>
                                    @endforeach

                                    <tr class="lot-total-row">
                                        <td style="text-align: center;" data-en="Total Lots:" data-ur="کل لاٹس:">
                                            Total Lots: {{ number_format($totalSaleUnits) }}
                                        </td>
                                        <td colspan="5" style="text-align: right;" data-en="Total" data-ur="کل">Total</td>
                                        <td>{{ number_format($bill->subtotal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Total Expenses and Net Amount outside the table --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong data-en="Total Expenses" data-ur="کل اخراجات">Total Expenses:</strong> {{ number_format($bill->total_expense) }}</p>
                                        <p><strong data-en="Net Amount" data-ur="خالص رقم">Net Amount:</strong> {{ number_format($bill->net_pay) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <table class="expense-table table table-bordered" style="width: auto;">
                                <thead>
                                    <tr>
                                        <th data-en="Expense Type" data-ur="اخراجات کی قسم">Expense Type</th>
                                        <th data-en="Amount" data-ur="رقم">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $index => $cat)
                                    <tr>
                                        <td>{{ $cat }}</td>
                                        <td>{{ number_format($finals[$index] ?? 0) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="expense-total">
                                        <td data-en="Total" data-ur="کل">Total</td>
                                        <td>{{ number_format($bill->total_expense) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <!-- JS for Adjusting Print Layout -->
    <script>
        function printDocument(mode) {
            if (mode === 'blank') {
                document.body.classList.remove('color-print');
                document.body.classList.add('blank-print');
            } else {
                document.body.classList.remove('blank-print');
                document.body.classList.add('color-print');
            }
            window.print();
        }

        // Language Toggle
        let isUrdu = false;
        document.getElementById('toggle-lang').addEventListener('click', function() {
            isUrdu = !isUrdu;
            const elements = document.querySelectorAll('[data-en]');
            elements.forEach(el => {
                el.textContent = isUrdu ? el.getAttribute('data-ur') : el.getAttribute('data-en');
            });
            this.textContent = isUrdu ? 'Translate to English' : 'Translate to Urdu';
        });
    </script>


</body>