<?php

namespace Database\Seeders;
use App\Models\RecipeCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Chicken',
            'Beef',
            'Pork',
            'Seafood',
            'Vegetarian',
            'Breakfast',
            'Lunch',
            'Dinner',
            'Dessert',
            'Appetizer',
            'Slow Cooker',
            'Instant Pot',
            'Side Dish',
            'Main Dish',
            'Soup',
            'Salad',
            'Baking',
        ];

        foreach ($categories as $category) {

            RecipeCategory::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($category)],
                ['name' => $category]
            );

        }
    }
}

