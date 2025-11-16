<?php

namespace App\Models\Suppliers;

use App\Models\InventoryTransactions\InventoryTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_info',
        'address',
    ];

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
