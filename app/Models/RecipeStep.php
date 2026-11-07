<?php

namespace App\Models;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;

class RecipeStep extends Model
{
    protected $fillable = [
        'recipe_id',
        'instruction',
        'sort_order',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
