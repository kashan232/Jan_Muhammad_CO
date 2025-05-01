<!DOCTYPE html>
<html lang="ur" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>رسید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Nastaliq Urdu', serif;
            direction: rtl;
            background-color: #f9f9f9;
        }

        .invoice-box {
            border: 2px solid #000;
            padding: 10px;
            margin: 20px auto;
            max-width: 360px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 5px;
        }

        .logo img {
            max-width: 80px;
        }

        .phone-numbers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .header-info {
            font-weight: bold;
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .section-title {
            background: #e9ecef;
            padding: 4px 8px;
            font-weight: bold;
            border: 1px solid #000;
            margin-top: 8px;
            font-size: 13px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 13px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-weight: bold;
        }

        .totals td {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .footer-note {
            border: 1px dashed #000;
            padding: 6px;
            margin-top: 10px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background-color: #fcfcfc;
        }

        .d-flex img {
            margin: 0;
        }

        .d-flex span {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <!-- Customer Info -->
        <div class="header-info row">
            <div class="col">نام: {{ $customer_name }}</div>
            <div class="col text-start">
                <div>تاریخ: {{ $start_date }} سے</div>
                <div>{{ $end_date }} تک</div>
            </div>
        </div>

        <!-- Sales Table -->
        <table>
            <thead>
                <tr>
                    <th>نگ</th>
                    <th>وزن</th>
                    <th>ریٹ</th>
                    <th>نامہ رقم</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->quantity }}</td>
                    <td>{{ $sale->weight }}</td>
                    <td>{{ $sale->price }}</td>
                    <td>{{ $sale->total }}</td>
                </tr>
                @endforeach
                <tr class="totals">
                    <td colspan="3">ٹوٹل</td>
                    <td>{{ $total_sales }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary Table -->
        <table>
            <tr class="totals">
                <td colspan="2">کل نگ: {{ $total_quantity }}</td>
                <td>ٹوٹل نامہ رقم</td>
                <td>{{ $total_sales }}</td>
            </tr>
            <tr class="totals">
                <td colspan="2"></td>
                <td>سابقہ بیلنس</td>
                <td>{{ $previous_balance }}</td>
            </tr>
            <tr class="totals">
                <td colspan="2"></td>
                <td>ٹوٹل</td>
                <td>{{ $total_balance }}</td>
            </tr>
            <tr class="totals">
                <td colspan="2"></td>
                <td>وصُولی</td>
                <td>{{ $total_recovery }}</td>
            </tr>
            <tr class="totals" style="background-color: #d1e7dd;">
                <td colspan="2"></td>
                <td><strong>بقایا بیلنس</strong></td>
                <td><strong>{{ $total_balance - $total_recovery }}</strong></td>
            </tr>
        </table>
    </div>

</body>

</html>