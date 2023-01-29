<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    use HasFactory;

    public function event()
    {
        $this->belongsTo(Event::class);
    }

    function member(){
        $this->belongsTo(User::class);
    }

}
