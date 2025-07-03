<?php

namespace App\Models;

use App\Models\Member;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'user_id', 
        'auction_id'
    ];

    protected $table = 'bid';
    public $timestamps  = false;

    public function auction(){
        return $this->belongsTo(Auction::class, 'auction_id');    }

    public function bidder() {
        return $this->belongsTo(Member::class, 'user_id');
    }
}
