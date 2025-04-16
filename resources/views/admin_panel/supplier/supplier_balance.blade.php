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
                    <h4 class="page-title mb-0">Vendor Balance </h4>
                </div>

                <div class="row g-4">
                    <!-- Left Side: Customers -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-gradient-primary text-white fw-semibold">
                                <i class="fas fa-users me-2"></i> Vendor List
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
                                            @forelse($SupplierLedger as $ledger)
                                            <tr class="clickable-row" data-id="{{ $ledger->supplier->id }}">
                                                <td><a href="#" class="customer-link">{{ $ledger->supplier->name }}</a></td>
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
                                <i class="fas fa-file-alt me-2"></i> Select a Vendor to view details
                            </div>
                            <div class="card-body" id="customerDetailContent">
                                <div class="alert alert-info">
                                    Click a Vendor from the left panel to view their full ledger.
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
        $(document).ready(function() {
            $('.clickable-row').on('click', function() {
                let vendorId = $(this).data('id');
                let vendorName = $(this).find('.customer-link').text();

                $('#customerDetailTitle').html(`<i class="fas fa-file-alt me-2"></i> ${vendorName} - Ledger Details`);
                $('#customerDetailContent').html(`<div class="text-center">Loading...</div>`);

                $.ajax({
                    url: '{{ route("Supplier-balance-ledger", ":id") }}'.replace(':id', vendorId),
                    method: 'GET',
                    success: function(data) {
                        if (data.length === 0) {
                            $('#customerDetailContent').html('<div class="alert alert-warning">No records found for this vendor.</div>');
                            return;
                        }

                        let html = `<table class="table table-bordered table-striped">
                    <thead><tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr></thead><tbody>`;

                        data.forEach(entry => {
                            html += `<tr>
                        <td>${entry.date}</td>
                        <td>${entry.type}</td>
                        <td>${parseFloat(entry.amount).toLocaleString()}</td>
                        <td>${entry.remarks}</td>
                    </tr>`;
                        });

                        html += `</tbody></table>`;
                        $('#customerDetailContent').html(html);
                    },
                    error: function() {
                        $('#customerDetailContent').html('<div class="alert alert-danger">Error loading data.</div>');
                    }
                });
            });
        });
    </script>