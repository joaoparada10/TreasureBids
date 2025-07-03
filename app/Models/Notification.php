<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    public $timestamps  = false;

    protected $fillable = [
        'notified_id',
        'urgency',
        'text',
        'url',
        'date',
        'seen',
    ];

    public function member(): BelongsTo {
        return $this->belongsTo(Member::class, 'notified_id');
    }


}
