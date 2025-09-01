<!-- Modal -->
<div class="modal fade" id="showVariantModal{{ $item->id }}" tabindex="-1" aria-labelledby="showVariantModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="showVariantModalLabel">{{ $item->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <!-- Tabs -->
                <div class="nav-tabs-container mb-3">
                    <ul class="nav nav-tabs flex-nowrap overflow-auto" id="variantTabs{{ $item->id }}"
                        role="tablist" style="white-space: nowrap; -webkit-overflow-scrolling: touch;">
                        @foreach ($item->variants as $key => $variant)
                            @php
                                $product = $variant->product; // karena belongsTo, hasilnya 1 object, bukan collection
                            @endphp
                            @if ($product)
                                <li class="nav-item d-inline-block" role="presentation">
                                    <button class="nav-link {{ $key === 0 ? 'active' : '' }}"
                                        id="variant-tab-{{ $variant->id }}" data-bs-toggle="tab"
                                        data-bs-target="#variant-{{ $variant->id }}" type="button" role="tab">
                                        {{ $product->name }}
                                    </button>
                                </li>
                            @endif
                        @endforeach

                    </ul>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="variantTabsContent{{ $item->id }}">
                    @foreach ($item->variants as $key => $variant)
                        <div class="tab-pane fade {{ $key === 0 ? 'show active' : '' }}"
                            id="variant-{{ $variant->id }}" role="tabpanel">

                            <div class="row mb-3">
                                <!-- Flavour -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 bg-light">
                                        <div class="card-body d-flex align-items-center">

                                            <!-- Gambar variant -->
                                            <div class="me-3 flex-shrink-0">
                                                <img src="{{ asset('storage/' . optional($variant->images->first())->image_path ?? '') }}"
                                                    alt="{{ $variant->flavour->name ?? 'No Image' }}" class="img-fluid"
                                                    style="width: 52px; height: 52px; object-fit: cover; border-radius: 5px;">
                                            </div>

                                            <!-- Info flavour -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-tag-fill text-primary me-2"></i>
                                                    <h6 class="card-subtitle fw-bold text-primary mb-0">Flavour</h6>
                                                </div>
                                                <span class="fw-semibold">{{ $variant->flavour->name ?? '-' }}</span>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 bg-light">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-list-ul text-success me-2 fs-5"></i>
                                                <h6 class="card-subtitle fw-bold text-success">Category</h6>
                                            </div>
                                            <span class="fw-semibold">
                                                {{ $variant->category->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 bg-light">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-star-fill text-purple me-2 fs-5"></i>
                                                <h6 class="card-subtitle fw-bold text-purple">Status</h6>
                                            </div>
                                            <span class="badge {{ $statusColors[$item->status] ?? 'bg-secondary' }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Size & Pricing Details -->
                            <h6 class="mb-2">Size & Pricing Details</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Size</th>
                                            <th>Original Price</th>
                                            <th>Discount</th>
                                            <th>Flashsale Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($variant->sizes as $size)
                                            <tr>
                                                <td>{{ $size->size->label ?? '-' }}{{ $size->size->unit ?? '' }}</td>
                                                <td>Rp {{ number_format($size->price) }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-danger">{{ $size->discount_flashsale ?? 0 }}%</span>
                                                </td>
                                                <td class="text-success">
                                                    Rp
                                                    {{ number_format($size->price_after_discount_flashsale ?? $size->price) }}
                                                </td>
                                                <td>{{ $size->stock_flashsale ?? $size->stock }}</td>
                                                <td>
                                                    @if (($size->stock_flashsale ?? $size->stock) > 10)
                                                        <span class="badge bg-success">In Stock</span>
                                                    @elseif(($size->stock_flashsale ?? $size->stock) > 0)
                                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
