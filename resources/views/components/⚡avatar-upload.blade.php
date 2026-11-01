<?php

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component
{
    use WithFileUploads;

    public $avatar;

    public function save()
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'avatar' => 'required|image|max:3000',
        ]);

        try {
            // Get the Livewire temporary uploaded file
            $file = $this->avatar;

            $manager = new ImageManager(new Driver());

            $image = $manager->decodePath($file->getPathname());

            // Crop and resize to 300 x 300
            $image->cover(300, 300);

            $filename = uniqid() . '.jpg';

            // Make sure the directory exists
            Storage::disk('public')->makeDirectory('avatars');

            // Save the image
            $image->save(
                Storage::disk('public')->path("avatars/{$filename}")
            );

            // Update the user's avatar
            $user = auth()->user();
            $user->avatar = $filename;
            $user->save();

            session()->flash('success', 'Avatar uploaded!');
            return $this->redirect('/manage-avatar', navigate: true);

        } catch (\Throwable $e) {
            Log::error('Avatar upload failed: ' . $e->getMessage());
            session()->flash('failure', 'Unable to upload image.');
            return $this->redirect('/manage-avatar', navigate: true);
        }
    }
};
?>


<form wire:submit="save">
    <div class="mb-3">
        <input
            wire:model="avatar"
            type="file"
            accept="image/*"
        >

        @error('avatar')
            <p class="alert small alert-danger shadow-sm">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Image Preview --}}
    @if ($avatar)
        <div class="mb-3">
            <p class="small text-muted">Image Preview:</p>

            <img
                src="{{ $avatar->temporaryUrl() }}"
                alt="Avatar preview"
                class="rounded-circle"
                style="width: 150px; height: 150px; object-fit: cover;"
            >
        </div>
    @endif

    <button
        wire:loading.attr="disabled"
        wire:target="save"
        class="btn btn-primary"
    >
        <span wire:loading.remove wire:target="save">
            Save
        </span>

        <span wire:loading wire:target="save">
            Uploading...
        </span>
    </button>
</form>

