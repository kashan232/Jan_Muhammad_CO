@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4>Sale Record for Truck #{{ $truck->truck_number }}</h4>
                </div>
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="table-responsive--sm table-responsive">
                            <table id="example" class="display  table table--light" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Customer Phone</th>
                                        <th>Sold Units</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Sale Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>{{ $sale->customer_phone }}</td>
                                        <td>{{ $sale->quantity }}</td>
                                        <td>{{ $sale->price }}</td>
                                        <td>{{ $sale->total }}</td>
                                        <td>{{ $sale->sale_date }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin_panel.include.footer_include')
</body>