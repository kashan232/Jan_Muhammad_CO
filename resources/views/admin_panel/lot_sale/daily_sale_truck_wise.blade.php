{{-- resources/views/admin_panel/daily_truck_sale.blade.php --}}
@include('admin_panel.include.header_include')

<style>
    .print-section .container {
        padding: 10px 15px;
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
        margin-bottom: 10px;
    }

    /* ====== Print Specific Styles ====== */
    @media print {

        body,
        html {
            margin: 0;
            padding: 0;
        }

        /* Hide everything except .print-section */
        body * {
            visibility: hidden;
        }

        .print-section,
        .print-section * {
            visibility: visible;
        }

        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">

                {{-- Header & Filters --}}
                <div class="d-flex mb-3 justify-content-between align-items-center">
                    <h6 class="page-title">Daily Sale Truck Wise</h6>
                    <button onclick="window.print()" class="btn btn-danger">Print</button>
                </div>

                <div class="card mb-4" id="daily-sale-div">
                    <div class="card-body">
                        <form id="customerSaleForm" class="row g-3">
                            @csrf
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
                        </form>
                    </div>
                </div>

                {{-- Print‑section (also visible on screen) --}}
                <div id="printSection" class="print-section">
                    <div class="container">
                        <div class="print-header">
                            Daily Sale Truck Wise Report
                        </div>
                        <div class="text-center mb-3" id="printDateRange" style="font-size:14px;"></div>

                        {{-- Dynamic per‑truck tables will be injected here --}}
                        <div id="printResults"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        $('#filterSales').on('click', function() {
            const start = $('#start_date').val();
            const end = $('#end_date').val();

            if (!start || !end) {
                alert('Please select both start and end dates.');
                return;
            }

            $.ajax({
                url: '{{ route("daily.truck.sale") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    start_date: start,
                    end_date: end
                },
                success: function(response) {
                    // Group by truck_number
                    const grouped = {};
                    response.forEach(r => {
                        (grouped[r.truck_number] = grouped[r.truck_number] || []).push(r);
                    });

                    let grandTotal = 0;
                    let html = '';

                    // Build per‑truck tables
                    for (const [truck, rows] of Object.entries(grouped)) {
                        let truckTotal = 0;
                        let rowsHtml = '';

                        rows.forEach(r => {
                            rowsHtml += `
                            <tr>
                                <td>${r.customer_name}</td>
                                <td>${r.total_quantity}</td>
                            </tr>`;
                            truckTotal += parseFloat(r.total_quantity);
                        });

                        grandTotal += truckTotal;

                        html += `
                        <div class="truck-block">
                            <h5>Truck: ${truck}</h5>
                            <table class="print-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rowsHtml}
                                </tbody>
                                <tfoot class="bg-dark">
                                    <tr>
                                        <td>Total for ${truck}</td>
                                        <td>${truckTotal}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>`;
                    }

                    // Grand total
                    html += `<div class="truck-block">
                            <table class="print-table">
                                <tfoot class="bg-dark">
                                    <tr>
                                        <td><strong>Grand Total Sold</strong></td>
                                        <td><strong>${grandTotal}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                         </div>`;

                    // Inject into print area
                    $('#printDateRange').text(`From ${start} to ${end}`);
                    $('#printResults').html(html);
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    </script>
</body>