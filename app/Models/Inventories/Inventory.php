<?php

namespace App\Models\Inventories;

use App\Models\Product\Product;
use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'minimum_quantity',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
