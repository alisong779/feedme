<?php

use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    public $title;
    public $body;

    public function create(){
        if (!auth()->check()){
            abort(403, 'Not Authorized');
        }

        $data = $this->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['body'] = strip_tags($data['body']);
        $data['user_id'] = auth()->id();

        $newPost = Post::create($data);

        session()->flash('success', 'New post successfully created!');
        return $this->redirect("/post/{$newPost->id}", navigate: true);
       
    }
};
?>

<form wire:submit="create" action="/create-post" method="POST">
    @csrf
    <div class="form-group">
        <label for="post-title" class="text-muted mb-1"><small>Title</small></label>
        <input wire:model="title" required name="title" id="post-title" class="form-control form-control-lg form-control-title" type="text" placeholder="" autocomplete="off" />
        @error('title')
        <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="post-body" class="text-muted mb-1"><small>Body Content</small></label>
        <textarea wire:model="body" required name="body" id="post-body" class="body-content tall-textarea form-control" type="text"></textarea>
        @error('body')
        <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
        @enderror
    </div>

    <button class="btn btn-primary">Save New Post</button>
</form>