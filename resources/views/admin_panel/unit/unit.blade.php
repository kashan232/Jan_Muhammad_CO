<!-- meta tags and other links -->
@include('admin_panel.include.header_include')

<body>
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        <!-- sidebar end -->

        <!-- navbar-wrapper start -->
        @include('admin_panel.include.navbar_include')
        <!-- navbar-wrapper end -->

        <div class="body-wrapper">
            <div class="bodywrapper__inner">

                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Units</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">

                        <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                            data-modal_title="Add New Unit">
                            <i class="las la-plus"></i>Add New </button>

                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                @if (session()->has('success'))
                                <div class="alert alert-success">
                                    <strong>Success!</strong> {{ session('success') }}.
                                </div>
                                @endif
                                <div class="table-responsive--sm table-responsive">
                                    <table id="example" class="display  table table--light style--two bg--white" style="width:100%">

                                        <thead>
                                            <tr>
                                                <th>S.N.</th>
                                                <th>Name (English)</th>
                                                <th>Name (Urdu)</th> <!-- Added Urdu column -->
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($all_unit as $unit)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $unit->unit }}</td> <!-- English Name -->
                                                <td>{{ $unit->unit_urdu }}</td> <!-- Urdu Name -->
                                                <td>
                                                    <div class="button--group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline--primary editunitBtn" data-toggle="modal" data-modal_title="Edit Unit"
                                                            data-has_status="1" data-target="#editunit" data-unit-id="{{ $unit->id }}" data-unit-name="{{ $unit->unit }}"
                                                            data-unit-urdu="{{ $unit->unit_urdu }}">
                                                            <i class="la la-pencil"></i>Edit </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                    </table><!-- table end -->
                                </div>
                            </div>
                        </div><!-- card end -->
                    </div>
                </div>

                <!--Create & Update Modal -->
                <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><span class="type"></span> <span>Add Unit</span></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="{{ route('store-unit') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="unit" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Name (Urdu)</label> <!-- Added Urdu name field -->
                                        <input type="text" name="unit_urdu" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Unit -->
                <div class="modal fade" id="editunit" tabindex="-1" aria-labelledby="editunitLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editunitLabel">Edit Unit</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('update-unit') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name (English)</label>
                                        <input type="hidden" id="editUnitId" name="unit_id" class="form-control" required>
                                        <input type="text" id="editUnitName" name="unit_name" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Name (Urdu)</label> <!-- Added Urdu name field -->
                                        <input type="text" id="editUnitUrdu" name="unit_urdu" class="form-control">
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Update</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>
    @include('admin_panel.include.footer_include')

    <script>
        $(document).ready(function() {
            // Edit category button click event
            $('.editunitBtn').click(function() {
                var unitId = $(this).data('unit-id');
                var unitName = $(this).data('unit-name');
                var unitUrdu = $(this).data('unit-urdu'); // Get Urdu name

                $('#editUnitId').val(unitId);
                $('#editUnitName').val(unitName);
                $('#editUnitUrdu').val(unitUrdu); // Set Urdu name
            });

        });
    </script>