<!-- Add Flash Sale Modal -->
<div class="modal fade" id="addFlashSaleModal" tabindex="-1" aria-labelledby="addFlashSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFlashSaleModalLabel">Select Products for Flash Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Search bar (fixed, tidak scroll) -->
                <div class="mb-3">
                    <input type="text" id="search-product" class="form-control" placeholder="Search products...">
                </div>

                <!-- Wrapper produk scrollable -->
                <div id="product-list">
                    @foreach ($products as $item)
                        @foreach ($item->variants as $variant)
                            <div class="card mb-2 p-2">
                                <div class="d-flex align-items-center">
                                    <!-- Checkbox -->
                                    <div class="form-check me-2">
                                        <input class="form-check-input" type="checkbox" value="{{ $variant->id }}"
                                            id="product{{ $variant->id }}" name="products[{{ $variant->id }}][id]">
                                    </div>

                                    <!-- Image -->
                                    <img src="{{ asset('storage/' . $variant->images[0]->image_path) }}"
                                        alt="Product Image" width="60" height="60" class="rounded me-3">

                                    <!-- Product Info -->
                                    <div class="flex-grow-1">
                                        <label class="fw-bold mb-0" for="product{{ $variant->id }}">
                                            {{ $variant->name }}
                                        </label>
                                    </div>

                                    <!-- Discount & Qty -->
                                    <div class="d-flex gap-2">
                                        <div>
                                            <label class="form-label small mb-0">Discount %</label>
                                            <input type="number" class="form-control form-control-sm"
                                                name="products[{{ $variant->id }}][discount]" placeholder="0"
                                                min="0">
                                        </div>
                                        <div>
                                            <label class="form-label small mb-0">Qty</label>
                                            <input type="number" class="form-control form-control-sm"
                                                name="products[{{ $variant->id }}][qty]" placeholder="0"
                                                min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-add-products">Add Selected Products</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Scroll hanya daftar produk */
    #product-list {
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<script>
    $(document).ready(function() {
        // Search filter
        $("#search-product").on("keyup", function() {
            let value = $(this).val().toLowerCase();

            $("#product-list .card").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
