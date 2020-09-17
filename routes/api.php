<?php

use Illuminate\Http\Request;
use Illuminate\Broadcasting\BroadcastController;

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
Route::middleware('auth:api')->group(function () {
    // user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api-user');

    Route::post('/broadcast-announcement', 'AnnouncementController@broadcast')->name('api-broadcast-announcement');
    Route::post('/party/{partyId}/room/{roomId}/message', 'PartyRoomMessageController@create')->name('api-create-party-room-message');
    Route::post('/party/{partyId}/room/{roomId}/message/user-typing-indicator', 'PartyRoomMessageController@userTypingIndicator')->name('api-set-party-room-message-user-typing-indicator');

    Route::match(
        ['get', 'post'],
        '/broadcasting/auth',
        '\\'.BroadcastController::class.'@authenticate'
    );

    // under Api folder
    Route::namespace('Api')->group(function () {

    });
});

Route::fallback(function(){
    return response()->json([
        'message' => 'OOPS!'
    ], 404);
});