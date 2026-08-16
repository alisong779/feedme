<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

    public function viewPost(Post $post){
        return view('view-post', ['post' => $post]);
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
}
