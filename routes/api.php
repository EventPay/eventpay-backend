<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\auth\EmailVerificationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTicketController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//public routes
Route::post("/user/login", [AuthController::class, "login"])->name("login");
Route::post("/user/register", [AuthController::class, "register"])->name("register");

//forgot password
Route::post("/user/send-forgot-password", [ForgotPasswordController::class, "sendCode"])->name("send_recovery_password");
Route::post("/user/verify-password-code", [ForgotPasswordController::class, "checkCode"])->name("check_recovery_password");
Route::post("/user/change-password", [ForgotPasswordController::class, "changePassword"])->name("change_forgot_password");

Route::group(['middleware' => "auth:sanctum"], function () {

    Route::get("/user/get", [UserController::class, "get"])->name("get-user");
    Route::get("/user/get/events", [UserController::class, "events"])->name("get-user-events");
    Route::post("/user/edit/profile-image", [UserController::class, "uploadProfile"])->name("upload-profile");
    Route::post("/user/edit/profile", [UserController::class, "edit"])->name("edit-profile");
    Route::post("/user/edit/change-password/", [UserController::class, "changePassword"])->name("change-password");

    //email verification

    Route::post("/user/send-email-ver", [EmailVerificationController::class, "sendVerificationCode"])->name("send_verification_email");
    Route::post("/user/verify-code", [EmailVerificationController::class, "verifyCode"])->name("verify_email_code");

    Route::group(['middleware' => "email_auth"], function () {

        //following
        Route::post("/user/follow-user", [FollowController::class, "follow"])->name("follow_user");
        Route::post("/user/unfollow-user", [FollowController::class, "unFollow"])->name("unfollow_user");

        //event routes

        Route::get("/event/featured/", [EventController::class, "featuredEvents"])->name("featured-events");
        Route::get("/event/promoted/", [EventController::class, "promotedEvents"])->name("promoted-events");
        Route::get("/event/list/", [EventController::class, 'listEvents'])->name("list-events");

        Route::get("/event/get/{event_id}", [EventController::class, "show"])->name("getEvent");
        Route::post("/event/create", [EventController::class, "create"])->name("create-event");
        Route::post("/event/edit/{id}", [EventController::class, "edit"])->name("edit-event");
        Route::post("/event/delete/{id}", [EventController::class, "delete"])->name("delete-event");

        Route::get("/event/search/",[EventController::class,"search"])->name("search-event");

//category

        Route::get("/category/all", [EventCategoryController::class, "listCategory"])->name("list-category");

        //get events in category
        Route::get("/category/events/{slug}", [EventCategoryController::class, "show"])->name("get-event-category");
    

//comments
        Route::post("/event/comments/get/{event_id}", [EventController::class, "show"])->name("getEventComments");
        Route::post("/event/comments/create", [CommentController::class, "create"])->name("addComment");

        Route::post("/event/tickets/create", [EventTicketController::class, "create"])->name("create-ticket");
        Route::post("/event/tickets/delete/{id}", [EventTicketController::class, "delete"])->name("delete-ticket");
        Route::post("/event/tickets/edit/{id}", [EventTicketController::class, "edit"])->name("edit-ticket");
        //add edit and delete
        Route::post("/event/tickets/purchase", [TicketController::class, "purchase"])->name("purchase-ticket");
        Route::post("/event/tickets/validate", [TicketController::class, "validateTicket"])->name("validate-ticket");

    });

});

//placed outside the sanctum auth to avoid redirect
Route::get("/event/ticket/payment/paystack/{user_id}/{event_ticket_id}", [TicketController::class, "validatePayment"])->name("validate-payment");
