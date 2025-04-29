<style>
    body {
        font-family: 'Noto Nastaliq Urdu', sans-serif;
        direction: rtl;
        background-color: #f8f9fa;
        margin: 20px;
    }

    .card {
        background: white;
        border-radius: 10px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 10px 0;
    }

    .top-header {
        background-color: #d8d8d8;
        padding: 10px 20px;
        border-bottom: 2px solid #EC1E1E;
        margin-bottom: 20px;
    }

    .top-header div {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        text-align: center;
    }

    .contact-info {
        font-size: 12px;
        color: #000;
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 180px;
    }

    .center-title {
        text-align: center;
        font-weight: bold;
    }

    .company-title {
        background-color: #EC1E1E;
        color: #FFEC0D;
        padding: 5px 15px;
        font-size: 24px;
        border-radius: 4px;
    }

    .company-subtitle,
    .company-address {
        background-color: #2E3094;
        color: white;
        padding: 5px 10px;
        font-size: 14px;
    }

    .info-table,
    .main-table,
    .expense-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .info-table td,
    .main-table th,
    .main-table td,
    .expense-table th,
    .expense-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    .main-table thead th,
    .expense-table thead th {
        background-color: #e8e8e8;
        color: #000;
    }

    .section-title {
        margin-top: 20px;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 18px;
        text-align: center;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .col-md-6,
    .col-md-3 {
        padding: 10px;
        box-sizing: border-box;
    }

    .col-md-6 {
        flex: 0 0 50%;
    }

    .col-md-3 {
        flex: 0 0 25%;
    }

    .expense-table td {
        text-align: right;
    }

    img {
        height: 80px;
    }

    .urdu-header {
        color: #EC1E1E;
        font-size: 30px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
        line-height: 1.2;
    }

    .urdu-header div {
        margin-bottom: 5px;
    }

    /* Print Style */
    @media print {
        .no-print {
            display: none;
        }

        body {
            margin: 0;
            padding: 0;
        }
    }

    .paper-size {
        margin: 10px 0;
        font-size: 16px;
    }

    .back-btn {
        margin-top: 10px;
    }

    .back-button,
    .print-button {
        padding: 10px 20px;
        font-size: 18px;
        border: none;
        border-radius: 5px;
        margin: 5px;
        cursor: pointer;
        font-family: 'Noto Nastaliq Urdu', sans-serif;
    }

    .back-button {
        background-color: #28a745;
        /* Green */
        color: white;
    }

    .print-button {
        background-color: #dc3545;
        /* Red */
        color: white;
    }

    /* Hover effect */
    .back-button:hover {
        background-color: #218838;
    }

    .print-button:hover {
        background-color: #c82333;
    }

    /* Print Style */
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .card {
            box-shadow: none;
            margin: 0;
            padding: 0;
            border: none;
        }
    }
</style>

