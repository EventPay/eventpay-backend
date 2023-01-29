<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function get(Request $request)
    {

        //check user existence

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                "error" => "User not found",
            ]);
        }
        //user found

        return response()->json([
            "success" => "Retrieval success",
            "user" => $user->getData()
        ]);

    }

    public function uploadProfile(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'profile_image' => "required | image",
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();
        //validation successful

        $user = User::find(auth()->user()->id);

        $user->profile_image = uploadFileRequest($validated['profile_image'], "profile", "profile");
        $user->save();
    }

    public function edit(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required|numeric',
            'gender' => 'required',
        ]);

        $user = User::find(auth()->user()->id);
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->phone = $request->input('phone');
        $user->gender = $request->input('gender');
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'previous_password' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::find(auth()->user()->id);
        if (!Hash::check($request->input('previous_password'), $user->password)) {
            return response()->json([
                'error' => 'Incorrect previous password',
            ], 422);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function events()
    {
        return response()->json([
            "success" => "Request Successful",
            "events" => auth()->user()->events,
        ]);
    }
}
