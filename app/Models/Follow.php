<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
   public function userFollowing(){
    //user doing the following
    return $this->belongsTo(User::class, 'user_id');
   }

   public function userFollowed(){
    //user being followed
    return $this->belongsTo(User::class, 'followeduser');
   }
}
