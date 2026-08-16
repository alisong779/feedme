<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;



class UserController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed'],
            'name' => 'required'
        ]);
        $user = User::create($data);
        auth()->login($user);
        return redirect('/')->with('success', 'Thank you for creating an account.');
    }

    public function login(Request $request){
        $data = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if(auth()->attempt(['username' => $data['loginusername'], 'password' => $data['loginpassword']])){
            return redirect('/')->with('success', 'You have successfully logged in.');
        }else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }

    public function logout() {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

     public function homepageFeed() {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('homepage');
        }
    }

    public function profile(User $user){
        return view('profile-posts', [
            'username' => $user->username, 
            'posts' => $user->posts()->latest()->get(), 
            'user' => $user,
            'postCount' => $user->posts()->count()]);
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

   public function storeAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|max:3000',
    ]);

    try {
        $file = $request->file('avatar');

        $manager = new ImageManager(new Driver());

        $image = $manager->decodePath($file->getPathname());

        // Maximum width of 300px, maintaining aspect ratio
        //$image->scaleDown(width: 300);
        $image->cover(300, 300);

        $filename = uniqid() . '.jpg';

        $image->save(
            storage_path("app/public/avatars/{$filename}")
        );

        $user->avatar = $filename;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded and resized successfully.',
            'path' => Storage::url("avatars/{$filename}"),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Image upload failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}