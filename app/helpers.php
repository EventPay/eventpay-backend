<?php

use App\Models\EventTicket;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

function uploadFileRequest($file, $name, $folder)
{

    //change imagename to unix time stamp
    $fileName = uniqid("$name" . "_");
    $fileExtension = $file->getClientOriginalExtension();
    $fileNewName = $fileName . "." . $fileExtension;

    //print_r($file);
    //exit();

    Image::make($file->getRealpath())->save(storage_path("app/public/media/$fileNewName"), 40, "jpg");

    //  $file->storeAs("public/$folder", $fileNewName);

    return "$folder/$fileNewName";

}

function getBaseExtension($string)
{
// Decode the base64 string
    $decodedData = base64_decode($string);

// Determine the MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($decodedData);

// Extract the extension from the MIME type
    $extension = File::extension($mimeType);

    return $extension;

}

function getPaystackUrl($event_ticket, User $user, $quantity)
{
    //make api request to paystack

    $url = "https://api.paystack.co/transaction/initialize";
    $client = new Client();

    $amount = $event_ticket->price * $quantity;

//calculate the charge
    $charge = 2;

    $key = env('PAYSTACK_KEY');

    if ($amount >= 2500) {
        $finalAmount = ((($amount + $charge) * (100 / 98.5)) + 100) * 100;
    } else {
        $finalAmount = ((($amount + $charge) * (100 / 98.5))) * 100;
    }

    //caclulate the paystack final amount
    $finalAmount = round($finalAmount);

    try {
        $response = $client->request("POST", $url, [
            'body' => json_encode([
                'email' => $user->email,

                'amount' => $finalAmount,

                'metadata' => [
                    "event_ticket_id" => $event_ticket->id,
                    "user_id" => $user->id,
                    "quantity" => $quantity,
                ],

                'callback_url' => 'https://attend.org.ng/my-profile',

            ]),

            'headers' => [
                "Authorization" => "Bearer " . $key,

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

function verifyPaystackPayment($trxref, $event_ticket_id)
{

    $url = "https://api.paystack.co/transaction/verify/$trxref";
    $client = new Client();

    $event_ticket = EventTicket::find($event_ticket_id);

    if ($event_ticket->event->id == 8) {
        $key = "sk_live_aec4a55276ba56b0f74c1c8adc418ac2bce5c861";
    } elseif ($event_ticket->event->id == 12) {
        $key = "sk_live_0c336745d205a7de42b4a1ca0f2bf9fb12ab7885";

    } elseif ($event_ticket->event->id == 13) {
        $key = "sk_live_195f9fee7fe05500062508600365a23c3881a519";
    } elseif ($event_ticket->event->id == 15) {
        $key = "sk_live_86cacb558fbaaecdb148d23161abf17595f4d40c";
    } else {
        $key = env('PAYSTACK_KEY');
    }

    try {
        $response = $client->request("GET", $url, [
            'headers' => [
                "Authorization" => "Bearer " . $key,
                "Cache-Control" => "no-cache",

            ],
        ]);

        $response = json_decode($response->getBody());

        return $response->data->status;

    } catch (Exception $e) {
        return false;
    }

}

function show_date($time)
{

    $stamp = Carbon::parse($time);

    return $stamp->format("Y-m-d h:i");

}

function formatDateTime($date)
{
    return Carbon::parse($date)->format('F d, Y g:i A');
}

function formatDate($date)
{
    return Carbon::parse($date)->format('F d, Y');
}
/**
 * Formats to carbon's time diff function
 *
 * @param string $string
 * @return string
 */

function diffFormat($date)
{
    return Carbon::parse($date)->diffForHumans();
}
