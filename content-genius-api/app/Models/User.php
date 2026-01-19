<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'credits',
        'total_generations',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'credits' => 'integer',
            'total_generations' => 'integer',
        ];
    }

    /**
     * Get the user's content generations.
     */
    public function contentGenerations(): HasMany
    {
        return $this->hasMany(ContentGeneration::class);
    }

    /**
     * Check if user has enough credits.
     */
    public function hasCredits(int $amount): bool
    {
        return $this->credits >= $amount;
    }

    /**
     * Deduct credits from user.
     */
    public function deductCredits(int $amount): bool
    {
        if (!$this->hasCredits($amount)) {
            return false;
        }

        $this->decrement('credits', $amount);
        $this->increment('total_generations');

        return true;
    }

    /**
     * Add credits to user.
     */
    public function addCredits(int $amount): void
    {
        $this->increment('credits', $amount);
    }
}
