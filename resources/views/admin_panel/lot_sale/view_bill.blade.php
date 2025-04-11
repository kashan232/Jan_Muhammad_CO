@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    @media print {
        body.color-print .header-bar-space {
            height: 100px;
            /* Reserve space for header bar */
        }

        body.blank-print .header-bar-space {
            display: none;
        }

        .no-print {
            display: none !important;
        }
    }

    .print-btns {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: #fff;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }
</style>

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <!-- Print Buttons -->
                <div class="print-btns d-flex justify-content-end gap-2 no-print">
                    <button onclick="printDocument('blank')" class="btn btn-outline-secondary">Blank Print</button>
                    <button onclick="printDocument('color')" class="btn btn-primary">Color Print</button>
                </div>

                <!-- Top header space for color print -->
                <div class="header-bar-space mb-3"></div>

                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary mb-4">Vendor Bill Details</h4>
                </div>

                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <!-- Truck Info -->
                        <div class="mb-4">
                            <h5 class="fw-bold">Truck Information</h5>
                            <div class="row">
                                <div class="col-md-6"><strong>Truck ID:</strong> {{ $bill->truck_id }}</div>
                                <div class="col-md-6"><strong>Truck Number:</strong> {{ $bill->trucknumber }}</div>
                            </div>
                        </div>

                        <!-- LOT Details -->
                        <h5 class="text-success">Lots Sold</h5>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Lot ID</th>
                                    <th>Unit In</th>
                                    <th>Units</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(json_decode($bill->lot_id) as $index => $lotId)
                                <tr>
                                    <td>{{ $lotId }}</td>
                                    <td>{{ json_decode($bill->unit_in)[$index] ?? '-' }}</td>
                                    <td>{{ json_decode($bill->sale_units)[$index] ?? '-' }}</td>
                                    <td>Rs. {{ number_format(json_decode($bill->rate)[$index] ?? 0) }}</td>
                                    <td>Rs. {{ number_format(json_decode($bill->amount)[$index] ?? 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Subtotal</td>
                                    <td>Rs. {{ number_format($bill->subtotal, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Expenses -->
                        <h5 class="text-danger mt-4">Expenses</h5>
                        @php
                        $finalAmounts = json_decode($bill->final_amount, true);
                        $categories = json_decode($bill->category, true);
                        $mazdoriTotal = 0;
                        foreach ($categories as $key => $cat) {
                        if (strtolower($cat) === 'mazdori') {
                        $mazdoriTotal += $finalAmounts[$key] ?? 0;
                        }
                        }
                        $expenseTotal = array_sum($finalAmounts);
                        @endphp

                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Final Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $index => $category)
                                <tr>
                                    <td>{{ $category }}</td>
                                    <td>Rs. {{ number_format($finalAmounts[$index] ?? 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="1" class="text-end">Total Expenses</td>
                                    <td>Rs. {{ number_format($expenseTotal, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Summary -->
                        <!-- Summary Breakdown -->
                        <div class="mt-5 p-4 border rounded bg-white shadow-sm">
                            <h4 class="fw-bold text-center text-primary mb-4">Final Summary</h4>

                            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                <span class="fw-bold fs-5">Subtotal</span>
                                <span class="fw-bold fs-5 text-dark">Rs. {{ number_format($bill->subtotal, 0) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                                <span class="fw-bold fs-5 text-danger">Total Expenses</span>
                                <span class="fw-bold fs-5 text-danger">Rs. {{ number_format($expenseTotal, 0) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3">
                                <span class="fw-bold fs-4 text-success">Net Amount</span>
                                <span class="fw-bold fs-4 text-success">Rs. {{ number_format($bill->net_pay, 0) }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

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
    </script>
</body>