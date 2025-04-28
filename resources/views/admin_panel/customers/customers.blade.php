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
                    <h6 class="page-title">All Customer</h6>
                    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
                        <form action="" method="GET" class="d-flex gap-2">
                            <div class="input-group w-auto">
                                <input type="search" name="search" class="form-control bg--white" placeholder="Username" value="">
                                <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
                            </div>

                        </form>
                        <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Customer">
                            <i class="las la-plus"></i>Add New </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}
                        </div>
                        @endif
                        <div class="card b-radius--10">
                            <div class="card-body p-0">
                                <div class="table-responsive--sm table-responsive">
                                    <table id="example" class="display  table table--light" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>S.N.</th>
                                                <th>Name</th>
                                                <th>Urdu Name</th>
                                                <th>Phone</th>
                                                <th>City | Area</th>
                                                <th>Opening Balance</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($Customers as $Customer)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $Customer->customer_name }}</td>
                                                <td>{{ $Customer->customer_name_urdu }}</td>
                                                <td>{{ $Customer->customer_phone }}</td>
                                                <td>{{ $Customer->city }}<br>{{ $Customer->area }}</td>
                                                <td>{{ $Customer->opening_balance }}</td>
                                                <td>
                                                    <div class="button--group">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline--primary editcustomerbtn"
                                                            data-toggle="modal"
                                                            data-target="#exampleModal"
                                                            data-customer-id="{{ $Customer->id }}"
                                                            data-customer-name="{{ $Customer->customer_name }}"
                                                            data-customer-urdu="{{ $Customer->customer_name_urdu }}"
                                                            data-customer-city="{{ $Customer->city }}"
                                                            data-customer-area="{{ $Customer->area }}"
                                                            data-customer-phone="{{ $Customer->customer_phone }}"
                                                            data-opening-balance="{{ $Customer->opening_balance }}">
                                                            <i class="la la-pencil"></i>Edit
                                                        </button>

                                                    </div>
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
                <!-- Create Update Modal -->
                <div class="modal fade" id="cuModal">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>

                            <form action="{{ route('store-customer') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Urdu Name</label> <!-- Add this -->
                                        <input type="text" class="form-control" name="customer_name_urdu">
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="text" class="form-control" name="customer_phone">
                                    </div>
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="city">
                                    </div>
                                    <div class="form-group">
                                        <label>Area</label>
                                        <input type="text" class="form-control" name="area">
                                    </div>
                                    <div class="form-group">
                                        <label>Opening Balance</label>
                                        <input type="text" class="form-control" name="opening_balance">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Edit Customer</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('update-customer') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="hidden" name="customer_id" id="customer_id">
                                        <input type="text" class="form-control" id="edit_customer_name" name="customer_name" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Urdu Name</label> <!-- Add this -->
                                        <input type="text" class="form-control" id="edit_customer_name_urdu" name="customer_name_urdu">
                                    </div>

                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="text" class="form-control" id="edit_customer_phone" name="customer_phone">
                                    </div>
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="city" id="customer_city">
                                    </div>
                                    <div class="form-group">
                                        <label>Area</label>
                                        <input type="text" class="form-control" name="area" id="customer_area">
                                    </div>

                                    <div class="form-group">
                                        <label>Opening Balance</label>
                                        <input type="text" class="form-control" name="opening_balance" id="edit_opening_balance">
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

    <script>
        $(document).ready(function() {
            // Edit category button click event
            $('.editcustomerbtn').click(function() {
                // Extract category ID and name from data attributes
                var customerId = $(this).data('customer-id');
                var customername = $(this).data('customer-name');
                var customerphone = $(this).data('customer-phone');
                var customeraddress = $(this).data('customer-address');
                var customercity = $(this).data('customer-city');
                var customerarea = $(this).data('customer-area');
                var customeropeningbalance = $(this).data('opening-balance');
                var customerurdu = $(this).data('customer-urdu');

                $('#customer_id').val(customerId);
                $('#edit_customer_name').val(customername);
                $('#customer_city').val(customercity);
                $('#customer_area').val(customerarea);
                $('#edit_customer_phone').val(customerphone);
                $('#edit_customer_address').val(customeraddress);
                $('#edit_opening_balance').val(customeropeningbalance);
$('#edit_customer_name_urdu').val(customerurdu);
            });
        });
    </script>