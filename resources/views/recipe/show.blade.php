 <x-layout pagetitle="{{ $recipe->post->title }}">

    <div class="container py-5">
        <div class="container--narrow mx-auto">

            @if (auth()->id() === $recipe->post->user_id)

                <div class="mb-4">

                    <a href="{{ route('recipe.edit', $recipe->id) }}"
                    class="btn btn-outline-primary btn-sm">
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

                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            Delete Recipe
                        </button>
                    </form>

                </div>

            @endif

            {{-- Recipe Header --}}
            <h2>{{ $recipe->post->title }}</h2>

            <p class="text-muted">
                By {{ $recipe->post->user->username }}
            </p>

            {{-- Recipe Image --}}
            @if ($recipe->post->image)
                <div class="mb-4">
                    <img
                        src="{{ asset('storage/' . $recipe->post->image) }}"
                        alt="{{ $recipe->post->title }}"
                        class="img-fluid rounded"
                        style="max-height: 450px; width: 100%; object-fit: cover;"
                    >
                </div>
            @endif

            {{-- Description --}}
            <div class="mb-4">
                <p>{{ $recipe->post->body }}</p>
            </div>

            {{-- Preparation Information --}}
            <div class="card mb-4">
                <div class="card-body">

                    <h4 class="mb-3">Recipe Information</h4>

                    <div class="row">

                        <div class="col-6 col-md-3 mb-3">
                            <strong>Prep Time</strong>
                            <div>
                                {{ $recipe->prep_time_minutes !== null
                                    ? $recipe->prep_time_minutes . ' min'
                                    : 'Not specified' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3 mb-3">
                            <strong>Cook Time</strong>
                            <div>
                                {{ $recipe->cook_time_minutes !== null
                                    ? $recipe->cook_time_minutes . ' min'
                                    : 'Not specified' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3 mb-3">
                            <strong>Servings</strong>
                            <div>
                                {{ $recipe->servings ?? 'Not specified' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3 mb-3">
                            <strong>Difficulty</strong>
                            <div>
                                {{ $recipe->difficulty ?? 'Not specified' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {{-- Ingredients --}}
        <div class="card mb-4">
            <div class="card-body">

                <h4 class="mb-3">Ingredients</h4>

                <ul class="list-group list-group-flush">

                    @foreach ($recipe->ingredients as $ingredient)
                        <li class="list-group-item">

                            <strong>
                                {{ $ingredient->quantity }}
                                {{ $ingredient->measurement }}
                            </strong>

                            {{ $ingredient->name }}

                        </li>
                    @endforeach

                </ul>

            </div>
        </div>
        {{-- Instructions --}}
        <div class="card mb-4">
            <div class="card-body">

                <h4 class="mb-3">Instructions</h4>

                @foreach ($recipe->steps as $index => $step)

                    <div class="mb-4">

                        <h5>Step {{ $index + 1 }}</h5>

                        <p>{{ $step->instruction }}</p>

                    </div>

                @endforeach

            </div>
        </div>
        {{-- Categories --}}
        <div class="mb-4">

            <h4>Categories</h4>

            @foreach ($recipe->categories as $category)

                <span class="badge badge-primary mr-2 mb-2">
                    {{ $category->name }}
                </span>

            @endforeach

        </div>

        </div>
    </div>
    
</x-layout>