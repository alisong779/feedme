<?php

namespace App\Models;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
    'recipe_id',
    'quantity',
    'measurement',
    'name',
    'sort_order',
];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
