<?php

use Livewire\Component;

new class extends Component

{
    public $post;
    public $title;
    public $body;

    //mount is used to get existing data / populate the form
    public function mount() {
        $this->title = $this->post->title;
        $this->body = $this->post->body;
    }

    public function save() {
        $data = $this->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['body'] = strip_tags($data['body']);

        $this->authorize('update', $this->post);
        $this->post->update($data);

        session()->flash('success', 'Post successfully updated.');
        $id = $this->post->id;
        return $this->redirect("/post/$id/edit", navigate: true);
    }

};

?>

<form wire:submit="save" action="/post/{{$post->id}}" method="POST">
    <p><small><strong><a href="/post/{{$post->id}}" wire:navigate>&laquo; Back to post</a></strong></small></p>
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="post-title" class="text-muted mb-1"><small>Title</small></label>
        <input wire:model="title" value="{{old('title', $post->title)}}" name="title" id="post-title" class="form-control form-control-lg form-control-title" type="text" placeholder="" autocomplete="off" />
        @error('title')
        <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="post-body" class="text-muted mb-1"><small>Body Content</small></label>
        <textarea wire:model="body" name="body" id="post-body" class="body-content tall-textarea form-control" type="text">{{old('body', $post->body)}}</textarea>
        @error('body')
        <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
        @enderror
    </div>

    <button class="btn btn-primary">Save Changes</button>
</form>