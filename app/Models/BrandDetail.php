<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandDetail extends Model
{
    protected $table = 'brand_details';
    protected $guarded = [];

    public function features()
    {
        return $this->hasMany(FeatureBrand::class);
    }

    public function howItWorks()
    {
        return $this->hasOne(HowItWorksBrand::class);
    }
}
