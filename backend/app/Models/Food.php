<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;
    protected $table = 'foods';

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->resolveImageUrl($value)
        );
    }

    private function resolveImageUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return url('storage/' . $value);
    }
    protected $fillable = [
        'category_id',
        'name',
        'image',
        'price',
        'quantity',
        'description',
        'status',
    ];
    public function category(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
