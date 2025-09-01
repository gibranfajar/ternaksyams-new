@extends('layouts.app')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3>Flash Sales</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <h5 class="card-title mb-3">Edit Flashsale</h5>

                    {{-- form --}}
                    <form action="{{ route('flash-sales.update', $flashSale->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="{{ $flashSale->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="description" rows="5">{{ $flashSale->description }}</textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date"
                                    value="{{ $flashSale->start_date }}" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date"
                                    value="{{ $flashSale->end_date }}" name="end_date" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Selected Products</h6>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#addFlashSaleModal">Add Product</button>
                        </div>

                        {{-- Selected Products List --}}
                        <div class="my-3">
                            <ul class="list-group" id="selected-products-list">
                                @foreach ($variants as $item)
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $item->images->first()->image_path ?? '') }}"
                                                width="40" height="40" class="rounded me-2">
                                            <div>
                                                <div class="fw-bold">{{ $item->name }}</div>
                                                <small>Discount: {{ $item->sizes->first()->discount_flashsale }}% | Qty:
                                                    {{ $item->sizes->first()->stock_flashsale }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <!-- delete -->
                                            <button type="button" class="btn btn-sm btn-danger ms-2 remove-product">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <!-- hidden input biar ikut tersubmit -->
                                        <input type="hidden" name="selected_products[{{ $item->id }}][id]"
                                            value="{{ $item->id }}">
                                        <input type="hidden" name="selected_products[{{ $item->id }}][discount]"
                                            value="{{ $item->sizes->first()->discount_flashsale }}">
                                        <input type="hidden" name="selected_products[{{ $item->id }}][qty]"
                                            value="{{ $item->sizes->first()->stock_flashsale }}">
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- button --}}
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Flash Sale</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- modal add products --}}
            @include('flashsales.modaledit')

            <!-- [ Main Content ] end -->
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Saat klik tombol Add Selected Products
            $("#btn-add-products").on("click", function() {

                // Kosongkan list lama
                $("#selected-products-list").empty();

                // Loop semua checkbox yang dicentang
                $("#addFlashSaleModal input.form-check-input:checked").each(function() {
                    let $card = $(this).closest(".card");

                    let productId = $(this).val();
                    let productName = $card.find("label.fw-bold").text().trim();
                    let imgSrc = $card.find("img").attr("src");
                    let discount = $card.find("input[name*='[discount]']").val();
                    let qty = $card.find("input[name*='[qty]']").val();

                    // Buat element list baru
                    let li = `
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="${imgSrc}" width="40" height="40" class="rounded me-2">
                            <div>
                                <div class="fw-bold">${productName}</div>
                                <small>Discount: ${discount}% | Qty: ${qty}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <!-- delete -->
                            <button type="button" class="btn btn-sm btn-danger ms-2 remove-product">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <!-- hidden input biar ikut tersubmit -->
                        <input type="hidden" name="selected_products[${productId}][id]" value="${productId}">
                        <input type="hidden" name="selected_products[${productId}][discount]" value="${discount}">
                        <input type="hidden" name="selected_products[${productId}][qty]" value="${qty}">
                    </li>
                    `;

                    $("#selected-products-list").append(li);
                });

                // Tutup modal
                $("#addFlashSaleModal").modal("hide");
            });

            // Event delegation untuk tombol Remove
            $(document).on("click", ".remove-product", function() {
                $(this).closest("li").remove();
            });
        });
    </script>
@endsection
