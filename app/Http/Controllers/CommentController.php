<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "parent_comment" => "nullable|integer",
            "event_id" => "required|integer",
            "body" => "required|string|max:255",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        //checks if event exists
        if (!Event::find($validated['event_id'])) {
            return response()->json([
                "error" => "Event not found"
            ], 404);
        }


        //create comment

        $comment = new Comment();
        $comment->body = $validated['body'];
        $comment->parent_id = $validated['parent_comment'] ?? null;
        $comment->event_id = $validated['event_id'];
        $comment->user_id = auth()->user()->id;
        $comment->save();

        return response()->json([
            "success" => "Comment added" 
        ],201);

    }
}
