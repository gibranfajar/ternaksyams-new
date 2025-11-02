<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HowitworksBrand extends Model
{
    protected $table = 'howitworks_brands';
    protected $guarded = [];

    public function headlines()
    {
        return $this->hasMany(HowItWorksHeadline::class, 'howitworks_id');
    }

    public function steps()
    {
        return $this->hasMany(HowItWorksStep::class, 'howitworks_id');
    }
}
