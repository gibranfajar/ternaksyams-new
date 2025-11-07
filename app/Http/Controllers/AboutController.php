<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\WhyUsFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    /**
     * Display the About page (single record).
     */
    public function index()
    {
        $about = About::with(['partnerSection', 'profileSection', 'whyUsFeatures'])->first();
        return view('abouts.index', compact('about'));
    }

    /**
     * Store (create if not exist) or update About data.
     */
    public function update(Request $request, $id)
    {
        $about = About::with(['partnerSection', 'profileSection'])->findOrFail($id);

        // --- VALIDASI DASAR ---
        $request->validate([
            'hero_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'partner_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // --- HERO IMAGE ---
        if ($request->hasFile('hero_image_file')) {
            // hapus gambar lama jika ada
            if ($about->hero_image && Storage::disk('public')->exists($about->hero_image)) {
                Storage::disk('public')->delete($about->hero_image);
            }

            // simpan gambar baru
            $about->hero_image = $request->file('hero_image_file')->store('about_images', 'public');
        }

        // --- PARTNER IMAGE ---
        $partner = $about->partnerSection;
        if ($partner && $request->hasFile('partner_image_file')) {
            if ($partner->image_url && Storage::disk('public')->exists($partner->image_url)) {
                Storage::disk('public')->delete($partner->image_url);
            }

            $partner->image_url = $request->file('partner_image_file')->store('about_images', 'public');
        }

        // --- UPDATE DATA UTAMA (ABOUT) ---
        $about->update([
            'hero_title'         => $request->hero_title,
            'hero_subtitle'      => $request->hero_subtitle,
            'tagline'            => $request->tagline,
            'achievement_count'  => $request->achievement_count,
            'achievement_label'  => $request->achievement_label,
            'hero_image'         => $about->hero_image, // pastikan ter-update
        ]);

        // --- UPDATE PARTNER SECTION ---
        if ($partner) {
            $partner->update([
                'title'       => $request->partner_title,
                'description' => $request->partner_description,
                'image_url'   => $partner->image_url ?? $partner->getOriginal('image_url'),
            ]);
        }

        // --- UPDATE PROFILE SECTION ---
        $profile = $about->profileSection;
        if ($profile) {
            $profile->update([
                'founding_year'   => $request->founding_year,
                'mission'         => $request->mission,
                'image_embed_url' => $request->image_embed_url,
            ]);
        }

        // --- UPDATE WHY US FEATURES ---
        $about->whyUsFeatures()->delete();

        if ($request->filled('why_us_features')) {
            foreach ($request->why_us_features as $feature) {
                if (trim($feature) !== '') {
                    $about->whyUsFeatures()->create(['text' => $feature]);
                }
            }
        }

        return redirect()->back()->with('success', 'About page berhasil diperbarui!');
    }
}
