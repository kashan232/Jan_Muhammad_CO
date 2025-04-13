@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary"> Sold Trucks</h4>
                </div>
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="table-responsive--sm table-responsive">
                            <table id="example" class="display  table table--light" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Truck Number</th>
                                        <th>Vendor</th>
                                        <th>Arrival Date</th>
                                        <th>Total Available Units</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trucks as $truck)
                                    <tr>
                                        <td>{{ $truck->truck_number }}</td>
                                        <td>{{ $truck->vendor_id }}</td>
                                        <td>{{ $truck->entry_date }}</td>
                                        <td>{{ $truck->total_units }}</td>
                                        <td>
                                            @if($truck->total_units > 0)
                                            <a href="{{ route('show-Lots', $truck->id) }}" class="btn btn-primary btn-sm">Sale</a>
                                            @else
                                            <span class="btn btn-danger btn-sm">Units Sold</span>

                                            @if($truck->bill_id)
                                            <a href="{{ route('view-vendor-bill', $truck->bill_id) }}" class="btn btn-secondary btn-sm">View Bill</a>
                                            <a href="{{ route('bill-book', $truck->bill_id) }}" class="btn btn-dark btn-sm">Bill Book</a>
                                            @else
                                            <a href="{{ route('Create-Bill', $truck->id) }}" class="btn btn-primary btn-sm">Create Bill</a>
                                            @endif
                                            @endif

                                            <a href="{{ route('sale-record', $truck->id) }}" class="btn btn-success btn-sm">Sale Record</a>
                                        </td>
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