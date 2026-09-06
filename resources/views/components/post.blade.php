<a href="/post/{{$post->id}}" class="list-group-item list-group-item-action">
        <img class="avatar-small" src="{{ $post->user->avatar ? asset('storage/avatars/' . $post->user->avatar) : asset('images/default-avatar.jpg') }}" alt="{{ $post->user->username }}'s avatar" />          
        <strong>{{$post->title}}</strong> 
        <span class="text-muted small">
            @if (!isset($hide_author))
            by {{$post->user->username}} 
            @endif
            on {{$post->created_at->format('n/j/Y')}}
        </span>
        </a>