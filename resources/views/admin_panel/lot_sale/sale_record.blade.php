@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="container">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary ">
                            <h4 class="text-white">Sale Record for Truck #{{ $truck->truck_number }}</h4>
                        </div>
                        <div class="card-body">

                            @foreach($lots as $lot)
                            <div class="card my-3 border">
                                <div class="card-header bg-light">
                                    <h5>Lot: {{ $lot->category }} - {{ $lot->variety }}</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Total Quantity: </strong>{{ $lot->total_units }}</p>
                                    <p><strong>Total Sold: </strong>{{ $lot->sold_quantity }}</p>
                                    <p><strong>Available: </strong>{{ $lot->available_quantity }}</p>

                                    <table class="table table-bordered">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Sold Units</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Sale Date</th>
                                                <th>Type</th> <!-- Cash/Credit -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lot->sales as $sale)
                                            <tr>
                                                <td>{{ $sale->customer_name }}</td>
                                                <td>{{ $sale->quantity }}</td>
                                                <td>{{ number_format($sale->price, 2) }}</td>
                                                <td>{{ number_format($sale->total, 2) }}</td>
                                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $sale->customer_type == 'Credit' ? 'bg-danger' : 'bg-success' }}">
                                                        {{ $sale->customer_type }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
</body>
