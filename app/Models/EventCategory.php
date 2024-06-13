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

            $event = Event::with('user')->find($entry->event);
            if($event){
                array_push($events,$event);
            }
        }

        return $events;

    }
}
