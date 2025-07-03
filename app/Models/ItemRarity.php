<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRarity extends Model
{
    use HasFactory;

    protected $table = 'itemrarity';
    public $timestamps  = false;

    public function auctions(): HasMany{
        return $this->hasMany(Auction::class);
    }
}
