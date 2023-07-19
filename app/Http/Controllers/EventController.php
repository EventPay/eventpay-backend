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

    /**
     * Get event by ID.
     *
     * Retrieves an event based on its unique ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $event_id  The ID of the item to retrieve. Example: 10
     *
     * @response {
     *      "success": "Event Found",
     *      "event": {
     *          "id": 10,
     *          "title": "Sample Event",
     *          "startDate": "2023-07-07",
     *          "endDate": "2023-07-08",
     *          "description": "Sample event description",
     *          "tickets": [
     *              {
     *                  "id": 1,
     *                  "type": "General",
     *                  "price": 10.00
     *              },
     *              {
     *                  "id": 2,
     *                  "type": "VIP",
     *                  "price": 50.00
     *              }
     *          ],
     *          "comments": [
     *              {
     *                  "id": 1,
     *                  "user": "John Doe",
     *                  "message": "Great event!"
     *              },
     *              {
     *                  "id": 2,
     *                  "user": "Jane Smith",
     *                  "message": "Looking forward to it!"
     *              }
     *          ]
     *      }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $event_id  The ID of the event to retrieve.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $event_id)
    {
        $event = Event::find($event_id);

        if (!$event) {
            return response()->json([
                'error' => "Event not found",
            ]);
        }

        $event->tickets = $event->tickets;
        $event->comments = $event->comments();

        return response()->json([
            "success" => "Event Found",
            "event" => $event,
        ]);
    }

    /**
     * Create a new event.
     *
     * Creates a new event with the provided data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam title string required The title of the event.
     * @bodyParam startDate string required The start date of the event (format: Y-m-d).
     * @bodyParam endDate string required The end date of the event (format: Y-m-d).
     * @bodyParam description string required The description of the event.
     * @bodyParam cover_image string required The base64-encoded string of the event's cover image.
     * @bodyParam categories array required The array of category IDs associated with the event.
     * @bodyParam type string required The type of the event.
     * @bodyParam visibility string required The visibility of the event.
     * @bodyParam extra_images array|null The array of base64-encoded strings of additional images for the event.
     * @bodyParam tags string|null The tags associated with the event.
     *
     * @response {
     *     "success": "Event created successfully",
     *      "event" : {
     *              "id" : 3
     *          }
     * }
     * @response 400 {
     *     "error": {
     *         "title": [
     *             "The title field is required."
     *         ],
     *         "startDate": [
     *             "The start date field is required."
     *         ],
     *         ...
     *     }
     * }
     * @response 422 {
     *     "error": "Invalid Cover Image"
     * }
     * @response 500 {
     *     "error": "An error occurred, please contact support"
     * }
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|unique:events",
            "startDate" => "required|date",
            "endDate" => "required|date",
            "description" => "required|string",
            "cover_image" => "required",
            "categories" => "required",
            "type" => "required",
            "visibility" => "required",
            "extra_images" => "nullable",
            "extra_images.*" => "required",
            "tags" => "nullable",
        ], [
            "title.unique" => "The event name already exists",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();
        $extension = getBaseExtension($validated['cover_image']);

        try {
            // Decode the base64 string into an image file
            $cover_image = Image::make(base64_decode($validated['cover_image']));
        } catch (Exception $e) {
            return response()->json([
                "error" => "Invalid Cover Image",
            ], 422);
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

        // Change user to organizer
        $user = User::find(auth()->user()->id);
        $user->organizer = true;
        $user->save();

        $event->cover_image = uploadFileRequest($cover_image, "event", "media", $extension);

        $extra_images = [];
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                try {
                    $img = Image::make(base64_decode($image));
                    $extension = getBaseExtension($image);

                    $url = uploadFileRequest($img, "event_extra", "media", $extension);
                    $extra_images[] = $url;
                } catch (Exception $e) {
                    // Handle exception if needed
                }
            }
        }

        $event->extra_images = json_encode($extra_images);

        if (isset($validated['tags'])) {
            $event->tags = $validated['tags'];
        } else {
            $event->tags = null;
        }

        if ($event->save()) {
            $categories = $validated['categories'];

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
                "event" => $event,
            ]);
        } else {
            return response()->json([
                "error" => "An error occurred, please contact support",
            ], 500);
        }
    }

    public function createSecond(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|unique:events",
            "startDate" => "required|date",
            "endDate" => "required|date",
            "description" => "required|string",
            "cover_image" => "required|image|mimes:jpeg,png,jpg,gif|max:2048", // Add image validation rules
            "categories" => "required",
            "type" => "required",
            "visibility" => "required",
            "extra_images" => "nullable",
            "extra_images.*" => "image|mimes:jpeg,png,jpg,gif|max:2048", // Add image validation rules
            "tags" => "nullable",
        ], [
            "title.unique" => "The event name already exists",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ], 400);
        }

        $validated = $validator->validated();
        $coverImage = $request->file('cover_image');

        if (!$coverImage) {
            return response()->json([
                "error" => "Cover Image is required",
            ], 422);
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

        // Change user to organizer
        $user = User::find(auth()->user()->id);
        $user->organizer = true;
        $user->save();

        // Save cover image to storage
        $coverImagePath = $coverImage->store('event', 'media');
        $event->cover_image = $coverImagePath;

        $extraImages = [];
        if ($request->hasFile('extra_images')) {
            $extraImagesFiles = $request->file('extra_images');
            foreach ($extraImagesFiles as $extraImage) {
                // Save extra images to storage
                $extraImagePath = $extraImage->store('event_extra', 'media');
                $extraImages[] = $extraImagePath;
            }
        }

        $event->extra_images = json_encode($extraImages);

        if (isset($validated['tags'])) {
            $event->tags = $validated['tags'];
        } else {
            $event->tags = null;
        }

        if ($event->save()) {
            $categories = $validated['categories'];

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
                "event" => $event,
            ]);
        } else {
            return response()->json([
                "error" => "An error occurred, please contact support",
            ], 500);
        }
    }


    /**
     * Edit an event.
     *
     * Edits an existing event with the provided data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  The ID of the event to edit.
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam title string required The title of the event.
     * @bodyParam startDate string required The start date of the event (format: Y-m-d).
     * @bodyParam endDate string required The end date of the event (format: Y-m-d).
     * @bodyParam description string required The description of the event.
     * @bodyParam cover_image string required The base64-encoded string of the event's cover image.
     * @bodyParam type string required The type of the event.
     * @bodyParam visibility string required The visibility of the event.
     * @bodyParam categories array required The array of category IDs associated with the event.
     * @bodyParam extra_images array|null The array of base64-encoded strings of additional images for the event.
     * @bodyParam tags string required The tags associated with the event.
     *
     * @response {
     *     "success": "Event edited"
     * }
     * @response 400 {
     *     "error": {
     *         "title": [
     *             "The title field is required."
     *         ],
     *         "startDate": [
     *             "The start date field is required."
     *         ],
     *         ...
     *     }
     * }
     * @response 500 {
     *     "error": "An error occurred, please contact support"
     * }
     */
    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "startDate" => "required",
            "endDate" => "required",
            "description" => "required|string",
            "cover_image" => "required|string",
            "type" => "required",
            "visibility" => "required",
            "categories" => "required",
            "extra_images" => "nullable",
            "extra_images.*" => "required",
            "tags" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ], 400);
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

        // Check if image or base64

        // Decode the base64 string into an image file
        $cover_image = Image::make(base64_decode($validated['cover_image']));

        $event->cover_image = uploadFileRequest($cover_image, "event", "media");

        $extra_images = [];
        if (isset($validated['extra_images'])) {
            foreach ($validated['extra_images'] as $image) {
                $img = Image::make(base64_decode($image));
                $url = uploadFileRequest($img, "event_extra", "media");
                $extra_images[] = $url;
            }
        }

        $event->extra_images = json_encode($extra_images);

        $event->tags = $validated['tags'];

        if ($event->save()) {
            $categories = $validated['categories'];
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
                "error" => "An error occurred, please contact support",
            ], 500);
        }
    }

    /**
     * Search events.
     *
     * Searches for events based on the provided query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @queryParam q string The search query.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "events": [
     *         {
     *             "id": 1,
     *             "title": "Event 1",
     *             "startDate": "2023-07-07",
     *             "endDate": "2023-07-08",
     *             "description": "Sample event 1 description"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Event 2",
     *             "startDate": "2023-07-09",
     *             "endDate": "2023-07-10",
     *             "description": "Sample event 2 description"
     *         }
     *     ]
     * }
     */
    public function search(Request $request)
    {
        $query = $request->input("q", "");

        $events = Event::search($query)->get();

        return response()->json([
            "events" => $events,
        ]);
    }

    /**
     * Delete an event.
     *
     * Deletes an event with the specified ID.
     *
     * @param  int  $id  The ID of the event to delete.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Event deleted"
     * }
     * @response {
     *     "error": "Server error, please contact admin"
     * }
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        if ($event->delete()) {
            return response()->json([
                "success" => "Event deleted",
            ]);
        } else {
            return response()->json([
                "error" => "Server error, please contact admin",
            ]);
        }
    }

    /**
     * Get featured events.
     *
     * Retrieves a list of featured events, sorted randomly.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": true,
     *     "events": [
     *         {
     *             "id": 1,
     *             "title": "Event 1",
     *             "startDate": "2023-07-07",
     *             "endDate": "2023-07-08",
     *             "description": "Sample event 1 description"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Event 2",
     *             "startDate": "2023-07-09",
     *             "endDate": "2023-07-10",
     *             "description": "Sample event 2 description"
     *         }
     *     ]
     * }
     */
    public function featuredEvents()
    {
        $events = Event::featured();

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    /**
     * Get promoted events.
     *
     * Retrieves a list of promoted events, sorted randomly.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": true,
     *     "events": [
     *         {
     *             "id": 1,
     *             "title": "Event 1",
     *             "startDate": "2023-07-07",
     *             "endDate": "2023-07-08",
     *             "description": "Sample event 1 description"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Event 2",
     *             "startDate": "2023-07-09",
     *             "endDate": "2023-07-10",
     *             "description": "Sample event 2 description"
     *         }
     *     ]
     * }
     */
    public function promotedEvents()
    {
        $events = Event::promoted();

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    /**
     * List events.
     *
     * Retrieves a list of recent events sorted by date.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": true,
     *     "events": [
     *         {
     *             "id": 1,
     *             "title": "Event 1",
     *             "startDate": "2023-07-07",
     *             "endDate": "2023-07-08",
     *             "description": "Sample event 1 description",
     *             "category": "Category 1"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Event 2",
     *             "startDate": "2023-07-09",
     *             "endDate": "2023-07-10",
     *             "description": "Sample event 2 description",
     *             "category": "Category 2"
     *         }
     *     ]
     * }
     */
    public function listEvents()
    {
        $eventsReturn = Event::where("status", "!=", "FINISHED")->where("status", "!=", "REVIEWING")->orderByDesc("created_at")->get();
        $events = [];

        foreach ($eventsReturn as $event) {
            $event->category = $event->categoryName();
            array_push($events, $event);
        }

        return response()->json([
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
