<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{

    public function follow(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "target_user" => "id",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        //validation success
        $validated = $validator->validated();

        
        //checking if user exists
        $user = User::find($validated['target_user']);

        if (!$user) {
            return response()->json([
                'error' => "User not found",
            ], 404);
        }

        $follow = new Follow();
        $follow->sending_user = Auth::user()->id;
        $follow->target_user = $validated['target_user'];
        $follow->save();

        return response()->json([
            "success" => "Follow success",
        ]);
    }

    public function unFollow(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "target_user" => "id",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        //validation success
        $validated = $validator->validated();

        //checking if user exists
        $user = User::find($validated['target_user']);

        if (!$user) {
            return response()->json([
                'error' => "User not found",
            ], 404);
        }

        $follow = Follow::where("target_user", $validated['target_user'])->get()->first();

        //checking if following exists
        if (!$follow) {
            return response()->json([
                'error' => "Following not found",
            ], 404);
        }

        $follow->delete();

        return response()->json([
            "success" => "unfollow success",
        ]);
    }
}
