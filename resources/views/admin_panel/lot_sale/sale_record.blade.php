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

                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <th>Sold Units</th>
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
                                                            <td>{{ number_format($sale->price, 2) }}</td>
                                                            <td>{{ number_format($sale->quantity * $sale->price, 2) }}</td>
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
                                                                            <h5 class="modal-title" id="editModalLabel{{ $sale->id }}">Edit Sale - {{ $sale->customer_name }}</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="mb-3">
                                                                                <label for="quantity" class="form-label">Sold Units</label>
                                                                                <input type="number" name="quantity" class="form-control" value="{{ $sale->quantity }}" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="price" class="form-label">Price</label>
                                                                                <input type="number" name="price" step="0.01" class="form-control" value="{{ $sale->price }}" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="sale_date" class="form-label">Sale Date</label>
                                                                                <input type="date" name="sale_date" class="form-control" value="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}" required>
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
