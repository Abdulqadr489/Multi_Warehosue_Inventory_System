<?php

namespace App\Models\Countreis;

use App\Models\Warehouses\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'code',
    ];


    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
}
