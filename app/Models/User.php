<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getData(){
        $this->followersCount = $this->followersCount();
        $this->followingCount = $this->followingCount();
        $this->tickets = $this->tickets();

        return $this;
    }
    public function events()
    {
        return $this->hasMany(Event::class, "organizer");
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, "user_id");
    }

    public function tickets()
    {
        $userTickets = array();
        $tickets = Ticket::where("user_id", $this->id);
        foreach ($tickets as $ticket) {
            $ticketDetails = EventTicket::find($ticket->parent_ticket);
            $ticketDetails->user_id = $ticket->user_id;
            $ticketDetails->ticket_code = $ticket->ticket_code;
            $ticketDetails->status = $ticket->status;

            array_push($userTickets, $ticketDetails);
        };
        return $userTickets;
    }


    public function followersCount()
    {
        $count = Follow::where("target_user", $this->id)->count();
        return $count;
    }

    public function followingCount()
    {
        $count = Follow::where("sending_user", $this->id)->count();
        return $count;
    }

    public function emailCode()
    {
        return $this->hasOne(EmailCode::class, "user_id");
    }
}
