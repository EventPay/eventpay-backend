<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "firstname" => "required|string|max:40",
            "lastname" => "required|string|max:40",
            "email" => "required|email|unique:users",
            "gender" => "required",
            "phone" => "required",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ]);
        }
        //validation successful

        $validated = $validator->validated();

        $user = new User();
        $user->firstname = $validated['firstname'];
        $user->lastname = $validated['lastname'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->gender = strtoupper($validated['gender']);

        $user->password = Hash::make($validated['password']);

        //   dd($user);

        if ($user->save()) {
            return response()->json([
                "success" => "Registration Successful",
                "token" => $user->createToken("ApiAuthToken")->plainTextToken,
                "user" => $user->getData(),
                "email_verified" => $user->email_verified_at,
            ], 201);
        } else {
            return response()->json([
                "error" => "An error occured please try again later",
            ], 404);
        }

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ]);
        }

        $credentials = $request->only("email", "password");

        //validation successful attemt login
        if (!Auth::attempt($credentials)) {
            return response()->json([
                "error" => "Credentials do not match.",
            ]);
        }

        //get particular user

        $user = User::where("email", $credentials['email'])->get()->first();

        //assign login if successfull

        return response()->json([
            "success" => "Login successful",
            "token" => $user->createToken("ApiAuthToken")->plainTextToken,
            "user" => $user->getData(),
            "email_verified" => $user->email_verified_at,
        ]);

    }

}
