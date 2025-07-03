<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

use App\Http\Controllers\FileController;


class Member extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_pic',
        'credit',   // prolly safer to not make these $fillable but for now its needed for admin functions
        'blocked',   // prolly safer to not make these $fillable but for now its needed for admin functions
        'address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ]; */



    public function auctions(): HasMany {
        return $this->hasMany(Auction::class, 'owner_id');
    }

    public function bids(): HasMany {
        return $this->hasMany(Bid::class, 'user_id');
    }

    public function followedAuctions(): BelongsToMany {
        return $this->belongsToMany(Auction::class, 'followedauction', 'follower_id', 'auction_id');    }

    public function auctionsWon(): HasMany {
        return $this->hasMany(Auction::class, 'Transaction','member_id','auction_id');
    }

    public function ratingsGiven(): HasMany {
        return $this->hasMany(Rating::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'notified_id');
    }

    public function ratingsReceived()
{
    return $this->hasManyThrough(Rating::class, Auction::class, 'owner_id', 'rated_auction_id');
}

public function getAverageRatingAttribute()
{
    $average = $this->ratingsReceived()->avg('rating_value');

    return $average !== null ? rtrim(rtrim(number_format($average, 2), '0'), '.') : null;
}



public function transactions()
{
    return $this->hasMany(Transaction::class, 'buyer_id');
}

public function getProfileImage() {
    return FileController::get('profile_type', $this->id);
}


}
