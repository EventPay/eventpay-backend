<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;



    function parentComment(){
        return $this->hasOne(Comment::class,"parent_id");
    }


    function replies(){
        return $this->hasMany(Comment::class,"parent_id");
    }

}
