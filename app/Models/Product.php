<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'unit',
        'cost_price',
        'sell_price',
        'reorder_level',
        'metadata',
        'description',
        'barcode',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock levels for this product.
     */
    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    /**
     * Get total stock across all warehouses.
     */
    public function getTotalStockAttribute()
    {
        return $this->stockLevels->sum('available_qty');
    }

    /**
     * Check if product is low in stock.
     */
    public function isLowStock()
    {
        return $this->total_stock <= $this->reorder_level;
    }
}
