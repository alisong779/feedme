<?php

namespace App\Models;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;

class RecipeCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function recipes()
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_category_assignments',
            'recipe_category_id',
            'recipe_id'
        );
    }
}
