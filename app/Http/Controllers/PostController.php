<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    //
    public function storeNewPost(Request $request) {
        $data = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['body'] = strip_tags($data['body']);
        $data['user_id'] = auth()->id();

        $newPost = Post::create($data);

        return redirect("/post/{$newPost->id}");
    }

    public function showCreateForm() {
        return view('create-post');
    }

    public function viewPost(Post $post, User $user){
        return view('view-post', 
        ['post' => $post,
        'avatar' => auth()->user()->avatar,
        'username' => auth()->user()->username
       
        ]);
    }

    public function delete(Post $post){
        $post->delete();

        return redirect ('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted');

    }

    public function showEditForm(Post $post) {
        return view('edit-post', ['post' => $post]);
    }

    public function update(Post $post, Request $request) {
        $data = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['body'] = strip_tags($data['body']);

        $post->update($data);

        return back()->with('success', 'Post successfully updated.');
    }

    public function search($term)
{
    // Search posts by title/body
    $posts = Post::search($term)->get();

    // Search users by username
    $userIds = User::where('username', 'like', '%' . $term . '%')
        ->pluck('id');

    // Add posts belonging to matching users
    if ($userIds->count()) {
        $userPosts = Post::whereIn('user_id', $userIds)->get();

        $posts = $posts->merge($userPosts)->unique('id');
    }

    $posts->load('user:id,username,avatar');

    return $posts->values();
}
    
}
