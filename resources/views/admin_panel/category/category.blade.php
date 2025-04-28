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
                    <h6 class="page-title">Products</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
                        <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Category">
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
                                    <table id="example" class="display  table table--light" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>S.N.</th>
                                                <th>Name</th>
                                                <th>Urdu</th>
                                                <th>Products</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($all_categories as $categories)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $categories->category }}</td>
                                                <td>{{ $categories->category_urdu ?? '-' }}</td>
                                                <td>{{ $categories->products_count }}</td>
                                                <td>
                                                    <div class="button--group">

                                                        <button type="button" class="btn btn-sm btn-outline-primary editCategoryBtn"
                                                            data-toggle="modal"
                                                            data-target="#editcategory"
                                                            data-category-id="{{ $categories->id }}"
                                                            data-category-name="{{ $categories->category }}"
                                                            data-category-urdu="{{ $categories->category_urdu }}">
                                                            <i class="la la-pencil"></i>Edit
                                                        </button>

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

                <!--Add Modal -->
                <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><span class="type"></span> <span>Add Category</span></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <form action="{{ route('store-category') }}" method="POST">
                                @csrf

                                <div class="modal-body">

                                    <div class="form-group">
                                        <label>Name (English)</label>
                                        <input type="text" name="category" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Name (Urdu)</label>
                                        <input type="text" name="category_urdu" class="form-control" required>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Edit Category -->
                <div class="modal fade" id="editcategory" tabindex="-1" aria-labelledby="editcategoryLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editcategoryLabel">Edit Category</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('update-category') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <input type="hidden" id="editCategoryId" name="category_id" class="form-control" required>

                                        <div class="form-group">
                                            <label>Name (English)</label>
                                            <input type="text" id="editCategoryName" name="category_name" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Name (Urdu)</label>
                                            <input type="text" id="editCategoryUrdu" name="category_urdu" class="form-control" placeholder="Urdu Name" required>
                                        </div>
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
            $('.editCategoryBtn').click(function() {
                // Extract category ID and name from data attributes
                var categoryId = $(this).data('category-id');
                var categoryName = $(this).data('category-name');
                var categoryUrdu = $(this).data('category-urdu');
                // Set the extracted values in the modal fields
                $('#editCategoryId').val(categoryId);
                $('#editCategoryName').val(categoryName);
                $('#editCategoryUrdu').val(categoryUrdu);

            });
        });
    </script>