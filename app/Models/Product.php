<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{

    use HasFactory;

    protected $fillable = [
        'image',
        'title',
        'slug',
        'category_id',
        'user_id',
        'description',
        'weight',
        'price',
        'stock',
        'discount'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('/storage/products/' . $value),
        );
    }

    public function reviewAvgRating(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? substr($value, 0, 3) : 0,
        );
    }

}
