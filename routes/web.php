<?php

use App\Http\Controllers\EventController;
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
    return view('welcome');
});
Route::get("/artisan-migrate", function () {
    Artisan::call("migrate:fresh");
    Artisan::call("db:seed");
    symlink("/home/attendor/public_html/api/storage/app/public", "/home/attendor/public_html/api/public/storage");
});

Route::get("artisan-cache",function(){
    Artisan::call("cache:clear");
    Artisan::call("config:clear");
});

//admin panel

Route::group(["prefix" => "admin", "as" => "admin."], function () {

    Route::get("/dashboard", [AdminPageController::class, "dashboard"])->name("dashboard");
    Route::get("/users/{param}", [AdminPageController::class, "users"])->name("users");
    Route::get("/events/{param}", [AdminPageController::class, "events"])->name("events");
    Route::get("/complaints", [AdminPageController::class, "complaints"])->name("complaints");
    Route::get("/support", [AdminPageController::class, "support"])->name("support");
    Route::get("/logout", [AdminPageController::class, "logout"])->name("logout");

    //extra
    Route::get("/edit-event/{id}", [AdminPageController::class, "editEvent"])->name("edit-event");
    Route::get("/suspend-event/{id}", [EventController::class, "suspend"])->name("suspend-event");
});
