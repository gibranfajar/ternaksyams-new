<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BrandAbout;
use App\Models\BrandDetail;
use App\Models\BrandFeature;
use App\Models\BrandHowitwork;
use App\Models\BrandProductsidebar;
use App\Models\BrandSize;
use App\Models\BrandTestimonial;
use App\Models\BrandVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // === 1️⃣ Upload utama: image brand ===
            $brandImage = null;
            if ($request->hasFile('image')) {
                $brandImage = $request->file('image')->store('brands', 'public');
            }

            // === 2️⃣ Simpan ke table brands ===
            $brand = Brand::create([
                'brand' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'image' => $brandImage,
            ]);

            // === 3️⃣ Simpan ke brand_sizes ===
            if ($request->filled('sizes')) {
                foreach ($request->sizes as $size) {
                    BrandSize::create([
                        'brand_id' => $brand->id,
                        'size' => $size,
                    ]);
                }
            }

            // === Simpan ke brand_variants ===
            if (!empty($request->variants['name'])) {
                foreach ($request->variants['name'] as $index => $variantName) {
                    $variantImage = null;
                    $variantDescription = $request->variants['descriptions'][$index] ?? null;

                    if (!empty($request->variants['images'][$index])) {
                        $variantImage = $request->variants['images'][$index]->store('brand_variants', 'public');
                    }

                    BrandVariant::create([
                        'brand_id'    => $brand->id,
                        'variant'     => $variantName,
                        'description' => $variantDescription,
                        'image'       => $variantImage,
                    ]);
                }
            }

            // === 5️⃣ Simpan ke brand_details ===
            $bannerPath = null;
            if ($request->hasFile('detail_banner')) {
                $bannerPath = $request->file('detail_banner')->store('brand_banners', 'public');
            }

            BrandDetail::create([
                'brand_id' => $brand->id,
                'herotitle' => $request->detail_title,
                'herosubtitle' => $request->detail_subtitle,
                'banner' => $bannerPath,
            ]);

            // === 6️⃣ Simpan ke brand_testimonials ===
            BrandTestimonial::create([
                'brand_id' => $brand->id,
                'quotes' => $request->detail_quotes,
                'textreview' => $request->detail_text_review,
                'textcta' => $request->detail_textcta_review,
                'linkcta' => $request->detail_linkcta_review,
                'cardcolor' => $request->detail_cardcolor_review,
                'textcolor' => $request->detail_textcolor_review,
            ]);

            // === 7️⃣ Simpan ke brand_features ===
            BrandFeature::create([
                'brand_id' => $brand->id,
                'marquebgcolor' => $request->detail_marque_bgcolor,
                'marquetextcolor' => $request->detail_marque_textcolor,
                'features' => $request->detail_marque,
            ]);

            // === 8️⃣ Simpan ke brand_productsidebars ===
            BrandProductsidebar::create([
                'brand_id' => $brand->id,
                'headline' => $request->detail_headline_product,
                'description' => $request->detail_description_product,
                'ctatext' => $request->detail_ctatext_product,
                'ctalink' => $request->detail_ctalink_product,
                'cardcolor' => $request->detail_cardcolor_product,
                'textcolor' => $request->detail_textcolor_product,
            ]);

            // === 9️⃣ Simpan ke brand_abouts ===
            $aboutImage = null;
            if ($request->hasFile('detail_about_image')) {
                $aboutImage = $request->file('detail_about_image')->store('brand_about', 'public');
            }

            BrandAbout::create([
                'brand_id' => $brand->id,
                'title' => $request->detail_title_about,
                'description' => $request->{'detail_description-about'},
                'image' => $aboutImage,
                'ctatext' => $request->detail_about_ctatext,
                'ctalink' => $request->detail_about_ctalink,
            ]);

            // === 🔟 Simpan ke brand_howitworks ===
            $howImage = null;
            if ($request->hasFile('detail_howitwork_image')) {
                $howImage = $request->file('detail_howitwork_image')->store('brand_howitwork', 'public');
            }

            BrandHowitwork::create([
                'brand_id' => $brand->id,
                'tagline' => $request->detail_tagline_howitwork,
                'image' => $howImage,
                'headline' => $request->detail_headline_howitwork,
                'steps' => $request->detail_steps_howitwork,
                'ctatext' => $request->detail_ctatext_product ?? '', // opsional jika belum ada di form
                'ctalink' => $request->detail_ctalink_product ?? '',
            ]);

            DB::commit();

            return redirect()->route('brands.index')->with('success', 'Brand berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan brand: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Brand $brand)
    {
        $brand->load('sizes', 'variants', 'detail', 'testimonial', 'feature', 'productsidebar', 'about', 'howitwork');
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $brand = Brand::findOrFail($id);

            // === 1️⃣ Update image utama (brand image) ===
            $brandImage = $brand->image;
            if ($request->hasFile('image')) {
                if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                    Storage::disk('public')->delete($brand->image);
                }
                $brandImage = $request->file('image')->store('brands', 'public');
            }

            // === 2️⃣ Update data utama brand ===
            $brand->update([
                'brand' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'image' => $brandImage,
            ]);

            // === 3️⃣ Update brand_sizes ===
            $brand->sizes()->delete(); // hapus dulu semua ukuran lama
            if ($request->filled('sizes')) {
                foreach ($request->sizes as $size) {
                    BrandSize::create([
                        'brand_id' => $brand->id,
                        'size' => $size,
                    ]);
                }
            }

            // === 4️⃣ Update brand_variants ===
            $brand->variants()->delete();
            if (!empty($request->variants['name'])) {
                foreach ($request->variants['name'] as $index => $variantName) {
                    $variantImage = null;
                    $variantDescription = $request->variants['descriptions'][$index] ?? null;

                    if (!empty($request->variants['images'][$index])) {
                        $variantImage = $request->variants['images'][$index]->store('brand_variants', 'public');
                    }

                    BrandVariant::create([
                        'brand_id'    => $brand->id,
                        'variant'     => $variantName,
                        'description' => $variantDescription,
                        'image'       => $variantImage,
                    ]);
                }
            }

            // === 5️⃣ Update brand_detail ===
            $detail = $brand->detail;
            $bannerPath = $detail ? $detail->banner : null;

            if ($request->hasFile('detail_banner')) {
                if ($bannerPath && Storage::disk('public')->exists($bannerPath)) {
                    Storage::disk('public')->delete($bannerPath);
                }
                $bannerPath = $request->file('detail_banner')->store('brand_banners', 'public');
            }

            $brand->detail()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'herotitle' => $request->detail_title,
                    'herosubtitle' => $request->detail_subtitle,
                    'banner' => $bannerPath,
                ]
            );

            // === 6️⃣ Update brand_testimonial ===
            $brand->testimonial()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'quotes' => $request->detail_quotes,
                    'textreview' => $request->detail_text_review,
                    'textcta' => $request->detail_textcta_review,
                    'linkcta' => $request->detail_linkcta_review,
                    'cardcolor' => $request->detail_cardcolor_review,
                    'textcolor' => $request->detail_textcolor_review,
                ]
            );

            // === 7️⃣ Update brand_feature ===
            $brand->feature()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'marquebgcolor' => $request->detail_marque_bgcolor,
                    'marquetextcolor' => $request->detail_marque_textcolor,
                    'features' => $request->detail_marque,
                ]
            );

            // === 8️⃣ Update brand_productsidebar ===
            $brand->productsidebar()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'headline' => $request->detail_headline_product,
                    'description' => $request->detail_description_product,
                    'ctatext' => $request->detail_ctatext_product,
                    'ctalink' => $request->detail_ctalink_product,
                    'cardcolor' => $request->detail_cardcolor_product,
                    'textcolor' => $request->detail_textcolor_product,
                ]
            );

            // === 9️⃣ Update brand_about ===
            $about = $brand->about;
            $aboutImage = $about ? $about->image : null;

            if ($request->hasFile('detail_about_image')) {
                if ($aboutImage && Storage::disk('public')->exists($aboutImage)) {
                    Storage::disk('public')->delete($aboutImage);
                }
                $aboutImage = $request->file('detail_about_image')->store('brand_about', 'public');
            }

            $brand->about()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'title' => $request->detail_title_about,
                    'description' => $request->{'detail_description-about'},
                    'image' => $aboutImage,
                    'ctatext' => $request->detail_about_ctatext,
                    'ctalink' => $request->detail_about_ctalink,
                ]
            );

            // === 🔟 Update brand_howitwork ===
            $howitwork = $brand->howitwork;
            $howImage = $howitwork ? $howitwork->image : null;

            if ($request->hasFile('detail_howitwork_image')) {
                if ($howImage && Storage::disk('public')->exists($howImage)) {
                    Storage::disk('public')->delete($howImage);
                }
                $howImage = $request->file('detail_howitwork_image')->store('brand_howitwork', 'public');
            }

            $brand->howitwork()->updateOrCreate(
                ['brand_id' => $brand->id],
                [
                    'tagline' => $request->detail_tagline_howitwork,
                    'image' => $howImage,
                    'headline' => $request->detail_headline_howitwork,
                    'steps' => $request->detail_steps_howitwork,
                    'ctatext' => $request->detail_ctatext_product ?? '',
                    'ctalink' => $request->detail_ctalink_product ?? '',
                ]
            );

            DB::commit();

            return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui brand: ' . $e->getMessage())->withInput();
        }
    }
}
