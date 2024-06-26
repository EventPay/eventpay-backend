<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        //test user mark
        $mark = new User();
        $mark->firstname = "Mark";
        $mark->lastname = "Frank";
        $mark->email = "markfrank2nite@gmail.com";
        $mark->phone = "09098575791";
        $mark->gender = "MALE";
        $mark->username = "'markfrank";
        $mark->password = Hash::make("markfrank");
        $mark->save();

        $eventCategory = new EventCategory();
        $eventCategory->name = "Party";
        $eventCategory->slug = "party";
        $eventCategory->description = "A night of fun to enjoy and leave out all your stress";
        $eventCategory->placeholder = "category/party.jpg";
        $eventCategory->save();

        // $eventCategory = new EventCategory();
        // $eventCategory->name = "Conference";
        // $eventCategory->slug = "conference";
        // $eventCategory->placeholder = "category/conference.jpg";
        // $eventCategory->save();

        // $eventCategory = new EventCategory();
        // $eventCategory->name = "Food";
        // $eventCategory->slug = "food";
        // $eventCategory->placeholder = "category/food.jpg";
        // $eventCategory->save();

        // $eventCategory = new EventCategory();
        // $eventCategory->name = "Education";
        // $eventCategory->slug = "education";
        // $eventCategory->placeholder = "category/education.jpg";
        // $eventCategory->save();

    }
}
