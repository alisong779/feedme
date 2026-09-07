<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;



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
            event(new ExampleEvent(['username' => auth()->user()->username, 'action' => 'logged in']));
            return redirect('/')->with('success', 'You have successfully logged in.');
        }else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }

    public function logout() {
        event(new ExampleEvent(['username' => auth()->user()->username, 'action' => 'logged out']));
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

     public function homepageFeed(User $user) {
        $this->getSharedData($user);
          
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feed_posts()->with('user')->latest()->paginate(3)]);
        } else {
            return view('homepage');
        }
    }

    private function getSharedData($user) {
        $checkFollowing = 0;

        if (auth()->check()) {
            $checkFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();
        }
       
        View::share('sharedData', [
            'checkFollowing' => $checkFollowing, 
            'avatar' => $user->avatar, 
            'username' => $user->username, 
            'postCount' => $user->posts()->count(),
            'followerCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count()
            ]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get(),  'pagetitle' => $user->username . "'s Profile"]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);
        //return $user->followers()->latest()->get();  //use to view json output
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->following()->latest()->get()]);
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

        $user = auth()->user();
        $user->avatar = $filename;
        $user->save();
        return back()->with('success', 'Success. Avatar updated!');
        
        /*return response()->json([
            'success' => true,
            'message' => 'Image uploaded and resized successfully.',
            'path' => Storage::url("avatars/{$filename}"),
        ], 200);*/

    } catch (\Exception $e) {
        /*return response()->json([
            'success' => false,
            'message' => 'Image upload failed.',
            'error' => $e->getMessage(),
        ], 500);*/
       return back()->with('failure', 'Unable to upload image.');  
    }
}
}