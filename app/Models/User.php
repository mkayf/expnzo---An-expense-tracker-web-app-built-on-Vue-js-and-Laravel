<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'password_set_at',
        'provider',
        'email_verified',
        'email_verified_at',
    ];

    protected $appends = ['has_custom_avatar'];

    protected $with = ['preferences'];

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
            'password_set_at' => 'datetime',
        ];
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? Storage::disk(config('filesystems.avatar_disk'))->url($value)
                : asset('media/default-avatar.avif')
        );
    }

    public function hasCustomAvatar(): Attribute
    {
        return Attribute::make(
            get: fn() => !is_null($this->getRawOriginal('avatar'))
        );
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class, 'user_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function customCategories()
    {
        return $this->hasMany(Category::class, 'user_id');
    }
}
