@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-30 flex-wrap gap-3 justify-content-between align-items-center">
                    <h6 class="page-title">Truck Entry Edit</h6>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                        @endif

                        <form action="{{ route('truck_entries.update', $truckEntry->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <!-- Truck Number -->
                                <div class="col-md-4">
                                    <label>Truck Number</label>
                                    <input type="text" name="truck_number" class="form-control" value="{{ $truckEntry->truck_number }}" required>
                                </div>

                                <!-- Driver Name -->
                                <div class="col-md-4">
                                    <label>Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control" value="{{ $truckEntry->driver_name }}" required>
                                </div>

                                <!-- Driver CNIC -->
                                <div class="col-md-4">
                                    <label>Driver CNIC</label>
                                    <input type="text" name="driver_cnic" class="form-control" value="{{ $truckEntry->driver_cnic }}">
                                </div>

                                <!-- Driver Contact -->
                                <div class="col-md-4 mt-2">
                                    <label>Driver Contact</label>
                                    <input type="text" name="driver_contact" class="form-control" value="{{ $truckEntry->driver_contact }}">
                                </div>

                                <!-- Vendor (Party) -->
                                <div class="col-md-4 mt-2">
                                    <label>Vendor (Party)</label>
                                    <select name="vendor_id" class="form-control">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->name }}" {{ $truckEntry->vendor_id == $vendor->name ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Entry Date -->
                                <div class="col-md-4 mt-2">
                                    <label>Entry Date</label>
                                    <input type="date" name="entry_date" class="form-control" value="{{ $truckEntry->entry_date }}" required>
                                </div>
                            </div>

                            <hr>

                            <!-- LOT ENTRY TABLE -->
                            <h5>Lot Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="lotTable">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Variety</th>
                                            <th>Size</th>
                                            <th>Unit In</th>
                                            <th>Lot Quantity</th>
                                            <th>Update Lot</th> <!-- New Field -->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lotEntries as $lot)
                                        <tr>
                                            <td>
                                                <select name="category[]" class="form-control">
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                    <option value="{{ $category->category }}" {{ $lot->category == $category->category ? 'selected' : '' }}>
                                                        {{ $category->category }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="variety[]" class="form-control">
                                                    <option value="">Select Variety</option>
                                                    @foreach($varieties as $variety)
                                                    <option value="{{ $variety->brand }}" {{ $lot->variety == $variety->brand ? 'selected' : '' }}>
                                                        {{ $variety->brand }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="unit[]" class="form-control">
                                                    <option value="">Select Unit</option>
                                                    @foreach($Units as $Unit)
                                                    <option value="{{ $Unit->unit }}" {{ $lot->unit == $Unit->unit ? 'selected' : '' }}>
                                                        {{ $Unit->unit }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="unit_in[]" class="form-control">
                                                    <option value="">Select Unit In</option>
                                                    @foreach($UnitIns as $Unitin)
                                                    <option value="{{ $Unitin->unit_in }}" {{ $lot->unit_in == $Unitin->unit_in ? 'selected' : '' }}>
                                                        {{ $Unitin->unit_in }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="lot_quantity[]" class="form-control" value="{{ $lot->lot_quantity }}" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="update_lot[]" class="form-control" value="0"> <!-- New Field -->
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>

                            <!-- <button type="button" class="btn btn-primary mt-2 mb-2" id="addMore">+ Add More Lot</button> -->

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">Update Truck Entry</button>
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