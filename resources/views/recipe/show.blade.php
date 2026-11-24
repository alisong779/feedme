 <x-layout pagetitle="{{ $recipe->post->title }}">

    <div class="container py-5">
        <div class="container--narrow mx-auto">

           
            @if (auth()->id() === $recipe->post->user_id)

                <div class="recipe-owner-actions mb-4">

                    <a href="{{ route('recipe.edit', $recipe->id) }}"
                    class="recipe-edit-btn">
                        <i class="fas fa-pen mr-2"></i>
                        Edit Recipe
                    </a>

                    <form
                        action="{{ route('recipe.destroy', $recipe->id) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this recipe? This cannot be undone.');"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="recipe-delete-btn">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Delete Recipe
                        </button>
                    </form>

                </div>

            @endif
            
            {{-- Recipe Hero --}}
            <div class="recipe-detail-hero mb-4">

                @if ($recipe->post->image)
                    <div class="recipe-detail-image">
                        <img
                            src="{{ asset('storage/' . $recipe->post->image) }}"
                            alt="{{ $recipe->post->title }}"
                        >
                    </div>
                @endif

                <div class="recipe-detail-intro">

                    <span class="recipe-detail-eyebrow">
                        FROM THE FEEDME KITCHEN
                    </span>

                    <h1>{{ $recipe->post->title }}</h1>

                    <p class="recipe-detail-author">
                        <i class="fas fa-user-circle mr-1"></i>
                        Shared by <strong>{{ $recipe->post->user->username }}</strong>
                    </p>

                    @if ($recipe->post->body)
                        <p class="recipe-detail-description">
                            {{ $recipe->post->body }}
                        </p>
                    @endif

                </div>

            </div>
         
            {{-- Recipe Information --}}
            <div class="recipe-detail-info mb-4">

                <div class="recipe-detail-section-heading">
                    <span class="recipe-detail-section-number">01</span>
                    <div>
                        <h4>Recipe Information</h4>
                        <p>A few helpful details before we get cooking!</p>
                    </div>
                </div>

                <div class="row">

                    <div class="col-6 col-md-3 mb-3">
                        <div class="recipe-info-card">
                            <i class="fas fa-clock"></i>
                            <h6>Prep Time</h6>
                            <strong>
                                {{ $recipe->prep_time_minutes !== null
                                    ? $recipe->prep_time_minutes . ' min'
                                    : 'Not specified' }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mb-3">
                        <div class="recipe-info-card">
                            <i class="fas fa-fire"></i>
                            <h6>Cook Time</h6>
                            <strong>
                                {{ $recipe->cook_time_minutes !== null
                                    ? $recipe->cook_time_minutes . ' min'
                                    : 'Not specified' }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mb-3">
                        <div class="recipe-info-card">
                            <i class="fas fa-users"></i>
                            <h6>Servings</h6>
                            <strong>
                                {{ $recipe->servings ?? 'Not specified' }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 mb-3">
                        <div class="recipe-info-card">
                            <i class="fas fa-utensils"></i>
                            <h6>Difficulty</h6>
                            <strong>
                                {{ $recipe->difficulty ?? 'Not specified' }}
                            </strong>
                        </div>
                    </div>

                </div>
            </div>
    
            {{-- Ingredients --}}
            <div class="recipe-detail-info mb-4">

                <div class="recipe-detail-section-heading">
                    <span class="recipe-detail-section-number">02</span>
                    <div>
                        <h4>Ingredients</h4>
                        <p>Everything you'll need to make this delicious recipe!</p>
                    </div>
                </div>

                <div class="recipe-ingredients-list">

                    @foreach ($recipe->ingredients as $ingredient)

                        <div class="recipe-ingredient-item">

                            <i class="fas fa-check-circle"></i>

                            <div>
                                @if ($ingredient->quantity || $ingredient->measurement)
                                    <strong>
                                        {{ $ingredient->quantity }}
                                        {{ $ingredient->measurement }}
                                    </strong>
                                @endif

                                <span>{{ $ingredient->name }}</span>
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        
            {{-- Cooking Instructions --}}
            <div class="recipe-detail-info mb-4">

                <div class="recipe-detail-section-heading">
                    <span class="recipe-detail-section-number">03</span>
                    <div>
                        <h4>Cooking Instructions</h4>
                        <p>Follow along and create something delicious!</p>
                    </div>
                </div>

                <div class="recipe-instructions-list">

                    @foreach ($recipe->steps as $index => $step)

                        <div class="recipe-instruction-item">

                            <div class="recipe-instruction-number">
                                {{ $index + 1 }}
                            </div>

                            <div class="recipe-instruction-content">
                                <h5>Step {{ $index + 1 }}</h5>
                                <p>{{ $step->instruction }}</p>
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

            {{-- Categories --}}
            <div class="recipe-detail-info mb-4">

                <div class="recipe-detail-section-heading">
                    <span class="recipe-detail-section-number">04</span>
                    <div>
                        <h4>Recipe Categories</h4>
                        <p>Find your next kitchen inspiration!</p>
                    </div>
                </div>

                <div class="recipe-category-list">

                    @foreach ($recipe->categories as $category)

                        <span class="recipe-category-badge">
                            <i class="fas fa-utensils"></i>
                            {{ $category->name }}
                        </span>

                    @endforeach

                </div>

            </div>

            {{-- FeedMe Kitchen Sign-off --}}
            <div class="recipe-mascot-footer">
                <div class="recipe-mascot-image">
                    <img
                        src="{{ asset('images/winking_mascot_spaghetti.png') }}"
                        alt="FeedMe burger chef presenting a plate of spaghetti"
                    >
                </div>

                <div class="recipe-mascot-message">
                    <span>FROM OUR KITCHEN TO YOURS</span>
                    <h3>Made with a little FeedMe love!</h3>
                    <p>
                        Happy cooking, and thanks for sharing your recipe
                        with our kitchen!
                    </p>
                </div>
            </div>

        </div>
    </div>
    
</x-layout>