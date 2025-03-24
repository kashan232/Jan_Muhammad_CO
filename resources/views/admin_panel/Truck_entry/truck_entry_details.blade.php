@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <!-- Page Title & Actions -->
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary" data-translate data-eng="Truck Entry Details" data-urdu="ٹرک اندراج کی تفصیلات">
                        Truck Entry Details
                    </h4>
                    <div class="d-flex gap-2">
                        <button id="toggleLanguage" class="btn btn-outline-primary">اردو میں دیکھیں</button>
                        <a href="{{ route('Truck-Entries') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> 
                            <span data-translate data-eng="Back to List" data-urdu="فہرست میں واپس جائیں">Back to List</span>
                        </a>
                    </div>
                </div>

                <!-- Truck Details Card -->
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="text-secondary border-bottom pb-2" data-translate data-eng="Truck Information" data-urdu="ٹرک کی معلومات">
                            Truck Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p>
                                    <strong data-translate data-eng="Truck Number:" data-urdu="ٹرک نمبر:">Truck Number:</strong> 
                                    {{ $truckEntry->truck_number }}
                                </p>
                                <p>
                                    <strong data-translate data-eng="Driver Name:" data-urdu="ڈرائیور کا نام:">Driver Name:</strong> 
                                    {{ $truckEntry->driver_name }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <strong data-translate data-eng="Vendor:" data-urdu="سپلائر:">Vendor:</strong> 
                                    {{ $truckEntry->vendor_id }}
                                </p>
                                <p>
                                    <strong data-translate data-eng="Entry Date:" data-urdu="اندراج کی تاریخ:">Entry Date:</strong> 
                                    {{ date('d-m-Y', strtotime($truckEntry->entry_date)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lot Details Table -->
                <div class="card shadow-lg mt-4">
                    <div class="card-body">
                        <h5 class="text-secondary border-bottom pb-2" data-translate data-eng="Lot Details" data-urdu="لاٹ کی تفصیلات">
                            Lot Details
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th data-translate data-eng="Category" data-urdu="زمرہ">Category</th>
                                        <th data-translate data-eng="Variety" data-urdu="قسم">Variety</th>
                                        <th data-translate data-eng="Size" data-urdu="سائز">Size</th>
                                        <th data-translate data-eng="Unit In" data-urdu="یونٹ ان">Unit In</th>
                                        <th data-translate data-eng="Total Units" data-urdu="کل یونٹس">Total Units</th>
                                        <th data-translate data-eng="Available Units" data-urdu="دستیاب یونٹس">Available Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($truckEntry->lots as $lot)
                                        <tr>
                                            <td>{{ $lot->category }}</td>
                                            <td>{{ $lot->variety }}</td>
                                            <td>{{ $lot->unit }}</td>
                                            <td>{{ $lot->unit_in }}</td>
                                            <td>{{ $lot->total_units }}</td>
                                            <td>{{ $lot->lot_quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('Truck-Entries') }}" class="btn btn-secondary text-white">
                                <i class="fas fa-arrow-left"></i> 
                                <span data-translate class="text-white" data-eng="Back to List" data-urdu="فہرست میں واپس جائیں">Back to List</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <!-- Language Toggle Script -->
    <script>
        document.getElementById("toggleLanguage").addEventListener("click", function() {
            let elements = document.querySelectorAll("[data-translate]"); 
            let isUrdu = this.getAttribute("data-urdu") === "true"; 

            elements.forEach(element => {
                let engText = element.getAttribute("data-eng");
                let urduText = element.getAttribute("data-urdu");

                element.innerText = isUrdu ? engText : urduText;
            });

            this.innerText = isUrdu ? "اردو میں دیکھیں" : "View in English";
            this.setAttribute("data-urdu", isUrdu ? "false" : "true");
        });
    </script>
</body>
