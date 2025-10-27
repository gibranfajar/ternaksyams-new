<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\WhyUsFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $about = About::first();
        return view('abouts.index', compact('about'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(About $about)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(About $about)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $about = About::with(['partnerSection', 'profileSection'])->findOrFail($id);

        // Hero Image
        if ($request->hasFile('hero_image_file')) {
            if ($about->hero_image && Storage::exists($about->hero_image)) {
                Storage::delete($about->hero_image);
            }
            $about->hero_image = $request->file('hero_image_file')->store('about_images', 'public');
        }

        // Partner Image
        $partner = $about->partnerSection;
        if ($request->hasFile('partner_image_file')) {
            if ($partner->image_url && Storage::exists($partner->image_url)) {
                Storage::delete($partner->image_url);
            }
            $partner->image_url = $request->file('partner_image_file')->store('about_images', 'public');
        }

        // Update About utama
        $about->update([
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'tagline' => $request->tagline,
            'achievement_count' => $request->achievement_count,
            'achievement_label' => $request->achievement_label,
        ]);

        // Update Partner Section
        $partner->update([
            'title' => $request->partner_title,
            'description' => $request->partner_description,
        ]);

        // Update Profile Section
        $profile = $about->profileSection;
        $profile->update([
            'founding_year' => $request->founding_year,
            'mission' => $request->mission,
            'image_embed_url' => $request->image_embed_url,
        ]);

        // Why Us Features
        $about->whyUsFeatures()->delete();
        if ($request->has('why_us_features')) {
            foreach ($request->why_us_features as $feature) {
                if ($feature) {
                    $about->whyUsFeatures()->create(['text' => $feature]);
                }
            }
        }

        return redirect()->back()->with('success', 'About berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(About $about)
    {
        //
    }
}
