<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;


    function eventTicket(){
        return $this->belongsTo(EventTicket::class,"parent_ticket");
    }

    function user(){
        return $this->belongsTo(User::class,"user_id");
    }


}
