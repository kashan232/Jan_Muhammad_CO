@include('admin_panel.include.header_include')

<body>
    <div class="page-wrapper default-version">
        @include('admin_panel.include.sidebar_include')
        @include('admin_panel.include.navbar_include')

        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                <div class="container">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary">
                            <h4 class="text-white">Sale Record for Truck #{{ $truck->truck_number }}</h4>
                        </div>

                        <div class="card-body">
                            @foreach($lots as $lot)
                            <div class="card my-3 border">
                                <div class="card-header bg-light">
                                    <h5>Lot: {{ $lot->category }} - {{ $lot->variety }}</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Total Quantity:</strong> {{ $lot->total_units }}</p>
                                    <p><strong>Total Sold:</strong> {{ $lot->sold_quantity }}</p>
                                    <p><strong>Available:</strong> {{ $lot->available_quantity }}</p>
                                    @php
                                    $totalAmount = $lot->sales->sum(function($sale) {
                                    return ($sale->weight ? $sale->weight : $sale->quantity) * $sale->price;
                                    });

                                    $averageSale = $lot->sold_quantity > 0 ? $totalAmount / $lot->sold_quantity : 0;
                                    @endphp

                                    <p><strong>Total Sale Amount:</strong> Rs. {{ number_format($totalAmount, 2) }}</p>
                                    <p><strong>Average Sale:</strong> Rs. {{ number_format($averageSale, 2) }}</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th>Sold Units</th>
                                                    <th>Weight</th> <!-- Add this -->
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                    <th>Sale Date</th>
                                                    <th>Type</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lot->sales as $sale)
                                                <tr>
                                                    <td>{{ $sale->customer_name }}</td>
                                                    <td>{{ $sale->quantity }}</td>
                                                    <td>{{ $sale->weight ?? '-' }}</td> <!-- Add this -->
                                                    <td>{{ number_format($sale->price, 2) }}</td>
                                                    <td>
                                                        {{ number_format(($sale->weight ?? $sale->quantity) * $sale->price, 2) }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</td>
                                                    <td>
                                                        <span class="badge {{ $sale->customer_type == 'Credit' ? 'bg-danger' : 'bg-success' }}">
                                                            {{ $sale->customer_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- Edit Button -->
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $sale->id }}">
                                                            Edit
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-danger delete-sale"
                                                            data-lot-id="{{ $sale->lot_id }}"
                                                            data-sale-id="{{ $sale->id }}"
                                                            data-customer-id="{{ $sale->customer_id }}">
                                                            Delete
                                                        </button>

                                                    </td>
                                                </tr>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editModal{{ $sale->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $sale->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <form method="POST" action="{{ route('update.lot.sale') }}">
                                                            @csrf
                                                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editModalLabel{{ $sale->id }}">
                                                                        Edit Sale - {{ $sale->customer_name }}
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Current sold units (read‑only) -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Sold Units</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ $sale->quantity }}" readonly>
                                                                    </div>
                                                                    <!-- Conditional Weight Field -->
                                                                    @if($sale->weight !== null)
                                                                    <div class="mb-3">
                                                                        <label for="weight" class="form-label">Weight (KG)</label>
                                                                        <input type="number" step="0.01" name="weight" class="form-control" value="{{ $sale->weight }}">
                                                                        <div class="form-text">Optional: Only applicable if weight-based sale.</div>
                                                                    </div>
                                                                    @endif
                                                                    <!-- NEW: Add Units -->
                                                                    <div class="mb-3">
                                                                        <label for="add_units" class="form-label">Add Units</label>
                                                                        <input type="number" name="add_units" id="add_units"
                                                                            class="form-control" value="0" min="0" required>
                                                                        <div class="form-text">
                                                                            Enter how many more units to sell (or 0 to leave unchanged).
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="price" class="form-label">Price</label>
                                                                        <input type="number" name="price" step="0.01"
                                                                            class="form-control" value="{{ $sale->price }}" required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="sale_date" class="form-label">Sale Date</label>
                                                                        <input type="date" name="sale_date" class="form-control"
                                                                            value="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}"
                                                                            required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" class="btn btn-primary">Update Sale</button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS for modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @include('admin_panel.include.footer_include')
</body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.delete-sale').on('click', function() {
        const lotId = $(this).data('lot-id');
        const saleId = $(this).data('sale-id');
        const customerid = $(this).data('customer-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this sale!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request here
                $.ajax({
                    url: '{{ route("delete.sale") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        lot_id: lotId,
                        sale_id: saleId,
                        customerid: customerid
                    },
                    success: function(res) {
                        Swal.fire(
                            'Deleted!',
                            res.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let error = xhr.responseJSON?.message || 'Something went wrong!';
                        Swal.fire('Error', error, 'error');
                    }
                });
            }
        });
    });
</script>