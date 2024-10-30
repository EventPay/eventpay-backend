<?php

namespace App\Http\Controllers\ui;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\Promotions;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPageController extends Controller
{
    public function dashboard()
    {
        $recentEvents = Event::orderBy("id", "desc")->take(10);
        $users = User::all()->count();
        $tops = Promotions::all()->count();
        $events = Event::get()->count();
        $ticketsPurchased = Ticket::count();
        $revenue = Ticket::sum("amount_paid");

        $data = [
            "recentEvents" => $recentEvents,
            "events" => $events,
            "users" => $users,
            "tops" => $tops,
            "ticketsPurchased" => $ticketsPurchased,
            "revenue" => $revenue,
        ];
        return view("admin.dashboard")->with($data);
    }

    public function login()
    {
        return view("admin.login");
    }


    public function tickets($id)
    {
        $tickets = Ticket::select('user_id', 'users.email', DB::raw('count(*) as total_tickets'), DB::raw('sum(amount_paid) as total_amount_paid'))
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->where('parent_ticket', $id)
            ->groupBy('user_id', 'users.email')
            ->paginate(30);

        return view('admin.tickets')->with("tickets", $tickets);
    }


    public function broadcast()
    {
        return view("admin.broadcast");
    }

    public function eventDetails($id)
    {
        $event = Event::findOrFail($id);
        $eventTickets = EventTicket::where('event_id', $event->id)->get();
        $purchases = $event->attendees()->paginate(10);

        return view('admin.event_details', compact('event', 'eventTickets', 'purchases'));
    }
    public function users($param, Request $request)
    {
        $search = null;
        $perPage = 10; // Adjust the number of users per page as needed

        if ($request->input("q")) {
            // Search request
            $query = $request->input("q");
            $search = $query;
            $users = User::where("id", $query)
                ->orWhere("email", $query)
                ->orWhere("firstname", "LIKE", "%{$query}%")
                ->orWhere("lastname", "LIKE", "%{$query}%")
                ->paginate($perPage);
        } else {
            if ($param == "unverified") {
                $users = User::orderBy("id", "desc")->whereNull("email_verified_at")->paginate($perPage);
            } elseif ($param == "recent") {
                $users = User::orderBy("id", "desc")->paginate($perPage);
            } elseif ($param == "suspended") {
                $users = User::where("suspended", true)->paginate($perPage);
            } else {
                $users = User::paginate($perPage);
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
        $perPage = 10; // Number of events per page, you can adjust this as needed

        if ($request->input("q")) {
            // Search request
            $query = $request->input("q");
            $search = $query;
            $events = Event::where("title", "LIKE", "%{$query}%")->orWhere("description", "LIKE", "%{$query}%")->paginate($perPage);
        } else {
            if ($param == "pending") {
                $events = Event::orderBy("id", "desc")->where("active", false)->paginate($perPage);
            } elseif ($param == "live") {
                $events = Event::orderBy("id", "desc")->where("status", "LIVE")->paginate($perPage);
            } elseif ($param == "recent") {
                $events = Event::orderBy("id", "desc")->paginate($perPage);
            } else {
                $events = Event::paginate($perPage);
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

    public function logout()
    {
        Auth::logout();

        return redirect()->route('admin_login');
    }
}
