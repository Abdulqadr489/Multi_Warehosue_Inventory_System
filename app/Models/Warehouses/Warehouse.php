<?php

namespace App\Models\Warehouses;

use App\Models\Countreis\Country;
use App\Models\Inventories\Inventory;
use App\Models\InventoryTransactions\InventoryTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'country_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }


    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }


    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
