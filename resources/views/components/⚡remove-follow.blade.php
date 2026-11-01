<?php
use App\Models\User;
use App\Models\Follow;
use Livewire\Component;

new class extends Component
{
    public $username;

    public function save() {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $user = User::where('username', $this->username)->first();
        Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->delete();

        session()->flash('success', 'User successfully unfollowed.');
        return $this->redirect("/profile/{$this->username}", navigate: true);
    }
};
?>

<form wire:submit="save" class="ml-2 d-inline" action="/remove-follow/{{$sharedData['username']}}" method="POST">
    @csrf          
    <button class="btn btn-danger btn-sm">Stop Following <i class="fas fa-user-times"></i></button>
</form>