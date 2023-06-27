<?php

namespace App\Http\Controllers;

use App\Mail\EventSuspensionMail;
use App\Mail\WithdrawalApproveMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventCategoryEntry;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class EventController extends Controller
{

    public function show(Request $request, $event_id)
    {

        $event = Event::find($event_id);

        if (!$event) {
            return response()->json([
                'error' => "Event not found",
            ]);
        }
        //event found return $details

        $event->tickets = $event->tickets;
        $event->comments = $event->comments();
        return response()->json([
            "success" => "Event Found",
            "event" => $event,
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|unique:events",
            "startDate" => "required",
            "endDate" => "required",
            "description" => "required | string",
            "cover_image" => "required",
            "categories" => "required",
            "type" => "required",
            "visibility" => "required",
            "extra_images" => "nullable",
            "extra_images*" => "string",
            "tags" => "nullable",
        ], [
            "title.unique" => "The event name already exists",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ],400);
        }

        $validated = $validator->validated();

        $extension = getBaseExtension($validated['cover_image']);

        try {
            // Decode the base64 string into an image file
            $cover_image = Image::make(base64_decode($validated['cover_image']));
        } catch (Exception $e) {
            return response()->json([
                "error" => "Invalid Cover Image",
            ]);
        }

        $event = new Event();
        $event->title = $validated['title'];
        $event->startDate = Carbon::parse($validated['startDate']);
        $event->endDate = Carbon::parse($validated['endDate']);
        $event->status = "PENDING";
        $event->visibility = strtoupper($validated['visibility']);
        $event->type = strtoupper($validated['type']);
        $event->organizer = auth()->user()->id;
        $event->description = $validated['description'];
        $event->active = true;

        //change user to organizer
        $user = User::find(auth()->user()->id);
        $user->organizer = true;
        $user->save();

        $event->cover_image = uploadFileRequest($cover_image, "event", "media", $extension);

        //     dd($validated['extra_images']);
        $extra_images = array();
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                try{
                    $img = Image::make(base64_decode($image));
                    $extension = getBaseExtension($image);
    
                    $url = uploadFileRequest($img, "event_extra", "media", $extension);
                    array_push($extra_images, $url);
                }
                catch(Exception $e){

                }
 
            }

        }

        $event->extra_images = json_encode($extra_images);
        //extra images

        if(isset($validated['tags'])){
            $event->tags = $validated['tags'];
        }
        else{
            $event->tags = null;
        }

        if ($event->save()) {
            //parse categories after event save

                $categories =$validated['categories'];

                foreach ($categories as $category) {
                    $cat = EventCategory::find($category);

                    if ($cat) {
                        $entry = new EventCategoryEntry();
                        $entry->event = $event->id;
                        $entry->category = $cat->id;
                        $entry->save();
                    }

    
                }


    

            return response()->json([
                "success" => "Event created successfully",
            ]);
        } else {

            return response()->json([
                "error" => "An error occurred please contact support",
            ]);
        }

    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "startDate" => "required",
            "endDate" => "required",
            "description" => "required | string",
            "cover_image" => "required | string",
            "type" => "required",
            "visibility" => "required",
            "categories" => "required",
            "extra_images" => "nullable",
            "extra_images*" => "string",
            "tags" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        $event = Event::findOrFail($id);
        $event->title = $validated['title'];
        $event->startDate = Carbon::parse($validated['startDate']);
        $event->endDate = Carbon::parse($validated['endDate']);
        $event->status = "PENDING";
        $event->visibility = strtoupper($validated['visibility']);
        $event->type = strtoupper($validated['type']);
        $event->description = $validated['description'];

        //check if image or base64

        // Decode the base64 string into an image file
        $cover_image = Image::make(base64_decode($validated['cover_image']));

        $event->cover_image = uploadFileRequest($cover_image, "event", "media");

        //     dd($validated['extra_images']);
        $extra_images = array();
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                $img = Image::make(base64_decode($image));
                $url = uploadFileRequest($img, "event_extra", "media");
                array_push($extra_images, $url);
            }

        }

        $event->extra_images = json_encode($extra_images);
        //extra images

        $event->tags = $validated['tags'];

        if ($event->save()) {
            //parse categories after event save

            $categories = json_decode($validated['categories']);

            foreach ($categories as $category) {
                $cat = EventCategory::find($category);
                if ($cat) {
                    $entry = new EventCategoryEntry();
                    $entry->event = $event->id;
                    $entry->category = $cat->id;
                    $entry->save();
                }

            }

            return response()->json([
                "success" => "Event edited",

            ]);
        } else {

            return response()->json([
                "error" => "An error occurred please contact support",
            ]);
        }

    }

    public function search(Request $request)
    {
        $query = "";

        if (!$request->input("q")) {
            $query = $request->input("q");
        }

        $events = Event::search($query)->get();

        return response()->json([
            "events" => $events,
        ]);

    }

    public function destroy($id)
    {

        //post request of event

        $event = Event::findOrFail($id);

        if ($event->delete()) {
            return response()->json([
                "success" => "Event deleted",

            ]);
        } else {
            return response()->json([
                "success" => "Server error please contact admin",

            ]);
        }

    }

    public function featuredEvents()
    {

        //algorithm gets featured events and sorts them randomly.

        $events = Event::featured();

        return response([
            'success' => true,
            'events' => $events,
        ]);
    }

    public function promotedEvents()
    {

        //algorithm gets promoted events and sorts them randomly.

        $events = Event::promoted();

        return response([
            'success' => true,
            'events' => $events,
        ]);
    }

    public function listEvents()
    {
        //algorithm gets recent events by date

        $eventsReturn = Event::where("status", "!=", "FINISHED")->where("status", "!=", "REVIEWING")->orderByDesc("created_at")->get();
        $events = array();

        foreach($events as $event){
            $event->category = $event->categoryName;
            array_push($events,$event);
        }
        return response([
            'success' => true,
            'events' => $events,
        ]);
    }

    public function suspend(Request $request, $id)
    {

        // $request->validate($request,[
        //     "reason" => "string"
        // ]);

        $event = Event::findOrFail($id);
        $event->active = false;
        $event->save();

        //send email to user here

        Mail::to(User::find($event->organizer))->send(new EventSuspensionMail($event));

        return back()->with("error", "Event Suspended");

    }

    public function withdraw(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "amount" => "required|numeric|gt:100",
            "event_id" => "required|integer",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $user = User::find(auth()->user()->id);
        $event = Event::find($request->event_id);

        //check if event exists
        if (!$event) {
            return response()->json([
                'error' => "Event does not exist!",
            ], 400);
        }

        //check for previous withdrawal
        $prev = Withdrawal::where("user_id", $user->id)->where("status", "pending")->get();

        //withdrawal already exists
        if ($prev) {
            return response()->json([
                'error' => "Pending withdrawal already exists",
            ], 400);
        }

        //check if amount is available
        if ($request->amount > $event->revenue) {
            return response()->json([
                'error' => "Event does not exist!",
            ], 400);
        }

        //process withdrawal
        $withdrawal = new Withdrawal();
        $withdrawal->user_id = $user->id;
        $withdrawal->status = "pending";
        $withdrawal->amount == $request->amount;
        $withdrawal->save();

        return response()->json([
            'success' => "Withdrawal successful",
        ], 200);

    }

    public function approveWithdrawal($withdrawal)
    {

        $withdrawal = Withdrawal::find($withdrawal);

        //check if withdrawal exists
        if (!$withdrawal || $withdrawal->status == "approved") {
            return response()->json([
                'error' => "Event does not exist!",
            ], 400);
        }

        $withdrawal->status = "approved";
        $withdrawal->save();

        //send email to organizer
        Mail::to($withdrawal->user)->send(new WithdrawalApproveMail($withdrawal));

        return back()->with("success", "Withdrawal approved");

    }

}
