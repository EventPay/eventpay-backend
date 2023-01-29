<?php

use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

function uploadFileRequest($file, $name, $folder)
{

    //change imagename to unix time stamp
    $fileName = uniqid("$name" . "_");
    $fileExtension = $file->getClientOriginalExtension();
    $fileNewName = $fileName . "." . $fileExtension;

    //print_r($file);
    //exit();

    Image::make($file->getRealpath())->save(public_path("storage/$folder/$fileNewName"), 40, "jpg");

    //  $file->storeAs("public/$folder", $fileNewName);

    return "$folder/$fileNewName";

}

function getPaystackUrl($event_ticket, User $user)
{
    //make api request to paystack

    $url = "https://api.paystack.co/transaction/initialize";
    $client = new Client();
    try {
        $response = $client->request("POST", $url, [
            'body' => json_encode([
                'email' => $user->email,

                'amount' => $event_ticket->price * 100,

                'callback_url' => route('validate-payment', ['user_id' => $user->id, "event_ticket_id" => "$event_ticket->id"]),

            ]),

            'headers' => [
                "Authorization" => "Bearer sk_test_5c8468e7e4f100926902358aa155823eb9bb6340",

                "Cache-Control" => "no-cache",

            ],
        ]);

        $content = json_decode($response->getBody());
        return $content->data->authorization_url;
    } catch (Exception $e) {
        echo $e->getMessage();
        return null;
    }
}

function verifyPaystackPayment($trxref)
{

    $url = "https://api.paystack.co/transaction/verify/$trxref";
    $client = new Client();
    try {
        $response = $client->request("GET", $url, [
            'headers' => [
                "Authorization" => "Bearer sk_test_5c8468e7e4f100926902358aa155823eb9bb6340",
                "Cache-Control" => "no-cache",

            ],
        ]);

        $response = json_decode($response->getBody());

        return $response->data->status;

    } catch (Exception $e) {
        return false;
    }

}


function show_date($time){

    $stamp = Carbon::parse($time);

    return $stamp->format("Y-m-d h:i");

}
