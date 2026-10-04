<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*Broadcast::channel('chatchannel', function(){
    if (auth()->check()){
        return true;
    }
    return false;
});*/

Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
    return (int) $user->id === (int) $user1
        || (int) $user->id === (int) $user2;
});