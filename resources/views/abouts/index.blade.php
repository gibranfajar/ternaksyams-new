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
                                <h3>Abouts</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('abouts.update', $about->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Hero Section -->
                                <h5>Hero Section</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Hero Title</label>
                                        <input type="text" name="hero_title" class="form-control"
                                            value="{{ $about->hero_title }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hero Subtitle</label>
                                        <input type="text" name="hero_subtitle" class="form-control"
                                            value="{{ $about->hero_subtitle }}">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Hero Image</label>
                                        <input type="file" name="hero_image_file" id="hero_image_file"
                                            class="form-control">
                                        <img id="hero_image_preview"
                                            src="{{ $about->hero_image ? asset('storage/' . $about->hero_image) : '' }}"
                                            style="max-height:150px; margin-top:10px;" alt="Preview Hero Image">
                                    </div>
                                </div>

                                <!-- Tagline -->
                                <div class="mb-3">
                                    <label class="form-label">Tagline</label>
                                    <textarea name="tagline" class="form-control" rows="3">{{ $about->tagline }}</textarea>
                                </div>

                                <!-- Partner Section -->
                                <h5>Partner Section</h5>
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="partner_title" class="form-control"
                                        value="{{ $about->partnerSection->title }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="partner_description" class="form-control" rows="3">{{ $about->partnerSection->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Partner Image</label>
                                    <input type="file" name="partner_image_file" id="partner_image_file"
                                        class="form-control">
                                    <img id="partner_image_preview"
                                        src="{{ $about->partnerSection->image_url ? asset('storage/' . $about->partnerSection->image_url) : '' }}"
                                        style="max-height:150px; margin-top:10px;" alt="Preview Partner Image">
                                </div>

                                <!-- Why Us Features -->
                                <h5>Why Us Features</h5>
                                <div id="why-us-features-container">
                                    @foreach ($about->whyUsFeatures as $index => $feature)
                                        <div class="input-group mb-2 feature-item">
                                            <input type="text" name="why_us_features[]" class="form-control"
                                                value="{{ $feature->text }}">
                                            <button type="button" class="btn btn-danger remove-feature">Remove</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="add-feature" class="btn btn-success mb-3">Add Feature</button>


                                <!-- Achievement -->
                                <h5>Achievement</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Achievement Count</label>
                                        <input type="text" name="achievement_count" class="form-control"
                                            value="{{ $about->achievement_count }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Achievement Label</label>
                                        <input type="text" name="achievement_label" class="form-control"
                                            value="{{ $about->achievement_label }}">
                                    </div>
                                </div>

                                <!-- Profile Section -->
                                <h5>Profile Section</h5>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Founding Year</label>
                                        <input type="number" name="founding_year" class="form-control"
                                            value="{{ $about->profileSection->founding_year }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Mission</label>
                                        <textarea name="mission" class="form-control" rows="3">{{ $about->profileSection->mission }}</textarea>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Video Embed URL</label>
                                        <input type="text" name="image_embed_url" class="form-control"
                                            value="{{ $about->profileSection->image_embed_url }}">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('why-us-features-container');
            const addButton = document.getElementById('add-feature');

            // Tambah fitur baru
            addButton.addEventListener('click', function() {
                const div = document.createElement('div');
                div.classList.add('input-group', 'mb-2', 'feature-item');

                div.innerHTML = `
            <input type="text" name="why_us_features[]" class="form-control" placeholder="Enter feature">
            <button type="button" class="btn btn-danger remove-feature">Remove</button>
        `;

                container.appendChild(div);

                // Tambahkan event listener ke tombol remove baru
                div.querySelector('.remove-feature').addEventListener('click', function() {
                    div.remove();
                });
            });

            // Remove fitur yang sudah ada
            document.querySelectorAll('.remove-feature').forEach(btn => {
                btn.addEventListener('click', function() {
                    btn.closest('.feature-item').remove();
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            function previewImage(inputId, imgId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(imgId);

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            previewImage('hero_image_file', 'hero_image_preview');
            previewImage('partner_image_file', 'partner_image_preview');
        });
    </script>
@endpush
