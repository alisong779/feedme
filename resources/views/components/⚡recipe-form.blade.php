<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\RecipeCategory;
use Livewire\Attributes\Computed;
use App\Models\Post;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public ?int $recipeId = null;

    public $name = '';
    public $description = '';

    public $image;
    public ?string $existingImage = null;

    public $prepTime = '';
    public $cookTime = '';
    public $servings = '';
    public $difficulty = '';

    public $selectedCategories = [];

    public $ingredients = [
        [
            'quantity' => '',
            'measurement' => '',
            'name' => ''
        ]
    ];

    public $steps = [
        ['instruction' => '']
    ];

    public function mount(?Recipe $recipe = null)
    {
        // If no existing recipe was passed, this is the create form.
        if (!$recipe || !$recipe->exists) {
            return;
        }

        // Make sure the logged-in user owns this recipe.
        abort_unless(
            $recipe->post()->where('user_id', auth()->id())->exists(),
            403
        );

        // Load the recipe and its related information.
        $recipe->load([
            'post',
            'ingredients',
            'steps',
            'categories'
        ]);

        $this->recipeId = $recipe->id;

        // Populate the main recipe fields.
        $this->name = $recipe->post->title;
        $this->description = $recipe->post->body;
        $this->existingImage = $recipe->post->image;

        // Populate preparation information.
        $this->prepTime = $recipe->prep_time_minutes;
        $this->cookTime = $recipe->cook_time_minutes;
        $this->servings = $recipe->servings;
        $this->difficulty = $recipe->difficulty;

        // Populate selected categories.
        $this->selectedCategories = $recipe->categories
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        // Populate ingredients.
        $this->ingredients = $recipe->ingredients
            ->map(fn ($ingredient) => [
                'quantity' => $ingredient->quantity ?? '',
                'measurement' => $ingredient->measurement ?? '',
                'name' => $ingredient->name,
            ])
            ->toArray();

        // Populate instructions.
        $this->steps = $recipe->steps
            ->map(fn ($step) => [
                'instruction' => $step->instruction,
            ])
            ->toArray();

        // Make sure the form always has at least one row.
        if (empty($this->ingredients)) {
            $this->ingredients = [
                ['quantity' => '', 'measurement' => '', 'name' => '']
            ];
        }

        if (empty($this->steps)) {
            $this->steps = [
                ['instruction' => '']
            ];
        }
    }
     
    #[Computed]
    public function categories()
    {
        return RecipeCategory::orderBy('name')->get();
    }

    public function addIngredient()
    {
        $this->ingredients[] = [
            'quantity' => '',
            'measurement' => '',
            'name' => ''
        ];
    }

    public function removeIngredient($index)
    {
        if (count($this->ingredients) <= 1) {
            return;
        }

        unset($this->ingredients[$index]);

        $this->ingredients = array_values($this->ingredients);
    }

    public function addStep()
    {
        $this->steps[] = [
            'instruction' => ''
        ];
    }

    public function removeStep($index)
    {
        if (count($this->steps) <= 1) {
            return;
        }

        unset($this->steps[$index]);

        $this->steps = array_values($this->steps);
    }

   public function saveRecipe()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',

            'image' => 'nullable|image|max:5120',

            'prepTime' => 'nullable|integer|min:0',
            'cookTime' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'difficulty' => 'nullable|in:Easy,Medium,Hard',

            'selectedCategories' => 'array',
            'selectedCategories.*' => 'integer|exists:recipe_categories,id',

            'ingredients' => 'required|array|min:1',
            'ingredients.*.quantity' => 'nullable|string|max:50',
            'ingredients.*.measurement' => 'nullable|string|max:100',
            'ingredients.*.name' => 'required|string|max:255',

            'steps' => 'required|array|min:1',
            'steps.*.instruction' => 'required|string',
        ]);

        $isEditing = !empty($this->recipeId);

        // If editing, retrieve the recipe and verify ownership.
        $recipe = null;

        if ($isEditing) {
            $recipe = Recipe::with('post')
                ->whereKey($this->recipeId)
                ->whereHas('post', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();
        }

        $imagePath = null;
        $oldImagePath = $recipe?->post->image;

        try {
            // Store a newly uploaded image, if one was selected.
            if ($this->image) {
                $imagePath = $this->image->store('posts', 'public');
            }

            DB::transaction(function () use ($validated, $imagePath, &$recipe) {

                if ($recipe) {

                    // Update the existing post.
                    $postData = [
                        'title' => $validated['name'],
                        'body' => $validated['description'],
                    ];

                    if ($imagePath) {
                        $postData['image'] = $imagePath;
                    }

                    $recipe->post->update($postData);

                    // Update the existing recipe information.
                    $recipe->update([
                        'prep_time_minutes' => $validated['prepTime'] ?: null,
                        'cook_time_minutes' => $validated['cookTime'] ?: null,
                        'servings' => $validated['servings'] ?: null,
                        'difficulty' => $validated['difficulty'] ?: null,
                    ]);

                    // Remove old ingredients and instructions.
                    $recipe->ingredients()->delete();
                    $recipe->steps()->delete();

                } else {

                    // Create a new post.
                    $post = Post::create([
                        'user_id' => auth()->id(),
                        'title' => $validated['name'],
                        'body' => $validated['description'],
                        'image' => $imagePath,
                    ]);

                    // Create the associated recipe.
                    $recipe = Recipe::create([
                        'post_id' => $post->id,
                        'prep_time_minutes' => $validated['prepTime'] ?: null,
                        'cook_time_minutes' => $validated['cookTime'] ?: null,
                        'servings' => $validated['servings'] ?: null,
                        'difficulty' => $validated['difficulty'] ?: null,
                    ]);
                }

                // Save ingredients.
                foreach ($validated['ingredients'] as $index => $ingredient) {
                    $recipe->ingredients()->create([
                        'quantity' => $ingredient['quantity'],
                        'measurement' => $ingredient['measurement'],
                        'name' => $ingredient['name'],
                        'sort_order' => $index,
                    ]);
                }

                // Save instructions.
                foreach ($validated['steps'] as $index => $step) {
                    $recipe->steps()->create([
                        'instruction' => $step['instruction'],
                        'sort_order' => $index,
                    ]);
                }

                // Update selected categories.
                $recipe->categories()->sync(
                    $validated['selectedCategories']
                );
            });

        } catch (\Throwable $e) {

            // Remove the newly uploaded image if saving fails.
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $e;
        }

        // Remove the previous image only after the database update succeeds.
        if ($imagePath && $oldImagePath && $oldImagePath !== $imagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        if ($isEditing) {
            session()->flash('success', 'Your recipe was updated successfully!');

            return redirect()->route('recipe.show', $recipe->id);
        }

        session()->flash('success', 'Your recipe was created successfully!');

        $this->reset();
    }

};
?>
<div class="container py-5">
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container--narrow mx-auto">

        <h2 class="mb-4">
            {{ $recipeId ? 'Edit Recipe' : 'Create a Recipe' }}
        </h2>

        {{-- Recipe Name --}}
        <div class="form-group">
            <label>Recipe Name</label>

            <input
                type="text"
                class="form-control"
                wire:model="name"
                placeholder="Enter recipe name"
            >
        </div>

        {{-- Description --}}
        <div class="form-group">
            <label>Description</label>

            <textarea
                class="form-control"
                wire:model="description"
                rows="3"
                placeholder="Tell us about your recipe..."
            ></textarea>
        </div>

        {{-- Recipe Image --}}
        <hr>

        <h4>Recipe Image</h4>

        <div class="form-group">
            <label for="image">Upload a recipe photo</label>

            <input
                type="file"
                id="image"
                class="form-control-file"
                wire:model="image"
                accept="image/*"
            >

            @error('image')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <div wire:loading wire:target="image" class="text-muted mt-2">
                Uploading image...
            </div>

            @if ($image)
                {{-- Preview newly uploaded image --}}
                <div class="mt-3">
                    <img
                        src="{{ $image->temporaryUrl() }}"
                        class="img-fluid rounded"
                        style="max-width: 350px;"
                        alt="New recipe image preview"
                    >
                </div>
            @elseif ($existingImage)
                {{-- Display the currently saved image --}}
                <div class="mt-3">
                    <p class="text-muted mb-2">Current recipe image:</p>
                    <img
                        src="{{ asset('storage/' . $existingImage) }}"
                        class="img-fluid rounded"
                        style="max-width: 350px;"
                        alt="Current recipe image"
                    >
                </div>
            @endif
        </div>

        <hr>

        {{-- Preparation Information --}}
        <h4>Preparation Information</h4>

        <div class="form-row">

            <div class="form-group col-md-3">
                <label>Prep Time (minutes)</label>
                <input
                    type="number"
                    class="form-control"
                    wire:model="prepTime"
                    min="0"
                >
            </div>

            <div class="form-group col-md-3">
                <label>Cook Time (minutes)</label>
                <input
                    type="number"
                    class="form-control"
                    wire:model="cookTime"
                    min="0"
                >
            </div>

            <div class="form-group col-md-3">
                <label>Servings</label>
                <input
                    type="number"
                    class="form-control"
                    wire:model="servings"
                    min="1"
                >
            </div>

            <div class="form-group col-md-3">
                <label>Difficulty</label>

                <select
                    class="form-control"
                    wire:model="difficulty"
                >
                    <option value="">Select</option>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>

            <hr>

            {{-- Recipe Categories --}}
            <h4>Recipe Categories</h4>

            <p class="text-muted">
                Select all categories that apply to your recipe.
            </p>

            <div class="d-flex flex-wrap mb-3">

                @foreach ($this->categories as $category)
                    <label class="mr-4 mb-2">
                        <input
                            type="checkbox"
                            wire:model="selectedCategories"
                            value="{{ $category->id }}"
                        >
                        {{ $category->name }}
                    </label>
                @endforeach

            </div>

        </div>

        <hr>

        {{-- Ingredients --}}
        <h4>Ingredients</h4>

        @foreach ($ingredients as $index => $ingredient)

            <div
                class="form-row align-items-center mb-2"
                wire:key="ingredient-{{ $index }}"
            >

                <div class="col-md-2">
                    <input
                        type="text"
                        class="form-control"
                        wire:model="ingredients.{{ $index }}.quantity"
                        placeholder="Qty"
                    >
                </div>

                <div class="col-md-3">
                    <input
                        type="text"
                        class="form-control"
                        wire:model="ingredients.{{ $index }}.measurement"
                        placeholder="Measurement"
                    >
                </div>

                <div class="col-md-5">
                    <input
                        type="text"
                        class="form-control"
                        wire:model="ingredients.{{ $index }}.name"
                        placeholder="Ingredient"
                    >
                </div>

                <div class="col-md-2">
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        wire:click="removeIngredient({{ $index }})"
                    >
                        Remove
                    </button>
                </div>

            </div>

        @endforeach

        <button
            type="button"
            class="btn btn-outline-primary btn-sm mt-2"
            wire:click="addIngredient"
        >
            + Add Ingredient
        </button>

        <hr>

        {{-- Instructions --}}
        <h4>Recipe Instructions</h4>

        @foreach ($steps as $index => $step)

            <div
                class="form-group"
                wire:key="step-{{ $index }}"
            >

                <label>Step {{ $index + 1 }}</label>

                <textarea
                    class="form-control"
                    wire:model="steps.{{ $index }}.instruction"
                    rows="2"
                    placeholder="Describe this step..."
                ></textarea>

                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm mt-2"
                    wire:click="removeStep({{ $index }})"
                >
                    Remove Step
                </button>

            </div>

        @endforeach

        <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            wire:click="addStep"
        >
            + Add Step
        </button>

    </div>

    <hr>

    <button
        type="button"
        class="btn btn-primary"
        wire:click="saveRecipe"
        wire:loading.attr="disabled"
    >
        <span wire:loading.remove wire:target="saveRecipe">
            {{ $recipeId ? 'Update Recipe' : 'Create Recipe' }}
        </span>

        <span wire:loading wire:target="saveRecipe">
            Saving Recipe...
        </span>
    </button>

</div>