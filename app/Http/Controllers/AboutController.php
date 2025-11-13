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
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image3' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image4' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hero_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'partner_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // --- FILE UPLOAD ---
        $fileFields = ['banner', 'image1', 'image2', 'image3', 'image4', 'hero_image_file', 'partner_image_file'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $oldFile = null;
                $storagePath = 'about_images';

                // tentukan file lama
                if (in_array($field, ['banner', 'image1', 'image2', 'image3', 'image4'])) {
                    $oldFile = $about->$field;
                } elseif ($field === 'hero_image_file') {
                    $oldFile = $about->hero_image;
                } elseif ($field === 'partner_image_file') {
                    $oldFile = $about->partnerSection->image_url ?? null;
                }

                // hapus file lama jika ada
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }

                // simpan file baru
                $path = $request->file($field)->store($storagePath, 'public');

                // simpan ke database
                if (in_array($field, ['banner', 'image1', 'image2', 'image3', 'image4'])) {
                    $about->$field = $path;
                } elseif ($field === 'hero_image_file') {
                    $about->hero_image = $path;
                } elseif ($field === 'partner_image_file') {
                    if ($about->partnerSection) {
                        $about->partnerSection->image_url = $path;
                        $about->partnerSection->save();
                    }
                }
            }
        }

        // --- UPDATE DATA UTAMA (ABOUT) ---
        $about->update([
            'hero_title'         => $request->hero_title,
            'hero_subtitle'      => $request->hero_subtitle,
            'tagline'            => $request->tagline,
            'achievement_count'  => $request->achievement_count,
            'achievement_label'  => $request->achievement_label,
            'banner'             => $about->banner,
            'image1'             => $about->image1,
            'image2'             => $about->image2,
            'image3'             => $about->image3,
            'image4'             => $about->image4,
            'hero_image'         => $about->hero_image,
        ]);

        // --- UPDATE PARTNER SECTION ---
        if ($about->partnerSection) {
            $about->partnerSection->update([
                'title'       => $request->partner_title,
                'description' => $request->partner_description,
                'image_url'   => $about->partnerSection->image_url ?? $about->partnerSection->getOriginal('image_url'),
            ]);
        }

        // --- UPDATE PROFILE SECTION ---
        if ($about->profileSection) {
            $about->profileSection->update([
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
