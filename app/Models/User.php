<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'gender',
        'password',
        'dob',
        'phone',
        'avatar',
        'provider_avatar'
    ];

    protected $appends = [
        'full_name'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'role_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_id' => 'integer'
    ];

    protected $attributes = [
        'avatar' => 'images/users/default-user.png'
    ];

    public function cartProducts()
    {
        return $this->belongsToMany(ProductVariant::class, 'cart_product')->withPivot('quantity')->withTimestamps();
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlist_product')->withTimestamps();
    }

    public function wishlistPublicProducts()
    {
        return $this->wishlistProducts()->status(1);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function providers()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    public function getAvatarAttribute($value)
    {
        return asset("storage/$value");
    }

    public function getRawAvatarAttribute()
    {
        return $this->getRawOriginal('avatar');
    }

    public function getEmailAttribute($value)
    {
        $user = auth('sanctum')->user();
        if (!empty($user))
            if ($user->id === $this->id || $user->isAdmin()) {
                return $value;
            }
        return preg_replace("/(?!^).(?=[^@]+@)/", "*", $value);
    }

    public function getFullNameAttribute($value)
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isAdmin()
    {
        return $this->role_id == 0;
    }

    public function hasBought($product)
    {
        return $product
            ->variants()
            ->whereHas('orders', function ($q) {
                return $q->where('user_id', $this->id);
            })->exists();
    }

    public function hasReviewed($product)
    {
        return $product->reviews()->where('user_id', $this->id)->exists();
    }
}
