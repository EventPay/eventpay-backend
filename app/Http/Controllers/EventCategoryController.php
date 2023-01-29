<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;

class EventCategoryController extends Controller
{

    public function show($slug)
    {

        $category = EventCategory::where("slug", "$slug")->get()->first();

        if (!$category) {
            return response()->json([
                'error' => "Category not found",
            ]);
        }

        //get events in category

        return response()->json(
            [
                'success' => "Events found",
                'events' => $category->events(),
            ]
        );

    }

    public function listCategory()
    {
        return response()->json(
            [
                'categories' => EventCategory::all(),
            ]
        );
    }
}
