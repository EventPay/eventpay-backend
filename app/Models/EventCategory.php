<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;



    function events(){

        
        $entries = EventCategoryEntry::where("category",$this->id)->get();
        $events = array();

        foreach($entries as $entry){
            $event = Event::find($entry->event);
            array_push($events);
        }

        return $events;

    }
}
