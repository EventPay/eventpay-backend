<?php

namespace App\Http\Controllers\ui;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Promotions;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    public function dashboard()
    {
        $recentEvents = Event::orderBy("id", "desc")->take(10);
        $users = User::all()->count();
        $tops = Promotions::all()->count();
        $events = Event::where("active", "true")->get()->count();

        $data = [
            "recentEvents" => $recentEvents,
            "events" => $events,
            "users" => $users,
            "tops" => $tops,
        ];
        return view("admin.dashboard")->with($data);
    }

    public function users($param, Request $request)
    {
        $search = null;
        if ($request->input("q")) {
//search request
            $query = $request->input("q");
            $search = $query;
            $users = User::where("id", $query)->orWhere("email", $query)->orWhere("firstname", "LIKE", "%{$query}%")->orWhere("lastname", "LIKE", "%{$query}%")->get();
        } else {
            if ($param == "unverified") {

                $users = User::orderBy("id", "desc")->where("email_verified_at", null)->get();
            } else if ($param == "recent") {
                $users = User::orderBy("id", "desc")->where("email_verified_at", "!=", null)->get();

            } elseif ($param == "suspended") {
                $users = User::where("suspended", "true")->get();
            } else {
                $users = User::all();
            }
        }

        $data = [
            "search" => $search,
            "users" => $users,
        ];
        return view("admin.users")->with($data);
    }

    public function events($param, Request $request)
    {
        $search = null;
        if ($request->input("q")) {
//search request
            $query = $request->input("q");
            $search = $query;
            $events = Event::where("title", "LIKE", "%{$query}%")->orWhere("description", "LIKE", "%{$query}%")->get();
        } else {
            if ($param == "pending") {

                $events = Event::orderBy("id", "desc")->where("active", false)->get();
            } else if ($param == "live") {
                $events = Event::orderBy("id", "desc")->where("status", "LIVE")->get();

            } elseif ($param == "recent") {
                $events = Event::orderBy("id", "desc")->get();
            } else {
                $events = Event::all();
            }
        }

        $data = [
            "events" => $events,
            "search" => $search,
        ];

        return view("admin.events")->with($data);
    }

    public function editEvent($id)
    {
        $event = Event::findOrFail($id);
        return view("admin.edit_event")->with("event", $event);
    }
    public function complaints()
    {
        return view("admin.complaints");
    }

    public function support()
    {
        return view("admin.support");
    }
}
