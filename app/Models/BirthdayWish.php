<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdayWish extends Model
{
    use HasFactory;

    protected $fillable = ['wish', 'tone', 'audience', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function random(): ?self
    {
        return static::where('is_active', true)->inRandomOrder()->first();
    }

    /**
     * Get a random wish matched to age-appropriate audience
     */
    public static function randomForAge(?int $age): ?self
    {
        if (!$age) return static::random();

        // Map age to preferred audiences
        $audiences = ['Universal', 'Colleague'];
        if ($age < 25) {
            $audiences = array_merge($audiences, ['Youth', 'Young Adults']);
        } elseif ($age < 35) {
            $audiences = array_merge($audiences, ['Young Adults', 'Adults']);
        } elseif ($age < 50) {
            $audiences = array_merge($audiences, ['Adults', 'Professional']);
        } else {
            $audiences = array_merge($audiences, ['Elders', 'Adults']);
        }

        $wish = static::where('is_active', true)
            ->whereIn('audience', $audiences)
            ->inRandomOrder()
            ->first();

        return $wish ?? static::random();
    }
}
