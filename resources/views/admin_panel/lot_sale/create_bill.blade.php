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
                                            <td>{{ number_format($lot->average_sale, 2) }}</td>
                                            <td>{{ number_format($lot->total_sale, 2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="addBillRow({{ $lot->id }}, '{{ $lot->category }}', '{{ $lot->variety }}', '{{ $lot->unit }}', {{ $lot->total_units }}, '{{ $lot->unit_in }}')">
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

                            <div class="d-flex justify-content-between mt-3">
                                <h5 class="fw-bold">Total Mazdori: <span id="totalMazdori">0</span></h5>
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
    <script>
        function addBillRow(id, category, variety, unit, total_units, unit_in) {
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
            <td><input type="number" name="sale_units[${id}]" class="form-control sale-units" data-id="${id}" min="1" max="${total_units}" required></td>
            <td><input type="number" name="rate[${id}]" class="form-control rate" data-id="${id}" min="1" required></td>
            <td><input type="number" name="amount[${id}]" class="form-control amount" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeBothRows(this, ${id})">Remove</button></td>
        `;
            billTable.appendChild(billRow);

            billRow.querySelector('.sale-units').addEventListener('input', updateRowAmount);
            billRow.querySelector('.rate').addEventListener('input', updateRowAmount);

            addMazdoriRow(id, total_units, unit_in);
        }

        function updateRowAmount(e) {
            const id = e.target.dataset.id;
            const units = parseFloat(document.querySelector(`input[name="sale_units[${id}]"]`).value) || 0;
            const rate = parseFloat(document.querySelector(`input[name="rate[${id}]"]`).value) || 0;
            const amount = units * rate;

            document.querySelector(`input[name="amount[${id}]"]`).value = Math.round(amount);
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
            let totalExpense = 0;

            document.querySelectorAll('#expenseTable tbody tr').forEach(row => {
                const type = row.querySelector('.expense-type').value;
                const value = parseFloat(row.querySelector('.expense-input').value) || 0;
                let final = 0;

                if (type === 'Commission') {
                    final = subtotal * (value / 100);
                } else {
                    final = value;
                }

                row.querySelector('.expense-final').value = Math.round(final);
                totalExpense += final;
            });

            document.getElementById('totalExpense').textContent = Math.round(totalExpense);
            document.getElementById('netPay').textContent = Math.round(subtotal - totalExpense);
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
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>
                <select class="form-control expense-type" onchange="calculateExpenses()">
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

        document.getElementById('billForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const truckId = form.querySelector('input[name="truck_id"]').value;
            const trucknumber = form.querySelector('input[name="truck_number"]').value;
            const subtotal = document.getElementById('subtotal').textContent;
            const totalExpense = document.getElementById('totalExpense').textContent;
            const netPay = document.getElementById('netPay').textContent;

            // Collect bill details
            const bills = [];
            document.querySelectorAll('#billTable tbody tr').forEach(row => {
                const lotId = row.querySelector('input[name^="lots"]').value;
                bills.push({
                    lot_id: lotId,
                    sale_units: row.querySelector(`input[name="sale_units[${lotId}]"]`).value,
                    rate: row.querySelector(`input[name="rate[${lotId}]"]`).value,
                    amount: row.querySelector(`input[name="amount[${lotId}]"]`).value,
                    unit_in: row.querySelector(`input[name="unit_in[${lotId}]"]`).value
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
                subtotal: subtotal,
                total_expense: totalExpense,
                net_pay: netPay,
                bill_details: bills,
                expenses: expenses
            };

            // 🔥 Check what’s being sent (can remove console later)
            console.log(payload);

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
                    Swal.fire('Success', 'Bill has been saved!', 'success');
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'Something went wrong!', 'error');
                });
        });
    </script>
</body>