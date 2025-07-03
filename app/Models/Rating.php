<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'rating';
    public $timestamps  = false;

    protected $fillable = [
        'rating_value',
        'comment',
        'date',
        'rater_id',
        'rated_auction_id',
    ];

    // Relationship with Member (Rater)
    public function rater()
    {
        return $this->belongsTo(Member::class, 'rater_id');
    }

    // Relationship with Auction
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'rated_auction_id');
    }
}
