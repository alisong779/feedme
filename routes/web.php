<?php

use App\Events\ChatMessage;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;


Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

//Admin Routes
Route::get('/admin', function(){
    return 'Welcome to the Admin Page';
})->middleware('can:visitAdminPages');

//User Routes
Route::get('/', [UserController::class, "homepageFeed"])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('mustBeLoggedIn');
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('mustBeLoggedIn');

//Post Routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('mustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'viewPost']);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'update'])->middleware('can:update,post');
Route::get('/search/{term}', [PostController::class, 'search']);

//Profile Routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);

//Follow Routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('mustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware('mustBeLoggedIn');

Route::view('/recipe/create', 'recipe.create')->middleware('auth')->name('recipe.create');
Route::get('/recipe/{recipe}', function (Recipe $recipe) {
    $recipe->load([
        'post.user',
        'ingredients',
        'steps',
        'categories'
    ]);

    return view('recipe.show', compact('recipe'));

})->whereNumber('recipe')->name('recipe.show');


Route::get('/recipe/{recipe}/edit', function (Recipe $recipe) {
    return view('recipe.edit', compact('recipe'));
})
->middleware('auth')
->whereNumber('recipe')
->name('recipe.edit');


Route::delete('/recipe/{recipe}', function (Recipe $recipe) {

    // Only the recipe owner can delete it.
    abort_unless(
        $recipe->post()->where('user_id', auth()->id())->exists(),
        403
    );

    // Remember the image path before deleting the post.
    $imagePath = $recipe->post->image;

    // Deleting the post cascades to its recipe and related records.
    DB::transaction(function () use ($recipe) {
        $recipe->post()->delete();
    });

    // Delete the image from public storage.
    if ($imagePath) {
        Storage::disk('public')->delete($imagePath);
    }

    return redirect('/')
        ->with('success', 'Your recipe was deleted successfully!');

})
->middleware('auth')
->whereNumber('recipe')
->name('recipe.destroy');


require __DIR__.'/settings.php';
