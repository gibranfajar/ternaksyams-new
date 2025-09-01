<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantSize extends Model
{
    protected $table = 'variant_sizes';

    protected $guarded = ['id'];

    // relasi ke table variant
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    // relasi ke table size
    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
