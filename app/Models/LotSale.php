<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LotSale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function lot()
    {
        return $this->belongsTo(LotEntry::class, 'lot_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
   
}
