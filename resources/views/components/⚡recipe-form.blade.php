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
    {{-- FeedMe Recipe Banner --}}
    <div class="feedme-recipe-banner mb-4">
        <img
            src="{{ asset('images/feedme-create-banner.png') }}"
            alt="FeedMe - Share Something Delicious"
            class="img-fluid w-100"
        >
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container mx-auto">

        <h2 class="mb-4">
            {{ $recipeId ? 'Edit Recipe' : 'Create a Recipe' }}
        </h2>

        {{-- Recipe Details --}}
        <div class="recipe-form-card">

            <div class="recipe-section-heading">
                <span class="recipe-section-number">01</span>
                <div>
                    <h4>Recipe Details</h4>
                    <p>Every great recipe starts with a name!</p>
                </div>
            </div>

            <div class="form-group">
                <label>Recipe Name</label>

                <input
                    type="text"
                    class="form-control recipe-input"
                    wire:model="name"
                    placeholder="e.g. Grandma's Homemade Lasagna"
                >
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label>Description</label>

                <textarea
                    class="form-control recipe-input"
                    wire:model="description"
                    rows="4"
                    placeholder="Tell us what makes this recipe special..."
                ></textarea>
                @error('description')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <hr>

        {{-- Recipe Photo Upload --}}
        <div class="recipe-form-card">

            <div class="recipe-section-heading">
                <span class="recipe-section-number">02</span>
                <div>
                    <h4>Recipe Photo</h4>
                    <p>Show us what you're cooking!</p>
                </div>
            </div>

            <div class="recipe-upload-area">

                <div class="recipe-upload-icon">
                    <i class="fas fa-camera"></i>
                </div>

                <h5>Got a delicious photo?</h5>

                <p>
                    Choose a picture of your finished dish.
                    We'd love to see it!
                </p>

                <label for="image" class="btn recipe-upload-btn">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Choose Recipe Photo
                </label>

                <input
                    type="file"
                    id="image"
                    class="recipe-file-input"
                    wire:model="image"
                    accept="image/*"
                >

                <small class="text-muted d-block mt-2">
                    JPG, PNG or other image formats
                </small>

                @error('image')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror

                <div wire:loading wire:target="image"
                    class="recipe-upload-loading mt-3">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Uploading your photo...
                </div>

            </div>

            @if ($image)
                {{-- Preview newly uploaded image --}}
                <div class="recipe-preview-container mt-4">
                    <h5>Your New Recipe Photo</h5>

                    <img
                        src="{{ $image->temporaryUrl() }}"
                        class="recipe-image-preview"
                        alt="New recipe image preview"
                    >
                </div>

            @elseif ($existingImage)
                {{-- Display the currently saved image --}}
                <div class="recipe-preview-container mt-4">
                    <h5>Current Recipe Photo</h5>

                    <img
                        src="{{ asset('storage/' . $existingImage) }}"
                        class="recipe-image-preview"
                        alt="Current recipe image"
                    >

                    <p class="text-muted mt-2 mb-0">
                        Choose another photo above to replace this one.
                    </p>
                </div>
            @endif

        </div>


        <hr>

        {{-- Preparation Information --}}
        <div class="recipe-form-card">

            <div class="recipe-section-heading">
                <span class="recipe-section-number">03</span>
                <div>
                    <h4>Preparation Information</h4>
                    <p>A few details to help fellow food lovers.</p>
                </div>
            </div>

            <div class="form-row">

                <div class="form-group col-md-3">
                    <label>Prep Time</label>
                    <div class="recipe-input-group">
                        <input
                            type="number"
                            class="form-control recipe-input"
                            wire:model="prepTime"
                            min="0"
                            placeholder="0"
                        >
                        <span>min</span>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label>Cook Time</label>
                    <div class="recipe-input-group">
                        <input
                            type="number"
                            class="form-control recipe-input"
                            wire:model="cookTime"
                            min="0"
                            placeholder="0"
                        >
                        <span>min</span>
                    </div>
                </div>

                <div class="form-group col-md-3">
                    <label>Servings</label>
                    <input
                        type="number"
                        class="form-control recipe-input"
                        wire:model="servings"
                        min="1"
                        placeholder="e.g. 4"
                    >
                </div>

                <div class="form-group col-md-3">
                    <label>Difficulty</label>

                    <select
                        class="form-control recipe-input"
                        wire:model="difficulty"
                    >
                        <option value="">Select</option>
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>

            </div>

        </div>

        <hr>
         
         {{-- Recipe Categories --}}
        <div class="recipe-form-card">
            <div class="recipe-section-heading">
                <span class="recipe-section-number">04</span>
                <div>
                    <h4>Recipe Categories</h4>
                    <p>Select all categories that apply to your recipe.</p>
                </div>
            </div>

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
        <div class="recipe-form-card">

            <div class="recipe-section-heading">
                <span class="recipe-section-number">05</span>
                <div>
                    <h4>Ingredients</h4>
                    <p>Let's gather everything we need!</p>
                </div>
            </div>

            @foreach ($ingredients as $index => $ingredient)

                <div class="ingredient-row"
                    wire:key="ingredient-{{ $index }}">

                    <div class="ingredient-number">
                        {{ $index + 1 }}
                    </div>

                    <div class="row flex-grow-1 align-items-center">

                        <div class="col-md-2 form-group mb-2">
                            <input
                                type="text"
                                class="form-control recipe-input"
                                wire:model="ingredients.{{ $index }}.quantity"
                                placeholder="Qty"
                            >
                        </div>

                        <div class="col-md-3 form-group mb-2">
                            <input
                                type="text"
                                class="form-control recipe-input"
                                wire:model="ingredients.{{ $index }}.measurement"
                                placeholder="Measurement"
                            >
                        </div>

                        <div class="col-md-5 form-group mb-2">
                            <input
                                type="text"
                                class="form-control recipe-input"
                                wire:model="ingredients.{{ $index }}.name"
                                placeholder="Ingredient name"
                            >
                            @error("ingredients.$index.name")
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-2 form-group mb-2">
                            <button
                                type="button"
                                class="btn recipe-remove-btn btn-sm"
                                wire:click="removeIngredient({{ $index }})"
                            >
                                Remove
                            </button>
                        </div>

                    </div>
                </div>

            @endforeach

            <button
                type="button"
                class="btn recipe-add-btn mt-3"
                wire:click="addIngredient"
            >
                + Add Ingredient
            </button>

        </div>


        <hr>
    
        {{-- Recipe Instructions --}}
        <div class="recipe-form-card">

            <div class="recipe-section-heading">
                <span class="recipe-section-number">06</span>
                <div>
                    <h4>Cooking Instructions</h4>
                    <p>Walk us through your culinary masterpiece!</p>
                </div>
            </div>

            @foreach ($steps as $index => $step)

                <div class="instruction-row"
                    wire:key="step-{{ $index }}">

                    <div class="instruction-number">
                        Step {{ $index + 1 }}
                    </div>

                    <div class="form-group flex-grow-1 mb-2">

                        <textarea
                            class="form-control recipe-input"
                            wire:model="steps.{{ $index }}.instruction"
                            rows="3"
                            placeholder="What happens next? Describe this step..."
                        ></textarea>

                        @error("steps.$index.instruction")
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                    </div>

                    <button
                        type="button"
                        class="btn recipe-remove-btn btn-sm"
                        wire:click="removeStep({{ $index }})"
                    >
                        Remove Step
                    </button>

                </div>

            @endforeach

            <button
                type="button"
                class="btn recipe-add-btn mt-3"
                wire:click="addStep"
            >
                + Add Another Step
            </button>

        </div>


    </div>

    <hr>
   
    <button
        type="button"
        class="btn recipe-submit-btn"
        wire:click="saveRecipe"
        wire:loading.attr="disabled"
    >
        <span wire:loading.remove wire:target="saveRecipe">
            <i class="fas fa-utensils mr-2"></i>
            {{ $recipeId ? 'Update Recipe' : 'Share My Recipe!' }}
        </span>

        <span wire:loading wire:target="saveRecipe">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Saving Recipe...
        </span>
    </button>


</div>