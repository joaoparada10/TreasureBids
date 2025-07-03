<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\FileController;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class Auction extends Model
{
    use HasFactory;

    protected $table = 'auction';
    public $timestamps  = false;

    protected $fillable = [
        'starting_price',
        'starting_date',
        'end_date',
        'buyout_price',
        'title',
        'picture',
        'description',
        'discount',
        'status',
        'owner_id',
    ];

    protected $casts = [
        'starting_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'owner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function highestBid(): HasOne
    {
        return $this->hasOne(Bid::class)->latestOfMany();
    }

    public function category(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'auctioncategory', 'auction_id', 'category_id');
    }

    public function itemRarity(): BelongsTo
    {
        return $this->belongsTo(ItemRarity::class);
    }

    public function followingMembers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'followedauction', 'auction_id', 'follower_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class, 'rated_auction_id');
    }

    public function isFollowedByAuthUser(): bool
    {
        $authUser = Auth::user(); // Get the authenticated user
        if (!$authUser) {
            return false; // No authenticated user
        }

        return $this->followingMembers()->where('follower_id', $authUser->id)->exists();
    }

    public function getAuctionImage() {
        return FileController::get('auction_type', $this->id);
    }
    

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, $keywords)
    {
        return $query->whereRaw(
            "search_vector @@ plainto_tsquery('english', ?)", 
            [$keywords]
        );
    }

    public function transaction()
{
    return $this->hasOne(Transaction::class, 'auction_id');
}
}

?>