<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'available_qty',
        'reserved_qty',
    ];

    /**
     * Get the product that owns the stock level.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock level.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the total quantity (available + reserved).
     */
    public function getTotalQtyAttribute()
    {
        return $this->available_qty + $this->reserved_qty;
    }
}
