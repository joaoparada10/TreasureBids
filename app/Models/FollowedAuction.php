<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowedAuction extends Model
{
    use HasFactory;

    protected $table = 'followedauction';
    public $timestamps  = false;

    public function auction(): HasOne {
        return $this->hasOne(Auction::class);
    }
    
    public function member(): HasOne {
        return $this->hasOne(Member::class);
    }
}
