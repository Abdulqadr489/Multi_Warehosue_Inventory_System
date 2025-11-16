<?php

namespace App\Models\InventoryTransactions;

use App\Models\Product\Product;
use App\Models\Suppliers\Supplier;
use App\Models\User;
use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'supplier_id',
        'quantity',
        'transaction_type',
        'date',
        'created_by',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
