<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';
    public $timestamps  = false;

    protected $fillable = [
        'buyer_id',
        'auction_id',
        'price',
        'date',
    ];

    public function buyer()
    {
        return $this->belongsTo(Member::class, 'buyer_id');
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }
}
