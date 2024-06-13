<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return response()->json([
            'url' => Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            // Extracting first and last name from the Google user's name
            $nameParts = explode(' ', $googleUser->name);
            $firstname = $nameParts[0] ?? '';
            $lastname = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

            // Ensure email is not empty
            $email = $googleUser->email ?? 'example@example.com';

            // Creating the user
            $user = User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'admin' => false, // Use default value from schema
                'password' => Hash::make(rand(100000, 999999)),
                'username' => $this->generateUsername($firstname, $lastname), // Generate username
                'profile_image' => null,
                'bio' => null,
                'phone' => '+2348123456789',
                'suspended' => false,
                'gender' => '',
                'phone_verified_at' => null,
                'email_verified_at' => Carbon::now(),
                'organizer' => false,
            ]);

        }

        Auth::login($user);
        //assign login if successfull

        //Verify Email
        $user->email_verified_at = Carbon::now();
        $token = $user->createToken("ApiAuthToken")->plainTextToken;

        // Redirect back to the React app with the token
        return redirect("http://attend.org.ng/login?success=true&token={$token}");

    }

    // Function to generate username (replace with your desired logic)
    public function generateUsername($firstName, $lastName)
    {
        $username = strtolower(substr($firstName, 0, 1) . $lastName);
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username .= $i;
            $i++;
        }
        return $username;
    }
}
