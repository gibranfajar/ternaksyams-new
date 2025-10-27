<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyUsFeature extends Model
{
    protected $table = 'why_us_features';
    protected $guarded = [];

    public function about()
    {
        return $this->belongsTo(About::class);
    }
}
