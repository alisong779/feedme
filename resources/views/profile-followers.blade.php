<x-profile :sharedData="$sharedData" pagetitle="{{$sharedData['username']}}'s' Followers">
  <div class="list-group">
        @foreach($followers as $follow)
        <a href="/profile/{{$follow->userFollowing->username}}" class="list-group-item list-group-item-action">
          <img class="avatar-small" src="{{ $follow->userFollowing->avatar ? asset('storage/avatars/' . $follow->userFollowing->avatar ) : asset('images/default-avatar.jpg') }}" alt="{{ $follow->userFollowing->username }}'s avatar" /> 
          {{ $follow->userFollowing->username }}
        </a>
        @endforeach
      </div>
</x-profile>