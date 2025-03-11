@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Truck Entry</h6>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                        @endif

                        <form action="{{ route('Truck-Entry.Store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <!-- Truck Number -->
                                <div class="col-md-4">
                                    <label>Truck Number</label>
                                    <input type="text" name="truck_number" class="form-control" required>
                                </div>

                                <!-- Driver Name -->
                                <div class="col-md-4">
                                    <label>Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control" required>
                                </div>

                                <!-- Driver CNIC -->
                                <div class="col-md-4">
                                    <label>Driver CNIC</label>
                                    <input type="text" name="driver_cnic" class="form-control">
                                </div>

                                <!-- Driver Contact -->
                                <div class="col-md-4 mt-2">
                                    <label>Driver Contact</label>
                                    <input type="text" name="driver_contact" class="form-control">
                                </div>

                                <!-- Vendor (Party) -->
                                <div class="col-md-4 mt-2">
                                    <label>Vendor (Party)</label>
                                    <select name="vendor_id" class="form-control">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Entry Date -->
                                <div class="col-md-4 mt-2">
                                    <label>Entry Date</label>
                                    <input type="date" name="entry_date" class="form-control" required>
                                </div>
                            </div>

                            <hr>

                            <!-- LOT ENTRY TABLE -->
                            <h5>Lot Details</h5>
                            <table class="table table-bordered" id="lotTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Variety</th>
                                        <th>Size</th>
                                        <th>Unit In</th>
                                        <th>Lot Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="category[]" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->category }}">{{ $category->category }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="variety[]" class="form-control">
                                                <option value="">Select Variety</option>
                                                @foreach($varieties as $variety)
                                                <option value="{{ $variety->brand }}">{{ $variety->brand }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control">
                                                <option value="">Select Unit</option>
                                                @foreach($Units as $Unit)
                                                <option value="{{ $Unit->unit }}">{{ $Unit->unit }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="unit_in[]" class="form-control">
                                                <option value="" disabled>Select Unit In</option>
                                                <option value="Bori">Bori</option>
                                                <option value="Katta">Katta</option>
                                                <option value="Jali">Jali</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="lot_quantity[]" class="form-control">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-primary mt-2 mb-2" id="addMore">+ Add More Lot</button>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">Save Truck Entry</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        // Add More Lot Entry
        document.getElementById("addMore").addEventListener("click", function() {
            let table = document.getElementById("lotTable").getElementsByTagName("tbody")[0];
            let newRow = table.insertRow();

            newRow.innerHTML = `
                <td>
                    <select name="category[]" class="form-control">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category }}">{{ $category->category }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="variety[]" class="form-control">
                        <option value="">Select Variety</option>
                        @foreach($varieties as $variety)
                            <option value="{{ $variety->brand }}">{{ $variety->brand }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="unit[]" class="form-control">
                        <option value="">Select Unit</option>
                        @foreach($Units as $Unit)
                            <option value="{{ $Unit->unit }}">{{ $Unit->unit }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="unit_in[]" class="form-control">
                        <option value="" disabled>Select Unit In</option>
                        <option value="Bori">Bori</option>
                        <option value="Katta">Katta</option>
                        <option value="Jali">Jali</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="lot_quantity[]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="fas fa-trash"></i></button>
                </td>
            `;
        });

        // Remove Row
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-row")) {
                e.target.closest("tr").remove();
            }
        });
    </script>
</body>