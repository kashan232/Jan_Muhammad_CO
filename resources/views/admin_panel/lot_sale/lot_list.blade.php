@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary">Lots Available for Sale</h4>
                </div>
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <form method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="customer_type" class="form-label">Customer Type</label>
                                    <select class="form-control" id="customer_type" required>
                                        <option value="">Select Type</option>
                                        <option value="credit">Credit Customer</option>
                                        <option value="cash">Cash Customer</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3" id="customer_select" style="display: none;">
                                    <label for="customer" class="form-label">Select Customer</label>
                                    <select class="form-control" id="customer">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" data-number="{{ $customer->customer_phone }}">
                                            {{ $customer->customer_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3" id="customer_number_container" style="display: none;">
                                    <label for="customer_number" class="form-label">Customer Number</label>
                                    <input type="text" class="form-control" id="customer_number" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="sale_date" class="form-label">Sale Date</label>
                                    <input type="date" class="form-control" id="sale_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <h4 class="fw-bold text-primary mt-4">Lots Available for Sale (Truck: {{ $truck->truck_number }})</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Variety</th>
                                            <th>Unit</th>
                                            <th>Unit In</th>
                                            <th>Total Units</th>
                                            <th>Available Units</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lots as $lot)
                                        <tr>
                                            <td>{{ $lot->category }}</td>
                                            <td>{{ $lot->variety }}</td>
                                            <td>{{ $lot->unit }}</td>
                                            <td>{{ $lot->unit_in }}</td>
                                            <td>{{ $lot->total_units }}</td>
                                            <td>{{ $lot->lot_quantity }}</td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="addSaleRow({{ $lot->id }}, '{{ $lot->category }}', '{{ $lot->variety }}', '{{ $lot->unit }}', {{ $lot->lot_quantity }})">
                                                    Add to Sale
                                                </button>


                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h4 class="fw-bold text-primary mt-4">Sale Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="saleTable">
                                    <thead>
                                        <tr>
                                            <th>Sale Type</th>
                                            <th>Available Units</th>
                                            <th>Sale Units</th>
                                            <th>Rate</th>
                                            <th>Weight</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <h5 class="fw-bold">Sub Total: <span id="subtotal">0</span></h5>
                                <button type="submit" class="btn btn-primary" id="submitSale">Submit Sale</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('customer_type').addEventListener('change', function() {
            let type = this.value;
            let customerSelect = document.getElementById('customer_select');
            let customerNumberContainer = document.getElementById('customer_number_container');

            if (type === 'credit') {
                customerSelect.style.display = 'block';
                customerNumberContainer.style.display = 'block';
            } else {
                customerSelect.style.display = 'none';
                customerNumberContainer.style.display = 'none';
            }
        });

        document.getElementById('customer').addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            document.getElementById('customer_number').value = selectedOption.dataset.number || '';
        });

        function addSaleRow(id, category, variety, unit, lotQuantity) {
            let table = document.querySelector("#saleTable tbody");
            let row = document.createElement("tr");

            row.setAttribute("data-lot-id", id); // Lot ID ko row mein set karein

            row.innerHTML = `
        <td>
            <select class="form-control sale-type" onchange="toggleWeightInput(this)" style="width: 150px;">
                <option value="unit">Sale in Unit</option>
                <option value="kg">Sale in Kg</option>
            </select>
        </td>
        <td>${lotQuantity}</td>
        <td><input type="number" style="width: 150px;" class="form-control quantity" min="1" max="${lotQuantity}" oninput="calculateAmount(this)"></td>
        <td><input type="number" style="width: 150px;" class="form-control price" min="1" oninput="calculateAmount(this)"></td>
        <td><input type="number" style="width: 150px;" class="form-control weight" disabled oninput="calculateAmount(this)"></td>
        <td class="amount" style="width: 150px;">0</td>
        <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
    `;

            table.appendChild(row);
        }


        function toggleWeightInput(element) {
            let row = element.closest("tr");
            let weightInput = row.querySelector(".weight");
            weightInput.disabled = element.value === "unit";
            weightInput.value = "";
            calculateAmount(element);
        }

        function calculateAmount(element) {
            let row = element.closest("tr");
            let saleType = row.querySelector(".sale-type").value;
            let price = parseFloat(row.querySelector(".price").value) || 0;
            let quantity = parseFloat(row.querySelector(".quantity").value) || 0;
            let weight = parseFloat(row.querySelector(".weight").value) || 0;
            let amount = saleType === "unit" ? quantity * price : weight * price;
            row.querySelector(".amount").innerText = amount.toFixed(2);

            updateSubtotal(); // Subtotal ko update karne ke liye call
        }

        document.getElementById('customer').addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            let customerNumber = selectedOption.getAttribute('data-number') || '';
            document.getElementById('customer_number').value = customerNumber;
        });

        function updateSubtotal() {
            let subtotal = 0;
            document.querySelectorAll("#saleTable tbody tr").forEach(row => {
                let amount = parseFloat(row.querySelector(".amount").innerText) || 0;
                subtotal += amount;
            });
            document.querySelector("#subtotal").innerText = subtotal.toFixed(2);
        }

        function removeRow(button) {
            let row = button.closest("tr");
            row.remove();
            updateSubtotal(); // Row remove hone ke baad subtotal update karega
        }
    </script>

    <script>
        document.getElementById("submitSale").addEventListener("click", function(event) {
            event.preventDefault(); // Page reload hone se rokta hai

            let salesData = [];
            document.querySelectorAll("#saleTable tbody tr").forEach(row => {
                let saleType = row.querySelector(".sale-type").value;
                let quantity = parseFloat(row.querySelector(".quantity").value);
                let price = parseFloat(row.querySelector(".price").value);
                let lotId = row.getAttribute("data-lot-id"); // Lot ID ko extract karein

                if (lotId) { // Ensure ke lot_id null na ho
                    salesData.push({
                        lot_id: lotId,
                        quantity: quantity,
                        price: price
                    });
                }
            });

            let saleDate = document.getElementById("sale_date").value;
            let customerType = document.getElementById("customer_type").value;
            let customerId = document.getElementById("customer").value || null;

            fetch("{{ route('lot.sale.store') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        customer_type: customerType,
                        customer_id: customerId,
                        sale_date: saleDate,
                        sales: salesData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Success!",
                            text: data.message,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            location.reload(); // Page reload after user clicks OK
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Error: " + data.message,
                            icon: "error",
                            confirmButtonText: "Try Again"
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: "Something went wrong. Please try again.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });

        });
    </script>

</body>