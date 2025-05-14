<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LotEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function truckEntry()
    {
        return $this->belongsTo(TruckEntry::class, 'truck_id', 'id'); // Ensure correct foreign key
    }

    public function sales()
    {
        return $this->hasMany(LotSale::class, 'lot_id');
    }
}
