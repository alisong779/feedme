<x-profile :sharedData="$sharedData">
  <div class="list-group">
        @foreach($following as $follow)
        <a href="/profile/{{ $follow->userFollowed->username }}" class="list-group-item list-group-item-action">
        <img class="avatar-small" src="{{ $follow->userFollowed->avatar ? asset('storage/avatars/' . $follow->userFollowed->avatar ) : asset('images/default-avatar.jpg') }}" alt="{{ $follow->userFollowed->avatar }}'s avatar" />
        {{ $follow->userFollowed->username }}
        </a>
        @endforeach
      </div>
</x-profile>