<body>
    <div class="page-wrapper default-version">
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="header-bar-space mb-3"></div>

                <div id="urduSection">
                    <div class="card shadow-lg">
                        <div class="no-print" style="text-align: center; margin: 20px 0;">
                            <button class="back-button" onclick="history.back()">واپس جائیں</button>
                            <button class="print-button" onclick="window.print()">پرنٹ کریں</button>
                        </div>

                        <div class="card-body p-0">
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

                            <div class="top-header" style="padding: 0; margin-bottom: 0px;">
                                <img src="{{ asset('bill_header.png') }}" alt="Header Image" style="width: 100%; height: auto;">
                            </div>


                            <table class="info-table">
                                <tr>
                                    <td>
                                        <strong data-ur="ٹرک نمبر:">ٹرک نمبر:</strong> {{ $bill->trucknumber }}
                                    </td>
                                    <td style="text-align:center; font-size:25px; background:#e8e8e8;"><strong data-ur="وینڈر کا نام:">وینڈر کا نام:</strong> {{ $vendorName }}</td>
                                    <td>
                                        <strong data-ur="تاریخ:">تاریخ:</strong> {{ \Carbon\Carbon::parse($bill->created_at)->format('Y-m-d') }}
                                    </td>

                                </tr>
                            </table>

                            <div class="section-title" data-ur="لاٹ کی تفصیلات">لاٹ کی تفصیلات</div>

                            <div class="table-responsive">
                                <table class="main-table table table-bordered table-striped">
                                    <thead class="table-light" style="background:#e8e8e8!important;">
                                        <tr>
                                            <th data-ur="تَن٘گ" style="color:#000;">تَن٘گ</th>
                                            <th data-ur="وزن" style="color:#000;">وزن</th> {{-- نیا وزن کا کالم --}}
                                            <th data-ur="یونٹ" style="color:#000;">یونٹ</th>
                                            <th data-ur="زمرہ" style="color:#000;">زمرہ</th>
                                            <th data-ur="اقسام" style="color:#000;">اقسام</th>
                                            <th data-ur="اکائی" style="color:#000;">اکائی</th>
                                            <th data-ur="شرح" style="color:#000;">شرح</th>
                                            <th data-ur="کل" style="color:#000;">کل</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                        $totalSaleUnits = 0;
                                        $totalRowWeights = 0;
                                        $existingRows = count($lot_ids);
                                        $minRows = 5; // کم سے کم 5 rows چاہیے
                                        @endphp

                                        {{-- پہلے اصلی ڈیٹا --}}
                                        @foreach ($lot_ids as $index => $lotId)
                                        @php
                                        $unit = (int)($sale_units[$index] ?? 0);
                                        $weight = (float)($weights[$index] ?? 0);
                                        $totalSaleUnits += $unit;
                                        $totalRowWeights += $weight;
                                        $lot = $lotEntries[$lotId] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $unit }}</td>
                                            <td>{{ number_format($weight) }}</td> 
                                            <td>{{ $lot->unit_in_ur ?? 'نہیں ملا' }}</td>
                                            <td>{{ $lot->category_ur ?? '' }}</td>
                                            <td>{{ $lot->variety_ur ?? '' }}</td>
                                            <td>{{ $lot->unit_ur ?? 'نہیں ملا' }}</td>
                                            <td>{{ number_format($rates[$index] ?? 0) }}</td>
                                            <td>{{ number_format($amounts[$index] ?? 0) }}</td>
                                        </tr>
                                        @endforeach

                                        {{-- اگر rows کم ہیں تو خالی rows ڈالو --}}
                                        @if ($existingRows < $minRows)
                                            @for ($i=0; $i < ($minRows - $existingRows); $i++)
                                            <tr>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            </tr>
                                            @endfor
                                            @endif

                                            {{-- آخر میں total row --}}
                                            <tr class="lot-total-row">
                                                <td class="text-center">کل لاٹس: {{ number_format($totalSaleUnits) }}</td>
                                                <td class="text-center">کل وزن: {{ number_format($totalRowWeights) }}</td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                                <td colspan="1" class="text-end">کل</td>
                                                <td>{{ number_format($bill->subtotal) }}</td>
                                            </tr>
                                    </tbody>

                                </table>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="expense-table table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>اخراجات کی قسم</th>
                                                        <th>رقم</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($categories_ur as $index => $cat)
                                                    <tr>
                                                        <td><strong>{{ $cat }}</strong></td>
                                                        <td>{{ number_format($finals[$index] ?? 0) }}</td>
                                                    </tr>
                                                    @endforeach
                                                    <tr class="expense-total" style="color:#af3128;">
                                                        <td><strong>کل</strong></td>
                                                        <td>{{ number_format($bill->total_expense) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="expense-table table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>کل:</strong></td>
                                                        <td>{{ number_format($bill->subtotal) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>کل اخراجات:</strong></td>
                                                        <td>{{ number_format($bill->total_expense) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>کل ایڈجسٹمنٹ:</strong></td>
                                                        <td>{{ number_format($bill->adjustment) }}</td>
                                                    </tr>
                                                    <tr class="net-row" style="color:218838;">
                                                        <td><strong>نیٹ رقم:</strong></td>
                                                        <td>{{ number_format($bill->net_pay) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>