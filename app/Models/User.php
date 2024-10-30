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
        'username',
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
        $this->events = $this->events;

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
        $tickets = Ticket::where("user_id", $this->id)->with(['eventTicket.event'])->get();
        return $tickets;
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

    public function following()
    {
        return $this->hasMany(Follow::class, 'sending_user', 'id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'target_user', 'id');
    }

    public function getFollowing()
    {
        return $this->following->map(function ($follow) {
            return User::find($follow->target_user);
        });
    }

    public function getFollowers()
    {
        return $this->followers->map(function ($follow) {
            return User::find($follow->sending_user);
        });
    }
}
