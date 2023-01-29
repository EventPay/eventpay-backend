<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;

class ForgotPasswordController extends Controller
{

    public function sendCode(Request $request)
    {

        $validation = Validator::make($request->all(), [
            "email" => "string|required",
        ]);

        if ($validation->fails()) {
            return response()->json([
                "error" => $validation->errors(),
            ]);
        }

        //validation success

        $user = User::where("email", $request->email)->get()->first();

        if (!$user) {
            return response()->json([
                "error" => "We could not find an account with that email",
            ]);
        }

        //user account found
        $code = rand(11111, 99999);

        //checks if there is already a password reset entry and wipe

        $previous_codes = PasswordReset::where("email", $user->email)->delete();

        $password_reset = new PasswordReset();
        $password_reset->token = $code;
        $password_reset->email = $user->email;
        $password_reset->save();

        //sends email to user

        try {
            Mail::to($user)->send(new ForgotPasswordMail($user, $code));

            //mail was sent successfully
            return response()->json([
                "success" => "Code sent",
            ]);
        } catch (Exception $e) {
            //error sending mail
            return response()->json([
                "error" => "Error sending mail : " . $e->getMessage(),
            ]);
        }

    }

    public function checkCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "code" => "numeric|required",
            "email" => "required"
        ]);

        //checks if code is valid
        if ($validator->fails()) {
            return response()->json([
                "error" => "Code is required",
            ], 400);
        }

        //check if code exists
        $password_reset = PasswordReset::where("email", $request->email)->get()->first();
        if (!$password_reset) {
            return response()->json([
                "error" => "Code not found",
            ], 400);
        }

        //checks if code is correct
        if ($request->code != $password_reset->token) {
            return response()->json([
                "error" => "Incorrect code",
            ], 401);
        }

        //checks if code is expired

        if (Carbon::now()->gt(Carbon::parse($password_reset->created_at)->addHours(1))) {
            //code expired
            return response()->json([
                "error" => "Code expired, please request another one",
            ], 403);
        }

        //creates an auth token to verify password change on next screen
        $auth_token = Random::generate(14);
        $password_reset->auth_token = $auth_token;
        $password_reset->save();

        return response()->json([
            "success" => "Code correct",
            "auth_token" => $password_reset->auth_token,
        ]);

    }

    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "auth_token" => "string|required",
            "new_password" => "required|string|min:8",

        ]);

        //checks if code is valid
        if ($validator->fails()) {
            return response()->json([
                "error" => "Code is required",
            ], 400);
        }

        $password_reset = PasswordReset::where("auth_token", $request->auth_token)->get()->first();

        if (!$password_reset) {
            return response()->json([
                "errpr" => "Auth token not found",

            ], 403);
        }

        //checks if the auth_token is the same

        if ($request->auth_token != $password_reset->auth_token) {
            return response()->json([
                "error" => "Auth code incorrect",
            ]);
        }

        //code is correct and user is found

        $user = User::where("email",$password_reset->email)->get()->first();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            "success" => "Password changed successfully",
        ]);

    }
}
