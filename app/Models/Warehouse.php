<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'manager_id',
        'status',
    ];

    /**
     * Get the manager of the warehouse.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users assigned to this warehouse.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the stock levels for this warehouse.
     */
    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }
}
