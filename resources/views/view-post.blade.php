<x-layout :pagetitle="$post->title">
  <div class="container py-md-5 container--narrow">
      <div class="d-flex justify-content-between">
        <h2>{{$post->title}}</h2>
        @can('update', $post)
        <span class="pt-2">
          <a href="/post/{{$post->id}}/edit" wire:navigate class="text-primary mr-2" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit"></i></a>
          <livewire:delete-post :post="$post"/>
        </span>
        @endcan
      </div>

      <p class="text-muted small mb-4">
        <a href="/profile/{{$post->user->username}}" wire:navigate><img class="avatar-small" src="{{ $avatar ? asset('storage/avatars/' . $avatar) : asset('images/default-avatar.jpg') }}" alt="{{ $username }}'s avatar" /></a>
        Posted by <a href="/profile/{{$post->user->username}}" wire:navigate>{{$post->user->username}}</a> on {{$post->created_at->format('n/j/Y')}}
      </p>

      <div class="body-content">
        {!! $post->body !!}
      </div>
    </div>
</x-layout>