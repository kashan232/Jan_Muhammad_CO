@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Truck Entries</h6>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive--sm table-responsive">
                            <table id="example" class="display  table table--light" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Truck Number</th>
                                        <th>Driver Name</th>
                                        <th>Vendor</th>
                                        <th>Entry Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($truckEntries as $key => $entry)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $entry->truck_number }}</td>
                                        <td>{{ $entry->driver_name }}</td>
                                        <td>{{ $entry->vendor_id }}</td>
                                        <td>{{ date('d-m-Y', strtotime($entry->entry_date)) }}</td>
                                        <td>
                                            <a href="{{ route('Truck-Entry.Show', $entry->id) }}" class="btn btn-dark btn-sm">View</a>
                                            <a href="{{ route('Truck-Entry.Edit', $entry->id) }}" class="btn btn-primary btn-sm">Edit</a>
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