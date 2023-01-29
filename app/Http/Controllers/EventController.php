<?php

namespace App\Http\Controllers;

use App\Mail\EventSuspensionMail;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventCategoryEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
            "cover_image" => "required | image",
            "categories" => "required",
            "extra_images" => "nullable",
            "extra_images*" => "image",
            "tags" => "nullable",
        ], [
            "title.unique" => "The event name already exists",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        $event = new Event();
        $event->title = $validated['title'];
        $event->startDate = Carbon::parse($validated['startDate']);
        $event->endDate = Carbon::parse($validated['endDate']);
        $event->status = "PENDING";
        $event->organizer = auth()->user()->id;
        $event->description = $validated['description'];
        $event->cover_image = uploadFileRequest($validated['cover_image'], "event", "media");

        //     dd($validated['extra_images']);
        $extra_images = array();
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                $url = uploadFileRequest($image, "event_extra", "media");
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
            "cover_image" => "required | image",
            "categories" => "required",
            "extra_images" => "nullable",
            "extra_images*" => "image",
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
        $event->description = $validated['description'];
        $event->cover_image = uploadFileRequest($validated['cover_image'], "event", "media");

        //     dd($validated['extra_images']);
        $extra_images = array();
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                $url = uploadFileRequest($image, "event_extra", "media");
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

        $events = Event::where("status", "!=", "FINISHED")->orderByDesc("created_at")->get();

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

}
