@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')
        <!-- Modal for Lot Sale Details -->
        <div class="modal fade" id="lotSaleModal" tabindex="-1" aria-labelledby="lotSaleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lotSaleModalLabel">Lot Sale Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Detail</th>
                                    <th>Information</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Lot ID</strong></td>
                                    <td id="lotSaleLotId"></td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity</strong></td>
                                    <td id="lotSaleQuantity"></td>
                                </tr>
                                <tr>
                                    <td><strong>Price</strong></td>
                                    <td id="lotSalePrice"></td>
                                </tr>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td id="lotSaleTotal"></td>
                                </tr>
                                <tr>
                                    <td><strong>Sale Date</strong></td>
                                    <td id="lotSaleDate"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="page-title mb-0">Customer Balance </h4>
                </div>

                <div class="row g-4">
                    <!-- Left Side: Customers -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-gradient-primary text-white fw-semibold">
                                <i class="fas fa-users me-2"></i> Customers List
                            </div>
                            <div class="card-body p-0 customer-list-scroll">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="bg-light text-center text-dark fw-semibold">
                                            <tr>
                                                <th>Name</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customerList">
                                            @forelse($CustomerLedgers as $ledger)
                                            <tr class="clickable-row" data-id="{{ $ledger->Customer->id }}">
                                                <td><a href="#" class="customer-link">{{ $ledger->Customer->customer_name }}</a></td>
                                                <td class="text-end text-success">{{ number_format($ledger->closing_balance, 0) }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="2" class="text-center">No records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Customer Details -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-gradient-secondary text-white fw-semibold" id="customerDetailTitle">
                                <i class="fas fa-file-alt me-2"></i> Select a customer to view details
                            </div>
                            <div class="card-body" id="customerDetailContent">
                                <div class="alert alert-info">
                                    Click a customer from the left panel to view their full ledger.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- inner end -->
        </div> <!-- wrapper end -->
    </div>

    @include('admin_panel.include.footer_include')

    <style>
        .clickable-row:hover {
            background-color: #f7faff;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .customerDetailContent,
        .clickable-row {
            cursor: pointer;
        }

        thead.bg-white th {
            background-color: #fff !important;
            color: #000;
        }

        .clickable-sale {
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .clickable-sale:hover {
            background-color: #f9f9f9;
        }

        .customer-link {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }

        .customer-link:hover {
            text-decoration: underline;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #1e88e5, #42a5f5);
        }

        .bg-gradient-secondary {
            background: linear-gradient(45deg, #6c757d, #a0a5ab);
        }

        /* ⭐ NEW SCROLL STYLE FOR CUSTOMER LIST */
        .customer-list-scroll {
            max-height: 500px;
            /* Adjust height as needed */
            overflow-y: auto;
        }
    </style>


    <script>
        $('.clickable-row').on('click', function() {
            let customerId = $(this).data('id');
            let customerName = $(this).find('.customer-link').text();

            $('#customerDetailTitle').html(`<i class="fas fa-user me-2"></i> ${customerName} - Ledger`);
            $('#customerDetailContent').html(`<div class="text-center">Loading...</div>`);
            $('#customerLedgerModal').modal('show');

            $.ajax({
                url: '{{ route("customer.ledger", ":id") }}'.replace(':id', customerId),
                method: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        $('#customerDetailContent').html(`<div class="alert alert-warning">No records found for this customer.</div>`);
                        return;
                    }

                    let ledgerRows = response.map(entry => {
                        let isSale = entry.type.toLowerCase() === 'sale';
                        let rowClass = isSale ? 'sale-row clickable-sale' : '';
                        let dataAttr = isSale ? `data-lot-id="${entry.id}"` : '';

                        return `
        <tr class="${rowClass}" ${dataAttr}>
            <td>${entry.date}</td>
            <td>${entry.type}</td>
            <td>Rs. ${entry.amount ? Number(entry.amount).toLocaleString() : '-'}</td>
            <td>${entry.remarks ?? '-'}</td>
        </tr>
    `;
                    }).join('');

                    $('#customerDetailContent').html(`
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="bg-white text-center fw-semibold text-dark">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            ${ledgerRows}
                        </tbody>
                    </table>
                </div>
            `);

                    // Sale row click event to show modal with lot details
                    $('.clickable-sale').on('click', function() {
                        let lotId = $(this).data('lot-id');
                        $.ajax({
                            url: '{{ route("lot.sale.details", ":id") }}'.replace(':id', lotId),
                            method: 'GET',
                            success: function(lotDetails) {
                                if (lotDetails) {
                                    // Fill the modal with the lot sale details
                                    $('#lotSaleLotId').text(lotDetails.lot_id);
                                    $('#lotSaleQuantity').text(lotDetails.quantity);
                                    $('#lotSalePrice').text('Rs. ' + lotDetails.price);
                                    $('#lotSaleTotal').text('Rs. ' + lotDetails.total);
                                    $('#lotSaleDate').text(lotDetails.sale_date);

                                    // Show the modal
                                    $('#lotSaleModal').modal('show');
                                }
                            },
                            error: function() {
                                alert("Failed to fetch lot sale details.");
                            }
                        });
                    });

                },
                error: function() {
                    $('#customerDetailContent').html(`<div class="alert alert-danger">Failed to load ledger data.</div>`);
                }
            });
        });
    </script>