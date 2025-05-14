<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function lots()
    {
        return $this->hasMany(LotEntry::class, 'truck_id', 'id'); // Change 'truck_entry_id' to 'truck_id'
    }

    public function lotEntries()
    {
        return $this->hasMany(LotEntry::class, 'truck_id');
    }
}
