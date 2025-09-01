<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlashSale extends Model
{
    use SoftDeletes;

    protected $table = 'flash_sales';

    protected $guarded = ['id'];

    public function variants()
    {
        return $this->hasMany(Variant::class, 'flashsale_id', 'id');
    }
}
