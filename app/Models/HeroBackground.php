<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBackground extends Model
{
    use HasFactory;

    protected $table = 'hero_backgrounds';

    protected $fillable = [
        'image_path',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute(): string
    {
        return asset($this->image_path);
    }
}
