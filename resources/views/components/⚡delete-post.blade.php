<?php

use Livewire\Component;

new class extends Component
{
    public $post;

    public function delete(){
        $this->authorize('delete', $this->post);
        $this->post->delete();
        session()->flash('success', 'Post successfully deleted.');
        return $this->redirect('/profile/' . auth()->user()->username, navigate: true);
    }
};
?>

<form wire:submit="delete" class="delete-post-form d-inline" action="/post/{{$post->id}}" method="POST">
    @csrf
    @method('DELETE')
    <button class="delete-post-button text-danger" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash"></i></button>
</form>