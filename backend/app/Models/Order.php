<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_number',
        'table_id',
        'customer_id',
        'order_type',
        'status',
        'subtotal',
        'discount',
        'total',
    ];
    public function table(): BelongsTo{
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function items(): HasMany{
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function payment(){
        return $this->hasOne(Payment::class, 'order_id', 'id');
    }
}
