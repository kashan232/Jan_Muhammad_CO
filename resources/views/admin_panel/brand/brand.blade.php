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
                    <h6 class="page-title">Variety</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">

                        <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                            data-modal_title="Add New Variety">
                            <i class="las la-plus"></i>Add New </button>

                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card b-radius--10 ">
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
                                                <th>Name(Eng)</th>
                                                <th>Name(Urdu)</th>
                                                <th>Products</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($all_brand as $brand)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $brand->brand }}</td>
                                                <td>{{ $brand->brand_urdu }}</td>
                                                <td>{{ $brand->products_count }}</td>
                                                <td>
                                                    <div class="button--group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline--primary editbrandBtn" data-toggle="modal"
                                                            data-modal_title="Edit Brand" data-has_status="1"
                                                            data-target="#editbrand" data-brand-id="{{ $brand->id }}" data-brand-name="{{ $brand->brand }}" data-brand-name-urdu="{{ $brand->brand_urdu }}">
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

                <!--Create Update Modal -->
                <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><span class="type"></span> <span>Add Variety</span></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="{{ route('store-brand') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Brand Name (English)</label>
                                        <input type="text" name="brand" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Brand Name (Urdu)</label>
                                        <input type="text" name="brand_urdu" class="form-control" dir="rtl" style="font-family: 'Noto Nastaliq Urdu', serif;">
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Brand -->
                <div class="modal fade" id="editbrand" tabindex="-1" aria-labelledby="editbrandLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editbrandLabel">Edit  Variety	</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('update-brand') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="hidden" id="editbrandId" name="brand_id" class="form-control" required>
                                        <input type="text" id="editbrandName" name="brand_name" class="form-control">
                                        <input type="text" id="editbrandNameUrdu" name="brand_name_urdu" class="form-control mt-2" dir="rtl" style="font-family: 'Noto Nastaliq Urdu', serif;">
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Import Brand</h4>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="la la-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <form method="post" action="#">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="fw-bold">Select File</label>
                                        <input type="file" class="form-control" name="file" accept=".csv" required>
                                        <div class="mt-1">
                                            <small class="d-block">
                                                Supported files: <b class="fw-bold">csv</b>
                                            </small>
                                            <small>
                                                Download sample template file from here <a
                                                    href="https://script.viserlab.com/torylab/assets/files/sample/brand.csv"
                                                    title="Download csv file" class="text--primary" download>
                                                    <b>csv</b>
                                                </a>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="Submit" class="btn btn--primary w-100 h-45">Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmation Alert!</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="" method="POST">
                                <input type="hidden" name="_token" value="zv105s8kd1s2nyZ6nvoqU6pROYAnsCPYkYXTDlWn">
                                <div class="modal-body">
                                    <p class="question"></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">No</button>
                                    <button type="submit" class="btn btn--primary">Yes</button>
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
            $('.editbrandBtn').click(function() {
                var brandId = $(this).data('brand-id');
                var brandName = $(this).data('brand-name');
                var brandNameUrdu = $(this).data('brand-name-urdu');

                $('#editbrandId').val(brandId);
                $('#editbrandName').val(brandName);
                $('#editbrandNameUrdu').val(brandNameUrdu);
            });
        });
    </script>