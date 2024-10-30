<?php

use App\Http\Controllers\BroadcastEmailQueueController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ReminderBroadCastQueueController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ui\AdminPageController;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return redirect('https://attend.org.ng');
});
// Route::get("/artisan-migrate", function () {
//     Artisan::call("migrate:fresh");
//     Artisan::call("db:seed");
//     // symlink("/home/attendor/public_html/api/storage/app/public", "/home/attendor/public_html/api/public/storage");
//     symlink("/home/u180547988/domains/attend.org.ng/public_html/api/storage/app/public", "/home/u180547988/domains/attend.org.ng/public_html/api/public/storage");
// });

Route::get("artisan-cache", function () {
    Artisan::call("cache:clear");
    Artisan::call("config:clear");
});

Route::get("/migrate",function(){
    Artisan::call("migrate");
});

Route::get("test/{id}",function($id){

    return Event::with("revenue")->find($id);

});


Route::get("/event-cron",[EventController::class,"expire"])->name("check_events");
Route::get("/reminder-cron",[EventController::class,"sendReminders"])->name("remind_events");
Route::get("/process-broadcast",[BroadcastEmailQueueController::class,"sendQueuedEmails"])->name("process_broadcast");
Route::get("/process-reminders",[ReminderBroadCastQueueController::class,"sendQueuedEmails"])->name("process_reminders");
//admin panel

Route::get("/admin/login",[AdminPageController::class,"login"])->name("admin_login");

Route::post("/admin/login",[LoginController::class,"login"])->name("admin.login");


Route::get("test",function(){

});

Route::group(["prefix" => "admin", "as" => "admin.","middleware" => ['admin']], function () {

    Route::get("/",function(){
        if(auth()->check()){
            return redirect()->route("admin.dashboard");
        }
        else{
            return redirect()->route("admin_login");
        }
    });

    Route::get("/dashboard", [AdminPageController::class, "dashboard"])->name("dashboard");
    Route::get("/users/{param}", [AdminPageController::class, "users"])->name("users");
    Route::get("/events/{param}", [AdminPageController::class, "events"])->name("events");
    Route::get("/events/tickets/{id}", [AdminPageController::class, "tickets"])->name("tickets");
    Route::get("/complaints", [AdminPageController::class, "complaints"])->name("complaints");
    Route::get("/broadcast", [AdminPageController::class, "broadcast"])->name("broadcast");
    Route::get("/support", [AdminPageController::class, "support"])->name("support");
    Route::get("/logout", [AdminPageController::class, "logout"])->name("logout");


    Route::post("/purchase-ticket",[TicketController::class,"adminPurchase"])->name("ticket.purchase");
    Route::post("/send-broadcast",[MarketingController::class,"sendBroadcast"])->name("sendBroadcast");



    Route::get("/event-details/{id}",[AdminPageController::class,"eventDetails"])->name("event-details");
    Route::post("/event/delete/{id}",[EventController::class,"destroyAdmin"])->name("delete-event");

    //extra
    Route::get("/edit-event/{id}", [AdminPageController::class, "editEvent"])->name("edit-event");
    Route::get("/suspend-event/{id}", [EventController::class, "suspend"])->name("suspend-event");
});

Route::get("/resend-silent",[TicketController::class,"sendReminder"])->name("resend_silent");


Route::get('/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

//Paystack Webhook
Route::post("/paystack/webhook",[TicketController::class,"webhookVerify"])->name("paystack.webhook");


Route::get("/job-setup",function(){

    Artisan::call("queue:table");
    Artisan::call("migrate");

});
