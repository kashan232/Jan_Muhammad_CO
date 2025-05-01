<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رسید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your CSS here */
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Dynamic content will go here -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex flex-column text-start">
                <span style="font-weight: bold;">{{ $customerPhone }}</span>
            </div>
            <img src="logo-bill.png" alt="Logo" style="max-height: 100px;">
            <div class="d-flex flex-column text-end">
                <span style="font-weight: bold;">{{ $customerPhone }}</span>
            </div>
        </div>

        <div class="header-info row">
            <div class="col">نام: {{ $customerName }}</div>
            <div class="col text-start">
                <div>تاریخ: {{ $startDate }} سے</div>
                <div>{{ $endDate }} تک</div>
            </div>
        </div>

        <div class="section-title">تتاریخ: {{ $startDate }}</div>

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
                        <td>{{ $sale->weight }}</td>
                        <td>{{ $sale->rate }}</td>
                        <td>{{ $sale->amount }}</td>
                        <td>{{ $sale->total }}</td>
                    </tr>
                @endforeach
                <tr class="totals">
                    <td colspan="3">ٹوٹل</td>
                    <td>{{ $totalAmount }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-note">
            <p>پچھلا بیلنس: {{ $previousBalance }} PKR</p>
            <p>کل رقم: {{ $totalAmount }} PKR</p>
            <p>ریکاوری: {{ $totalRecovery }} PKR</p>
            <p>بقایا بیلنس: {{ $remainingBalance }} PKR</p>
        </div>
    </div>
</body>
</html>
