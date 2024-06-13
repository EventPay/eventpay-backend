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

    /**
     * Register a new user.
     *
     * Registers a new user account and logs them in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam firstname string required The user's first name (max: 40 characters).
     * @bodyParam lastname string required The user's last name (max: 40 characters).
     * @bodyParam username string required|unique:users|max:50 The desired username (unique).
     * @bodyParam email string required|email|unique:users The user's email address (unique).
     * @bodyParam gender string required The user's gender.
     * @bodyParam phone string required The user's phone number.
     * @bodyParam password string required The user's password.
     *
     * @response 201 {
     *     "success": "Registration Successful",
     *     "token": "ApiAuthToken",
     *     "user": {
     *         "id": 1,
     *         "firstname": "John",
     *         "lastname": "Doe",
     *         "username": "johndoe",
     *         "email": "john@example.com",
     *         "phone": "1234567890",
     *         "gender": "MALE",
     *         "created_at": "2023-09-08T12:34:56.000000Z",
     *         "updated_at": "2023-09-08T12:34:56.000000Z"
     *     },
     *     "email_verified": null
     * }
     * @response 400 {
     *     "error": {
     *         "firstname": [
     *             "The firstname field is required."
     *         ],
     *         "lastname": [
     *             "The lastname field is required."
     *         ],
     *         "username": [
     *             "The username field is required."
     *         ],
     *         "email": [
     *             "The email field is required."
     *         ],
     *         "gender": [
     *             "The gender field is required."
     *         ],
     *         "phone": [
     *             "The phone field is required."
     *         ],
     *         "password": [
     *             "The password field is required."
     *         ]
     *     }
     * }
     * @response 401 {
     *     "error": "Something is extremely wrong"
     * }
     * @response 404 {
     *     "error": "An error occurred, please try again later"
     * }
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "firstname" => "required|string|max:40",
            "lastname" => "required|string|max:40",
            "username" => "required|string|max:50|unique:users",
            "email" => "required|email|unique:users",
            "gender" => "required",
            "phone" => "required",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors(),
            ], 400);
        }
        //validation successful

        $validated = $validator->validated();

        $user = new User();
        $user->firstname = $validated['firstname'];
        $user->lastname = $validated['lastname'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->gender = strtoupper($validated['gender']);

        $user->password = Hash::make($validated['password']);

        //   dd($user);

        if ($user->save()) {

            //Attmept login if user saved
            if (!Auth::attempt([
                "email" => $user->email,
                "password" => $request->password,
            ])) {
                return response()->json([
                    "error" => "Something is extremely wrong",
                ], 401);
            }

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

    /**
     * User login.
     *
     * Logs in a user with their email and password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam email string required The user's email address.
     * @bodyParam password string required The user's password.
     *
     * @response {
     *     "success": "Login successful",
     *     "token": "ApiAuthToken",
     *     "user": {
     *         "id": 1,
     *         "firstname": "John",
     *         "lastname": "Doe",
     *         "username": "johndoe",
     *         "email": "john@example.com",
     *         "phone": "1234567890",
     *         "gender": "MALE",
     *         "created_at": "2023-09-08T12:34:56.000000Z",
     *         "updated_at": "2023-09-08T12:34:56.000000Z"
     *     },
     *     "email_verified": null
     * }
     * @response 400 {
     *     "error": {
     *         "email": [
     *             "The email field is required."
     *         ],
     *         "password": [
     *             "The password field is required."
     *         ]
     *     }
     * }
     * @response 401 {
     *     "error": "Credentials do not match."
     * }
     */

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

    /**
     * Check username availability.
     *
     * Checks if a username is available or already taken.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam username string required The desired username.
     *
     * @response 200 {
     *     "success": true,
     *     "message": "Username is available."
     * }
     * @response 400 {
     *     "success": false,
     *     "message": "Username is already taken."
     * }
     */

    public function checkUsernameAvailability(Request $request)
    {
        $username = $request->input('username');

        // Check if the username is already in use
        $user = User::where('username', $username)->first();
        if ($user) {
            return response()->json([
                'success' => false,
                'message' => 'Username is already taken.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Username is available.',
        ], 200);
    }

}
