@include('admin_panel.include.header_include')
<style>
    .badge.bg--success {
        background-color: #28a745;
        color: white;
    }

    .badge.bg--danger {
        background-color: #dc3545;
        color: white;
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
                    <h6 class="page-title">Vendors</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
                        <button type="button" class="btn btn-outline--primary cuModalBtn" data-modal_title="Add New Vendors">
                            <i class="la la-plus"></i>Add New </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                                @endif

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
                                                <th>Name</th>
                                                <th>Urdu Name</th>
                                                <th>Mobile</th>
                                                <th>Opening Balance</th>
                                                <th>Status</th> {{-- New Column --}}
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($Suppliers as $Supplier)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Supplier->name }}</td>
                                                <td>{{ $Supplier->urdu_name }}</td>
                                                <td>
                                                    <span class="fw-bold">{{ $Supplier->mobile }}</span><br>
                                                    <a href="#" class="__cf_email__">{{ $Supplier->email }}</a>
                                                </td>
                                                <td>{{ $Supplier->opening_balance }}</td>
                                                <td>
                                                    <span class="badge {{ $Supplier->status == 0 ? 'bg--success' : 'bg--danger' }}">
                                                        {{ $Supplier->status == 0 ? 'Active' : 'Disabled' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="button--group d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline--primary editCategoryBtn" data-toggle="modal" data-target="#exampleModal"
                                                            data-supplier-id="{{ $Supplier->id }}"
                                                            data-supplier-name="{{ $Supplier->name }}"
                                                            data-supplier-mobile="{{ $Supplier->mobile }}"
                                                            data-city="{{ $Supplier->city }}"
                                                            data-area="{{ $Supplier->area }}"
                                                            data-opening-balance="{{ $Supplier->opening_balance }}"
                                                            data-supplier-urdu="{{ $Supplier->urdu_name }}">
                                                            <i class="la la-pencil"></i>Edit
                                                        </button>

                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input toggle-supplier-status" type="checkbox"
                                                                role="switch" data-id="{{ $Supplier->id }}" {{ $Supplier->status == 1 ? 'checked' : '' }}>
                                                            <label class="form-check-label">Disable</label>
                                                        </div>
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

                <div class="modal fade" id="cuModal">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="{{ route('store-supplier') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" autocomplete="off" value="" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Urdu Name</label>
                                                <input type="text" name="urdu_name" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">Mobile <i class="fa fa-info-circle text--primary" title="Type the mobile number including the country code. Otherwise, SMS won't send to that number.">
                                                    </i>
                                                </label>
                                                <input type="number" name="mobile" value="" class="form-control " required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" name="city" class="form-control" autocomplete="off" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Area</label>
                                                <input type="text" name="area" class="form-control" autocomplete="off" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Opening Balance</label>
                                                <input type="number" name="opening_balance" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Edit Vendor</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('update-supplier') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="hidden" name="supplier_id" id="supplier_id">
                                                <input type="text" name="name" id="suplier_name" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Urdu Name</label>
                                                <input type="text" name="urdu_name" id="supplier_urdu_name" class="form-control" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">Mobile <i class="fa fa-info-circle text--primary" title="Type the mobile number including the country code. Otherwise, SMS won't send to that number.">
                                                    </i>
                                                </label>
                                                <input type="number" name="mobile" id="suplier_mobile" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" name="city" id="sup_city" class="form-control" autocomplete="off" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Area</label>
                                                <input type="text" name="area" id="sup_area" class="form-control" autocomplete="off" value="">
                                            </div>
                                        </div>
                                        <!-- Display only: Opening Balance (readonly) -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Current Opening Balance</label>
                                                <input type="number" class="form-control" readonly id="supplier_opening_balance">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Adjustment Type</label>
                                                <select name="adjustment_type" class="form-control" id="adjustment_type">
                                                    <option value="">Select Adjustment</option>
                                                    <option value="plus">Plus (+)</option>
                                                    <option value="minus">Minus (-)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Receipt Opening Balance -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Receipt Opening Balance</label>
                                                <input type="number" name="receipt_opening_balance" class="form-control" placeholder="Enter amount for adjustment">
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div><!-- bodywrapper__inner end -->
        </div><!-- body-wrapper end -->
    </div>
    @include('admin_panel.include.footer_include')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('.editCategoryBtn').click(function() {
                var supplierId = $(this).data('supplier-id');
                var supplierName = $(this).data('supplier-name');
                var supplierMobile = $(this).data('supplier-mobile');
                var supplierCity = $(this).data('city');
                var supplierArea = $(this).data('area');
                var supplierUrdu = $(this).data('supplier-urdu');
                var supplierOpeningBalance = $(this).data('opening-balance');

                // Populate the fields
                $('#supplier_id').val(supplierId);
                $('#suplier_name').val(supplierName); // Ensure the correct ID here
                $('#suplier_mobile').val(supplierMobile); // Ensure the correct ID here
                $('#sup_city').val(supplierCity); // Ensure the correct ID here
                $('#sup_area').val(supplierArea); // Ensure the correct ID here
                $('#supplier_urdu_name').val(supplierUrdu); // Ensure the correct ID here
                $('#supplier_opening_balance').val(supplierOpeningBalance); // Ensure the correct ID here

                // Reset the adjustment fields
                $('#adjustment_type').val('');
                $('input[name="receipt_opening_balance"]').val('');
            });
        });
    </script>

    <script>
        $('.toggle-supplier-status').change(function() {
            var supplierId = $(this).data('id');
            var newStatus = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('toggle-supplier-status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: supplierId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Updated!', response.message, 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Could not connect to server.', 'error');
                }
            });
        });
    </script>