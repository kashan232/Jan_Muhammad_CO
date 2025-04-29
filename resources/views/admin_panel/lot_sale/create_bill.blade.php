@include('admin_panel.include.header_include')
<meta name="csrf-token" content="{{ csrf_token() }}">

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex mb-4 flex-wrap gap-3 justify-content-between align-items-center">
                    <h4 class="fw-bold text-primary">Create Bill For Vendor</h4>
                </div>
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <form method="POST" id="billForm" onsubmit="return validateBill()">
                            @csrf
                            <h4 class="fw-bold text-primary mt-4">Create Bill For Vendor (Truck: {{ $truck->truck_number }})</h4>
                            <input type="hidden" name="truck_id" value="{{ $truck->id }}">
                            <input type="hidden" name="truck_number" value="{{ $truck->truck_number }}">
                            <input type="hidden" name="vendor_id" value="{{ $vendor_id }}">
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
                                            <th>Total Weight</th>
                                            <th>Sale Average</th>
                                            <th>Total Sale</th>
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
                                            <td>{{ $lot->total_weight }}</td>
                                            <td>{{ number_format($lot->average_sale, 2) }}</td>
                                            <td>{{ number_format($lot->total_sale, 2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="addBillRow({{ $lot->id }}, '{{ $lot->category }}', '{{ $lot->variety }}', '{{ $lot->unit }}', {{ $lot->total_units }}, '{{ $lot->unit_in }}', '{{ $lot->total_weight }}')">
                                                    Add to Bill
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                            <h4 class="fw-bold text-primary mt-5">Bill Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="billTable">
                                    <thead>
                                        <tr>
                                            <th>Bill Type</th>
                                            <th>Total Units</th>
                                            <th>Total Weight</th> <!-- New Column -->
                                            <th>Sale Units</th>
                                            <th>Rate</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <h5 class="fw-bold">Sub Total: <span id="subtotal">0</span></h5>
                            </div>
                            <hr>
                            <h4 class="fw-bold text-primary mt-5">Mazdori Details</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="mazdoriTable">
                                    <thead>
                                        <tr>
                                            <th>Unit In</th>
                                            <th>Total Units</th>
                                            <th>Price Per Lot</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <h5 class="fw-bold mb-0">
                                    Total Mazdori: <span id="totalMazdori">0</span>
                                    <!-- <a onclick="copyMazdori()" class="btn btn-sm btn-danger" title="Copy">
                                        <i class="bi bi-clipboard"></i>
                                    </a> -->
                                </h5>

                            </div>

                            <hr>
                            <div class="card mt-5 p-4 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Expenses</h5>
                                    <button type="button" class="btn btn-sm btn-success" onclick="addExpenseRow()">Add Expense</button>
                                </div>

                                <table class="table table-bordered" id="expenseTable">
                                    <thead>
                                        <tr>
                                            <th>Expense Category</th>
                                            <th>Value (Amount or %)</th>
                                            <th>Final Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>

                                <h5 class="mt-3 fw-bold">Total Expenses: <span id="totalExpense">0</span></h5>
                                <h4 class="mt-3 fw-bold text-success">Net Pay to Vendor: <span id="netPay">0</span></h4>
                                <input type="hidden" name="net_pay_to_vendor" id="net_pay_to_vendor">
                                <div class="d-flex align-items-center gap-2">
                                    <select id="adjustment_type" class="form-control" style="width: 80px;" onchange="calculateExpenses()">
                                        <option value="+">+</option>
                                        <option value="-">-</option>
                                    </select>
                                    <input type="number" id="adjustment" class="form-control" oninput="calculateExpenses()" placeholder="Adjustment" style="max-width: 150px;">
                                </div>


                            </div>

                            <button type="submit" class="btn btn-primary" id="submitBill">Save Bill</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <script>
        function addBillRow(id, category, variety, unit, total_units, unit_in, total_weight) {
            const billTable = document.querySelector('#billTable tbody');

            const lastRow = billTable.querySelector("tr:last-child");
            if (lastRow) {
                const lastQty = lastRow.querySelector(".sale-units")?.value;
                const lastRate = lastRow.querySelector(".rate")?.value;
                if (!lastQty || !lastRate) {
                    Swal.fire('Wait!', 'Please complete the previous row first.', 'warning');
                    return;
                }
            }

            const billRow = document.createElement('tr');
            billRow.innerHTML = `
    <td>
        ${category} - ${variety} (${unit})<br>
        <small class="text-muted">Unit In: ${unit_in}</small>
        <input type="hidden" name="lots[]" value="${id}">
        <input type="hidden" name="unit_in[${id}]" value="${unit_in}">
    </td>
    <td>${total_units}</td>
    <td>
        <input type="number" name="weight[]" class="form-control weight" step="any" min="0" value="${total_weight}" required>
    </td>
    <td><input type="number" name="sale_units[]" style="width:130px;" class="form-control sale-units" min="1" max="${total_units}" required></td>
    <td><input type="number" name="rate[]"  style="width:130px;" class="form-control rate" min="1" required></td>
    <td><input type="number" name="amount[]" style="width:130px;" class="form-control amount" readonly></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeBothRows(this, ${id})">Remove</button></td>
`;
            billTable.appendChild(billRow);

            billRow.querySelector('.sale-units').addEventListener('input', updateRowAmount);
            billRow.querySelector('.rate').addEventListener('input', updateRowAmount);

            addMazdoriRow(id, total_units, unit_in);
        }

        function copyMazdori() {
            const value = document.getElementById('totalMazdori').innerText;
            navigator.clipboard.writeText(value).then(() => {}).catch(err => {});
        }


        function updateRowAmount(e) {
            const row = e.target.closest('tr');
            const units = parseFloat(row.querySelector('.sale-units').value) || 0;
            const rate = parseFloat(row.querySelector('.rate').value) || 0;
            const amount = units * rate;
            row.querySelector('.amount').value = Math.round(amount);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.amount').forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) total += val;
            });
            document.getElementById('subtotal').textContent = Math.round(total);
            calculateExpenses();
        }

        function addMazdoriRow(id, total_units, unit_in) {
            // Check if row already exists
            if (document.getElementById(`mazdori_row_${id}`)) {
                return; // Already exists, don't add again
            }

            const table = document.querySelector('#mazdoriTable tbody');
            const row = document.createElement('tr');
            row.setAttribute("id", `mazdori_row_${id}`);

            row.innerHTML = `
        <td><input type="text" name="mazdori_unit_in[${id}]" class="form-control" value="${unit_in}" readonly></td>
        <td><input type="number" name="mazdori_units[${id}]" class="form-control" value="${total_units}" readonly></td>
        <td><input type="number" name="mazdori_price_per_lot[${id}]" class="form-control price-per-lot" data-id="${id}" required></td>
        <td><input type="number" name="mazdori_total[${id}]" class="form-control mazdori-total" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeBothRows(this, ${id})">Remove</button></td>
    `;
            table.appendChild(row);

            row.querySelector('.price-per-lot').addEventListener('input', calculateMazdoriTotal);
        }

        function calculateMazdoriTotal() {
            let totalMazdori = 0;
            document.querySelectorAll('.price-per-lot').forEach(input => {
                const id = input.dataset.id;
                const price = parseFloat(input.value) || 0;
                const units = parseFloat(document.querySelector(`input[name="mazdori_units[${id}]"]`).value) || 0;
                const total = price * units;
                document.querySelector(`input[name="mazdori_total[${id}]"]`).value = Math.round(total);
            });

            document.querySelectorAll('.mazdori-total').forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) totalMazdori += val;
            });

            document.getElementById('totalMazdori').textContent = Math.round(totalMazdori);
        }

        function removeBothRows(btn, id) {
            btn.closest('tr').remove();

            const mazdoriRow = document.getElementById(`mazdori_row_${id}`);
            if (mazdoriRow) mazdoriRow.remove();

            calculateTotal();
            calculateMazdoriTotal();
        }

        function calculateExpenses() {
            const subtotal = parseFloat(document.getElementById('subtotal').textContent) || 0;
            const totalMazdori = parseFloat(document.getElementById('totalMazdori').textContent) || 0;
            let adjustmentRaw = document.getElementById('adjustment').value;
            let adjustmentValue = adjustmentRaw === '' ? null : parseFloat(adjustmentRaw);
            const adjustmentType = document.getElementById('adjustment_type').value;

            if (adjustmentType === '-') {
                adjustmentValue = -adjustmentValue;
            }

            let totalExpense = 0;

            document.querySelectorAll('#expenseTable tbody tr').forEach(row => {
                const type = row.querySelector('.expense-type').value;
                const value = parseFloat(row.querySelector('.expense-input').value) || 0;

                let final = 0;

                if (type === 'Commission') {
                    final = subtotal * (value / 100);
                    totalExpense += final;
                } else {
                    final = value;
                    totalExpense += final;
                }

                row.querySelector('.expense-final').value = Math.round(final);
            });

            document.getElementById('totalExpense').textContent = Math.round(totalExpense);

            // ***** UPDATED FORMULA here *****
            const netPay = subtotal - totalExpense + (adjustmentValue ?? 0);

            document.getElementById('netPay').textContent = Math.round(netPay);
            document.getElementById('net_pay_to_vendor').value = Math.round(netPay);
        }



        function validateBill() {
            const hasBillRows = document.querySelector('#billTable tbody').children.length;
            if (!hasBillRows) {
                Swal.fire('Empty!', 'Please add at least one bill row.', 'warning');
                return false;
            }
            return true;
        }

        function addExpenseRow() {
            const totalMazdori = document.getElementById('totalMazdori').innerText || 0;
            const row = document.createElement('tr');
            row.innerHTML = `
        <td>
            <select class="form-control expense-type" onchange="handleTypeChange(this)">
                <option value="">Select</option>
                <option value="Mazdori">Mazdori</option>
                <option value="Commission">Commission (%)</option>
                <option value="Rent">Rent</option>
                <option value="Market Tax">Market Tax</option>
            </select>
        </td>
        <td><input type="number" class="form-control expense-input" oninput="calculateExpenses()"></td>
        <td><input type="number" class="form-control expense-final" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); calculateExpenses();">Remove</button></td>
    `;
            document.querySelector('#expenseTable tbody').appendChild(row);
        }

        function handleTypeChange(select) {
            const row = select.closest('tr');
            const input = row.querySelector('.expense-input');
            const final = row.querySelector('.expense-final');

            if (select.value === "Mazdori") {
                const totalMazdori = parseFloat(document.getElementById('totalMazdori').innerText) || 0;
                input.value = totalMazdori;
                final.value = totalMazdori;
            } else {
                input.value = '';
                final.value = '';
            }

            calculateExpenses();
        }
        document.getElementById('billForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const truckId = form.querySelector('input[name="truck_id"]').value;
            const trucknumber = form.querySelector('input[name="truck_number"]').value;
            const vendorId = form.querySelector('input[name="vendor_id"]').value;
            const subtotal = document.getElementById('subtotal').textContent;
            const totalExpense = document.getElementById('totalExpense').textContent;
            const netPay = document.getElementById('netPay').textContent;
            const adjustmentInput = document.getElementById('adjustment');
            const adjustment = adjustmentInput.value === '' ? null : parseFloat(adjustmentInput.value);

            // Collect bill details
            const bills = [];
            document.querySelectorAll('#billTable tbody tr').forEach(row => {
                bills.push({
                    lot_id: row.querySelector('input[name^="lots"]').value,
                    sale_units: row.querySelector('.sale-units').value,
                    rate: row.querySelector('.rate').value,
                    amount: row.querySelector('.amount').value,
                    unit_in: row.querySelector('input[name^="unit_in"]').value,
                    weight: row.querySelector('.weight').value // 👈 Add this line
                });
            });

            // Collect expenses
            const expenses = [];
            document.querySelectorAll('#expenseTable tbody tr').forEach(row => {
                expenses.push({
                    category: row.querySelector('.expense-type').value,
                    value: row.querySelector('.expense-input').value,
                    final_amount: row.querySelector('.expense-final').value
                });
            });

            const payload = {
                truck_id: truckId,
                trucknumber: trucknumber,
                vendorId: vendorId,
                subtotal: subtotal,
                total_expense: totalExpense,
                net_pay: netPay,
                adjustment: adjustment,
                bill_details: bills,
                expenses: expenses
            };

            fetch("{{ route('vendor.bill.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);

                    if (data.success) {
                        Swal.fire('Success', data.message, 'success').then(() => {
                            setTimeout(() => {
                                window.location.href = "{{ route('trucks-sold') }}"; // 👈 Replace with your actual route
                            }, 3000); // 5 seconds
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'Something went wrong with the request.', 'error');
                });


        });
    </script>
</body>