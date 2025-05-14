@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">

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
                                            <a href="{{ route('Truck-Entry.Show',   $entry->id) }}" class="btn btn-dark btn-sm">View</a>
                                            <a href="{{ route('Truck-Entry.Edit',   $entry->id) }}" class="btn btn-primary btn-sm">Edit</a>

                                            <!-- Delete button with route URL -->
                                            <button
                                                class="btn btn-danger btn-sm btn-delete-entry"
                                                data-url="{{ route('Truck-Entry.Destroy', $entry->id) }}">Delete</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>

                                <!-- CSRF token for AJAX -->
                                <meta name="csrf-token" content="{{ csrf_token() }}">

                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set CSRF header for all AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            // Delegate click on delete buttons
            $(document).on('click', '.btn-delete-entry', function() {
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently remove the truck entry and its lots.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire('Deleted!', response.message, 'success')
                                    .then(() => $('button[data-url="' + url + '"]').closest('tr').remove());
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON?.message || 'Something went wrong.';
                                Swal.fire('Cannot delete', msg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>


</body>