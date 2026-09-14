<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';

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
      'name',
        'slug',
      'description',
      'image',
      'status',
    ];
    public function products(){
        return $this->hasMany(Food::class);
    }
}
