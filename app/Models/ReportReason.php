<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    use HasFactory;

    protected $table = 'reportreason';
    public $timestamps  = false;

    public function reports(): HasMany {
        return $this->hasMany(Report::class);
    }
}
