<?php

namespace App\Models;

use App\Models\Ingredient;
use App\Models\Post;
use App\Models\RecipeCategory;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
   protected $fillable = [
    'post_id',
    'prep_time_minutes',
    'cook_time_minutes',
    'servings',
    'difficulty',
];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class)
            ->orderBy('sort_order');
    }

    public function steps()
    {
        return $this->hasMany(RecipeStep::class)
            ->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(
            RecipeCategory::class,
            'recipe_category_assignments',
            'recipe_id',
            'recipe_category_id'
        );
    }
}
