<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'food_id',
        'quantity',
        'price',
        'subtotal',
    ];
    public function order(): BelongsTo{
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    public function food(): BelongsTo{
        return $this->belongsTo(Food::class, 'food_id', 'id');
    }

}
