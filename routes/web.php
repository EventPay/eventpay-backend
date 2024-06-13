<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ui\AdminPageController;
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


Route::get("/event-cron",[EventController::class,"expire"])->name("check_events");
//admin panel

Route::get("/admin/login",[AdminPageController::class,"login"])->name("admin_login");

Route::post("/admin/login",[LoginController::class,"login"])->name("admin.login");


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
    Route::get("/complaints", [AdminPageController::class, "complaints"])->name("complaints");
    Route::get("/support", [AdminPageController::class, "support"])->name("support");
    Route::get("/logout", [AdminPageController::class, "logout"])->name("logout");


    Route::post("/purchase-ticket",[TicketController::class,"adminPurchase"])->name("ticket.purchase");
    Route::get("/event-details/{id}",[AdminPageController::class,"eventDetails"])->name("event-details");

    //extra
    Route::get("/edit-event/{id}", [AdminPageController::class, "editEvent"])->name("edit-event");
    Route::get("/suspend-event/{id}", [EventController::class, "suspend"])->name("suspend-event");
});

Route::get("/resend-silent",[TicketController::class,"sendReminder"])->name("resend_silent");


Route::get('/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
