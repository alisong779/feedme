 <a href="{{ $post->recipe ? route('recipe.show', $post->recipe->id) : url('/post/' . $post->id) }}"
   wire:navigate
   class="list-group-item list-group-item-action">

    {{-- User Avatar --}}
    <img
        class="avatar-small"
        src="{{ $post->user->avatar
            ? asset('storage/avatars/' . $post->user->avatar)
            : asset('images/default-avatar.jpg') }}"
        alt="{{ $post->user->username }}'s avatar"
    />

    {{-- Post Title --}}
    <strong>{{ $post->title }}</strong>

    <span class="text-muted small">
        @if (!isset($hide_author))
            by {{ $post->user->username }}
        @endif

        on {{ $post->created_at->format('n/j/Y') }}
    </span>

    {{-- Post Image --}}
    @if ($post->image)
        <div class="mt-3">
            <img
                src="{{ asset('storage/' . $post->image) }}"
                alt="{{ $post->title }}"
                class="img-fluid rounded"
                style="max-height: 300px; width: 100%; object-fit: cover;"
            >
        </div>
    @endif

    {{-- Recipe Indicator --}}
    @if ($post->recipe)
        <div class="mt-2">
            <span class="badge badge-success">Recipe</span>
        </div>
    @endif

</a>