<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');

            // Hero section
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('cta_shop')->nullable();
            $table->string('cta_shop_url')->nullable();
            $table->string('cta_subscribe')->nullable();
            $table->string('cta_subscribe_url')->nullable();
            $table->string('hero_image')->nullable();

            // Testimonial
            $table->text('testimonial_quote')->nullable();

            // Reviews
            $table->string('review_count')->nullable();
            $table->string('review_text')->nullable();
            $table->string('review_link_text')->nullable();
            $table->string('review_link_url')->nullable();
            $table->string('review_bg_color')->nullable();
            $table->string('review_text_color')->nullable();

            // Color Marque Features
            $table->string('color_marque_features_background')->nullable();
            $table->text('color_marque_features_text')->nullable();

            // Product Section
            $table->string('product_section_title')->nullable();
            $table->string('product_section_title_color')->nullable();

            // Product Sidebar
            $table->string('product_sidebar_headline')->nullable();
            $table->text('product_sidebar_description')->nullable();
            $table->string('product_sidebar_cta_text')->nullable();
            $table->string('product_sidebar_cta_url')->nullable();

            // About Section
            $table->string('about_section_title')->nullable();
            $table->text('about_section_description')->nullable();
            $table->string('about_section_cta_text')->nullable();
            $table->string('about_section_cta_url')->nullable();
            $table->string('about_section_image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_details');
    }
};
