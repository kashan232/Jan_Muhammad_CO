@include('admin_panel.include.header_include')

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
                    <h6 class="page-title">Cash Sale</h6>

                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table class="table--light style--two table">
                                        <thead>
                                            <tr>
                                                <th>Sale Date</th>
                                                <th>Category</th>
                                                <th>Variety</th>
                                                <th>Sold Units</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cash_sales as $sale)
                                            <tr>
                                                <td>{{ $sale->sale_date }}</td>
                                                <td>{{ $sale->category }}</td>
                                                <td>{{ $sale->variety }}</td>
                                                <td>{{ $sale->quantity }}</td>
                                                <td>{{ $sale->price }}</td>
                                                <td>{{ $sale->total }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>
    @include('admin_panel.include.footer_include')