<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Post extends Model
{
     use Searchable;
     use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'image',
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }
}
