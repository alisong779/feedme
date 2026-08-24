<x-profile :sharedData="$sharedData">
  <div class="list-group">
        @foreach($posts as $post)
        <a href="/post/{{$post->id}}" class="list-group-item list-group-item-action">
          <img class="avatar-small" src="{{ $sharedData['avatar'] ? asset('storage/avatars/' . $sharedData['avatar']) : asset('images/default-avatar.jpg') }}" alt="{{ $sharedData['username'] }}'s avatar" /> {{$sharedData['username'] }}
          <strong>{{$post->title}}</strong> on {{$post->created_at->format('n/j/Y')}}
        </a>
        @endforeach
      </div>
</x-profile>