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
        Schema::create('flashsale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flashsale_id')->constrained('flash_sales')->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
            $table->integer('size');
            $table->integer('stock');
            $table->integer('discount');
            $table->integer('flashsale_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashsale_items');
    }
};
