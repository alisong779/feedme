<?php

use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    public string $search = '';

    public function render()
    {
        $posts = collect();

        if (strlen($this->search) >= 2) {
            $posts = Post::search($this->search)->get();
        }

        return $this->view([
            'posts' => $posts,
        ]);
    }
};
?>

<div
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    {{-- Search button --}}
    <a
        href="#"
        class="text-white mr-2 header-search-icon"
        title="Search"
        data-toggle="tooltip"
        data-placement="bottom"
        @click.prevent="open = true; $nextTick(() => $refs.searchInput.focus())"
    >
        <i class="fas fa-search"></i>
    </a>

    {{-- Search overlay --}}
    <div
        class="search-overlay"
        :class="{ 'search-overlay--visible': open }"
    >

        {{-- Top of overlay --}}
        <div class="search-overlay-top shadow-sm">
            <div class="container container--narrow">

                <label
                    for="live-search-field"
                    class="search-overlay-icon"
                >
                    <i class="fas fa-search"></i>
                </label>

                <input
                    x-ref="searchInput"
                    id="live-search-field"
                    type="text"
                    autocomplete="off"
                    class="live-search-field"
                    placeholder="What are you interested in?"
                    wire:model.live.debounce.750ms="search"
                >

                <span
                    class="close-live-search"
                    @click="open = false"
                >
                    <i class="fas fa-times-circle"></i>
                </span>

            </div>
        </div>

        {{-- Results area --}}
        <div class="search-overlay-bottom">
            <div class="container container--narrow py-3">

                <div
                    wire:loading
                    wire:target="search"
                    class="circle-loader circle-loader--visible"
                ></div>

                <div class="live-search-results">

                    @if ($search !== '')

                        @if ($posts->count())

                            <div class="list-group shadow-sm">

                                <div class="list-group-item active">
                                    <strong>Search Results</strong>
                                    ({{ $posts->count() }}
                                    {{ $posts->count() === 1 ? 'item' : 'items' }}
                                    found)
                                </div>

                                @foreach ($posts as $post)

                                    <a
                                        href="/post/{{ $post->id }}"
                                        class="list-group-item list-group-item-action"
                                    >
                                        <img
                                            class="avatar-tiny"
                                            src="{{ $post->user->avatar
                                                ? asset('storage/avatars/' . $post->user->avatar)
                                                : asset('images/default-avatar.jpg') }}"
                                            alt="{{ $post->user->username }}'s avatar"
                                        >

                                        <strong>{{ $post->title }}</strong>

                                        <span class="text-muted small">
                                            by {{ $post->user->username }}
                                            on {{ $post->created_at->format('n/j/Y') }}
                                        </span>
                                    </a>

                                @endforeach

                            </div>

                        @elseif (strlen($search) >= 2)

                            <p class="alert alert-danger text-center shadow-sm">
                                Sorry, we could not find any results for that search.
                            </p>

                        @endif

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>