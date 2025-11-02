@extends('layouts.app')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h3>Create Brand (Full Schema)</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- ================= BRAND ================= --}}
                                <h4>Brand Info</h4>
                                <div class="mb-3">
                                    <input type="text" name="brand_name" placeholder="Nama Brand"
                                        class="form-control mb-2" required>
                                    <textarea name="brand_description" placeholder="Deskripsi" class="form-control mb-2"></textarea>
                                    <label>Logo / Gambar Brand:</label>
                                    <input type="file" name="brand_image" accept="image/*" class="form-control mb-2"
                                        onchange="previewImage(event, 'brandPreview')">
                                    <img id="brandPreview" class="img-thumbnail mt-2"
                                        style="max-width:200px; display:none;">
                                </div>

                                <hr>

                                {{-- ================= SIZES ================= --}}
                                <h4>Sizes</h4>
                                <div id="sizes-wrapper"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addSize()">+ Add
                                    Size</button>

                                <hr>

                                {{-- ================= VARIANTS ================= --}}
                                <h4>Variants</h4>
                                <div id="variants-wrapper"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addVariant()">+ Add
                                    Variant</button>

                                <hr>

                                {{-- ================= HERO SECTION ================= --}}
                                <h4>Hero Section</h4>
                                <input type="text" name="hero[title]" placeholder="Title" class="form-control mb-2">
                                <textarea name="hero[subtitle]" placeholder="Subtitle" class="form-control mb-2"></textarea>

                                <h5>CTA Buttons</h5>
                                <input type="text" name="hero[cta_shop]" placeholder="CTA Shop Text"
                                    class="form-control mb-2">
                                <input type="text" name="hero[cta_shop_url]" placeholder="CTA Shop URL"
                                    class="form-control mb-2">
                                <input type="text" name="hero[cta_subscribe]" placeholder="CTA Subscribe Text"
                                    class="form-control mb-2">
                                <input type="text" name="hero[cta_subscribe_url]" placeholder="CTA Subscribe URL"
                                    class="form-control mb-2">

                                <label>Hero Image:</label>
                                <input type="file" name="hero[image]" accept="image/*" class="form-control mb-2"
                                    onchange="previewImage(event, 'heroPreview')">
                                <img id="heroPreview" class="img-thumbnail mt-2" style="max-width:200px; display:none;">

                                <h5>Testimonial</h5>
                                <input type="text" name="testimonialquote" placeholder="Testimonial Quote"
                                    class="form-control mb-2">

                                <h5>Reviews</h5>
                                <input type="text" name="review[count]" placeholder="Review Count"
                                    class="form-control mb-2">
                                <input type="text" name="review[text]" placeholder="Review Text"
                                    class="form-control mb-2">
                                <input type="text" name="review[link_text]" placeholder="Review Link Text"
                                    class="form-control mb-2">
                                <input type="text" name="review[link_url]" placeholder="Review Link URL"
                                    class="form-control mb-2">
                                <input type="text" name="review[bg_color]" placeholder="Review Background Color"
                                    class="form-control mb-2">
                                <input type="text" name="review[text_color]" placeholder="Review Text Color"
                                    class="form-control mb-2">

                                <hr>

                                {{-- ================= FEATURES ================= --}}
                                <h4>Features</h4>
                                <input type="text" name="colormarquefeature[bg_color]"
                                    placeholder="Color Marque Feature Background Color" class="form-control mb-2">
                                <input type="text" name="colormarquefeature[text_color]"
                                    placeholder="Color Marque Feature Text Color" class="form-control mb-2">
                                <div id="features-wrapper"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addFeature()">+ Add
                                    Feature</button>

                                <hr>

                                <h5>Product Section</h5>
                                <input type="text" name="productsection[title]" placeholder="Title"
                                    class="form-control mb-2">
                                <input type="text" name="productsection[title_color]" placeholder="Title Color"
                                    class="form-control mb-2">
                                <input type="text" name="productsidebar[headline]" placeholder="Headline"
                                    class="form-control mb-2">
                                <textarea name="productsidebar[description]" placeholder="Deskripsi" class="form-control mb-2"></textarea>
                                <input type="text" name="productsidebar[cta_text]" placeholder="Cta Text"
                                    class="form-control mb-2">
                                <input type="text" name="productsidebar[cta_url]" placeholder="Cta URL"
                                    class="form-control mb-2">

                                {{-- ================= ABOUT SECTION ================= --}}
                                <h4>About Section</h4>
                                <input type="text" name="about[tagline]" placeholder="Tagline"
                                    class="form-control mb-2">
                                <input type="text" name="about[title]" placeholder="Judul" class="form-control mb-2">
                                <textarea name="about[description]" placeholder="Deskripsi" class="form-control mb-2"></textarea>

                                <label>About Image:</label>
                                <input type="file" name="about[image]" accept="image/*" class="form-control mb-2"
                                    onchange="previewImage(event, 'aboutPreview')">
                                <img id="aboutPreview" class="img-thumbnail mt-2" style="max-width:200px; display:none;">

                                <hr>

                                {{-- ================= HOW IT WORKS ================= --}}
                                <h4>How It Works</h4>
                                <input type="text" name="how[tagline]" placeholder="Tagline"
                                    class="form-control mb-2">
                                <label>Image:</label>
                                <input type="file" name="how[image]" accept="image/*" class="form-control mb-2"
                                    onchange="previewImage(event, 'howPreview')">
                                <img id="howPreview" class="img-thumbnail mt-2" style="max-width:200px; display:none;">

                                <h5>Headlines</h5>
                                <div id="headlines-wrapper"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addHeadline()">+ Add
                                    Headline</button>

                                <h5 class="mt-3">Steps</h5>
                                <div id="steps-wrapper"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" onclick="addStep()">+ Add
                                    Step</button>

                                <div class="mt-3">
                                    <input type="text" name="how[cta_text]" placeholder="CTA Text"
                                        class="form-control mb-2">
                                    <input type="text" name="how[cta_url]" placeholder="CTA URL"
                                        class="form-control mb-2">
                                </div>

                                <hr>
                                <button type="submit" class="btn btn-primary mt-3">Simpan Brand</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============== JAVASCRIPT ============== --}}
    <script>
        let variantIndex = 0;

        function addFeature() {
            const wrapper = document.getElementById('features-wrapper');
            const div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
                <input type="text" name="features[]" placeholder="Feature" class="form-control">
                <button type="button" class="btn btn-danger" onclick="this.parentNode.remove()">X</button>
            `;
            wrapper.appendChild(div);
        }

        function addSize() {
            const wrapper = document.getElementById('sizes-wrapper');
            const div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
                <input type="text" name="brand[sizes]" placeholder="Contoh: 250 Gram" class="form-control">
                <button type="button" class="btn btn-danger" onclick="this.parentNode.remove()">X</button>
            `;
            wrapper.appendChild(div);
        }

        function addVariant() {
            const wrapper = document.getElementById('variants-wrapper');
            const div = document.createElement('div');
            div.classList.add('border', 'p-3', 'mb-3');
            div.innerHTML = `
                <input type="text" name="brand[variants][${variantIndex}][name]" placeholder="Nama Varian" class="form-control mb-2">
                <input type="file" name="brand[variants][${variantIndex}][image]" accept="image/*" class="form-control mb-2" onchange="previewImage(event, 'variantPreview${variantIndex}')">
                <img id="variantPreview${variantIndex}" class="img-thumbnail mt-2" style="max-width:200px; display:none;">
                <button type="button" class="btn btn-danger mt-2" onclick="this.parentNode.remove()">Hapus Varian</button>
            `;
            variantIndex++;
            wrapper.appendChild(div);
        }

        function addHeadline() {
            const wrapper = document.getElementById('headlines-wrapper');
            const div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
                <input type="text" name="how[headlines][]" placeholder="Headline" class="form-control">
                <button type="button" class="btn btn-danger" onclick="this.parentNode.remove()">X</button>
            `;
            wrapper.appendChild(div);
        }

        function addStep() {
            const wrapper = document.getElementById('steps-wrapper');
            const div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
                <input type="text" name="how[steps][]" placeholder="Step" class="form-control">
                <button type="button" class="btn btn-danger" onclick="this.parentNode.remove()">X</button>
            `;
            wrapper.appendChild(div);
        }

        function previewImage(event, previewId) {
            const img = document.getElementById(previewId);
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    img.src = reader.result;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Tambahkan 1 field awal per grup biar ga kosong
        window.onload = function() {
            addFeature();
            addSize();
            addVariant();
            addHeadline();
            addStep();
        };
    </script>
@endsection
