<?php

namespace App\Models\Product;

use App\Models\Inventories\Inventory;
use App\Models\InventoryTransactions\InventoryTransaction;
use App\Models\Suppliers\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'status',
        'description',
        'price',
    ];



    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }


    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
