@include('admin_panel.include.header_include')
<style>
    .badge {
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f5f5f5;
        transition: background-color 0.3s ease;
    }

    .page-title {
        font-size: 22px;
        font-weight: bold;
        color: #4a4a4a;
    }
</style>

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">

        <!-- sidebar start -->

        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Daily Sale</h6>
                </div>
                <!-- Customer Selection Form -->
                <!-- Search Form -->
                <div class="card shadow-lg p-4">
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
                                    <button type="button" class="btn btn-primary w-100" id="filterSales">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table to show results -->
                <div class="table-responsive mt-4">
                <table class="table table-bordered table-hover shadow-sm rounded-3" style="width:100%">
                        <thead class="bg-primary text-white text-center">
                            <tr>
                                <th>#</th>
                                <th>Sale Date</th>
                                <th>Customer</th>
                                <th>Truck No</th>
                                <th>Category</th>
                                <th>Variety</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody" class="text-center">
                            <tr>
                                <td colspan="10">Please select a date range to view sales.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
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
                    let html = '';
                    if (response.length === 0) {
                        html = '<tr><td colspan="10">No sales found for selected dates.</td></tr>';
                    } else {
                        response.forEach((sale, index) => {
                            html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${sale.sale_date}</td>
                                <td>${sale.customer}</td>
                                <td>${sale.truck_number}</td>
                                <td>${sale.category}</td>
                                <td>${sale.variety}</td>
                                <td>${sale.unit} (${sale.unit_in})</td>
                                <td>${sale.quantity}</td>
                                <td>${sale.price}</td>
                                <td>${sale.total}</td>
                            </tr>
                        `;
                        });
                    }

                    $('#salesTableBody').html(html);
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    </script>