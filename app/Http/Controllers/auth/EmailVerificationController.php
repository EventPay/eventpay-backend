<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\EmailCode;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{

    public function sendVerificationCode()
    {

        //get current user
        $user = Auth::user();

        //send email

        $code = rand(11111, 99999);

        //checks if there is already an expired or unused code

        $emailCode = null;

        if ($user->emailCode) {
            $emailCode = EmailCode::find($user->emailCode->id);
        } else {
            $emailCode = new EmailCode();
            $emailCode->user_id = $user->id;
        }

        $emailCode->code = $code;
        $emailCode->save();

        //send code to email

        try {
            Mail::to($user)->send(new EmailVerificationMail($user, $code));

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

    public function verifyCode(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "code" => "numeric|required",
        ]);

        //checks if code is valid
        if ($validator->fails()) {
            return response()->json([
                "error" => "Code is required",
            ], 400);
        }

        $user = Auth::user();

        //checks if the code exists
        $emailCode = $user->emailCode;
        if (!$emailCode) {
            return response()->json([
                "error" => "Code not found",
            ], 400);
        }

        //checks if code is correct
        if ($request->code != $emailCode->code) {
            return response()->json([
                "error" => "Incorrect code",
            ], 401);
        }

        //checks if code is expired
        if (Carbon::now()->gt(Carbon::parse($emailCode->updated_at)->addHours(1))) {
            //code expired
            return response()->json([
                "error" => "Code expired, please request another one",
            ], 403);
        }

        //code is valid and checked
        $user = User::find($user->id);

        $user->email_verified_at = Carbon::now()->toString();
        $user->save();

        //delete email code
        $emailCode = EmailCode::find($user->emailCode->id);
        $emailCode->delete();

        return response()->json([
            "success" => "E-mail verification successful",
        ]);

    }
}
