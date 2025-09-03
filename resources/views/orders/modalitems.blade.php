<!-- Modal -->
<div class="modal fade" id="showItemsModal{{ $item->id }}" tabindex="-1" aria-labelledby="showItemsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="showItemsModalLabel">Order Details - #{{ $item->invoice }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Order Items -->
                        <div class="mb-3">
                            <h6 class="fw-bold">Order Items</h6>
                            <div class="list-group">
                                @foreach ($item->items as $orderItem)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $orderItem->variantSize->variant->images[0]->image_path) }}"
                                                alt="{{ $orderItem->name }}" class="rounded me-3" width="60">
                                            <div>
                                                <div class="fw-semibold">{{ $orderItem->name }}</div>
                                                <small>Size: {{ $orderItem->size }} | Variant:
                                                    {{ $orderItem->variant }}</small><br>
                                                <small>Qty: {{ $orderItem->qty }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if ($orderItem->discount > 0)
                                                <div class="text-decoration-line-through text-danger">
                                                    Rp {{ number_format($orderItem->original_price) }}
                                                </div>
                                                <div class="fw-semibold text-success">
                                                    Rp {{ number_format($orderItem->price) }}
                                                </div>
                                            @else
                                                <div class="fw-semibold">
                                                    Rp {{ number_format($orderItem->original_price) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row g-2">
                            <!-- Order Summary -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold">Order Summary</h6>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal</span>
                                        <span>Rp {{ number_format($item->items->sum('price')) }}</span>
                                    </div>
                                    @if ($item->voucher)
                                        <div class="d-flex justify-content-between text-success">
                                            <span>Voucher Discount</span>
                                            <span>-Rp {{ number_format($item->voucher_discount ?? 0) }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between">
                                        <span>Shipping</span>
                                        <span>Rp {{ number_format($item->shipping->shippingOption->cost) }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total</span>
                                        <span>Rp
                                            {{ number_format($item->items->sum('price') + $item->shipping->shippingOption->cost) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Information -->

                            <div class="card" style="background-color: #e8f8e8">
                                <div class="card-body">
                                    <h6 class="fw-bold">Shipping Information</h6>
                                    <hr>
                                    <div>{{ $item->shipping->shippingInfo->name }}</div>
                                    <div>{{ $item->shipping->shippingInfo->address }}</div>
                                    <div>{{ $item->shipping->shippingInfo->province }}
                                        {{ $item->shipping->shippingInfo->city }},
                                        {{ $item->shipping->shippingInfo->postal_code }}</div>
                                    <div>{{ $item->shipping->shippingInfo->country }}</div>
                                    <div>Phone: {{ $item->shipping->shippingInfo->phone }}</div>
                                </div>
                            </div>

                            <!-- Payment Information -->

                            <div class="card" style="background-color: #f2e9fb">
                                <div class="card-body">
                                    <h6 class="fw-bold">Payment Information</h6>
                                    <hr>
                                    <div>Method: {{ $item->payment->method }}</div>
                                    <div>Status:
                                        <span
                                            class="badge 
                                                        @if ($item->payment->status == 'pending') bg-warning 
                                                        @elseif($item->payment->status == 'settlement') bg-success 
                                                        @elseif($item->payment->status == 'failed') bg-danger 
                                                        @else bg-secondary @endif">
                                            {{ ucfirst($item->payment->status) }}
                                        </span>
                                    </div>
                                    @if ($item->payment->status == 'settlement')
                                        <div>Paid At:
                                            {{ Carbon\Carbon::parse($item->payment->paid_at)->format('d M Y | H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- Voucher Applied -->
                        @if ($item->voucher)
                            <div class="card" style="background-color: #fff9e6">
                                <div class="card-body">
                                    <h6 class="fw-bold">Voucher Applied</h6>
                                    <hr>
                                    <div>Code: {{ $item->voucher->code }}</div>
                                    <div>Discount: Rp {{ number_format($item->voucher->amount) }}</div>
                                    <div>Type: {{ ucfirst($item->voucher->type) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i
                            class="bi bi-file-earmark-pdf"></i> Download</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
