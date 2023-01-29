<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use HasFactory;
    use Searchable;

    public $asYouType = true;

    public $searchable = [
        'title',
        'description',
        'tags',
    ];

    public function tickets()
    {
        return $this->hasMany(EventTicket::class, "event_id");
    }

    public function comments()
    {
        $mainComments = Comment::where("event_id", $this->id)->where("parent_id", null)->get();
        $comments = array();
        foreach ($mainComments as $comment) {
            $comment->replies = $comment->replies;
            array_push($comments, $comment);
        }

        return $comments;
    }


    //event promotions
    public static function promoted()
    {
        // returns all promoted events
        $promotions = Promotions::where("type", "promoted")::where("active", "1")->get()->shuffle();
        $promoted = array();

        foreach ($promotions as $promotion) {
            array_push($promoted, $promotion->event);
        }

        return $promoted;

    }

    public static function featured()
    {
        // returns all featured events
        $promotions = Promotions::where("type", "featured")::where("active", "1")->get()->shuffle();
        $featured = array();

        foreach ($promotions as $promotion) {
            array_push($featured, $promotion->event);
        }

        return $featured;

    }
}