<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdayWish extends Model
{
    use HasFactory;

    protected $fillable = ['wish', 'tone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function random(): ?self
    {
        return static::where('is_active', true)->inRandomOrder()->first();
    }
}
