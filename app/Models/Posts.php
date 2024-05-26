<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Posts extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'image',
        'type',
        'posted_by',
    ];

    public function recipients(): MorphMany
    {
        return $this->morphMany(Recipients::class, 'receivable');
    }
}