<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class   Customer extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $guarded = [];

    public function lotSales()
    {
        return $this->hasMany(LotSale::class, 'customer_id');
    }

    public function latestLedger()
    {
        return $this->hasOne(CustomerLedger::class, 'customer_id')->latestOfMany();
    }
}
