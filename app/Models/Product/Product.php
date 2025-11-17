<?php

namespace App\Models\Product;

use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
