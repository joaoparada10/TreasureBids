<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';
    public $timestamps  = false;

    protected $fillable = ['name', 'color'];

    public function auctions(): BelongsToMany
    {
        return $this->belongsToMany(Auction::class, 'auctioncategory');
    }
}
