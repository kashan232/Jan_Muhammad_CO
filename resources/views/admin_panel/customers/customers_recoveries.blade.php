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
                    <h6 class="page-title">Customer Recoveries</h6>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table class="table--light style--two table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Customer</th>
                                                <th>Description</th>
                                                <th>Amount Paid</th>
                                                <th>Bank</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($Recoveries as $key => $recovery)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $recovery->customer->customer_name ?? 'N/A' }}</td>
                                                <td>{{ $recovery->description }}</td>
                                                <td>{{ number_format($recovery->amount_paid, 0) }}</td>
                                                <td>{{ $recovery->Bank }}</td>
                                                <td>{{ $recovery->date }}</td>

                                            </tr>
                                            @endforeach
                                            @if($Recoveries->isEmpty())
                                            <tr>
                                                <td colspan="7" class="text-center">No recoveries found.</td>
                                            </tr>
                                            @endif
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