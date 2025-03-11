@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <!-- Page Title & Actions -->
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary">Truck Entry Details</h4>
                    <a href="{{ route('Truck-Entries') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <!-- Truck Details Card -->
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="text-secondary border-bottom pb-2">Truck Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Truck Number:</strong> {{ $truckEntry->truck_number }}</p>
                                <p><strong>Driver Name:</strong> {{ $truckEntry->driver_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Vendor:</strong> {{ $truckEntry->vendor_id }}</p>
                                <p><strong>Entry Date:</strong> {{ date('d-m-Y', strtotime($truckEntry->entry_date)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lot Details Table -->
                <div class="card shadow-lg mt-4">
                    <div class="card-body">
                        <h5 class="text-secondary border-bottom pb-2">Lot Details</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Category</th>
                                        <th>Variety</th>
                                        <th>Size</th>
                                        <th>Unit In</th>
                                        <th>Lot Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($truckEntry->lots as $lot)
                                        <tr>
                                            <td>{{ $lot->category }}</td>
                                            <td>{{ $lot->variety }}</td>
                                            <td>{{ $lot->unit }}</td>
                                            <td>{{ $lot->unit_in }}</td>
                                            <td>{{ $lot->lot_quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('Truck-Entries') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
</body>
