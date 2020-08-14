<?php

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

// it generates all the routes required for user authentication
// e.g.
// 1. Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
// 2. Route::post('register', 'Auth\RegisterController@register');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
     // under Api folder
    Route::post('/broadcast-announcement', 'AnnouncementController@broadcast')->name('broadcast-announcement');
    Route::post('/party/{partyId}/room/{roomId}/message', 'PartyRoomMessageController@create')->name('create-party-room-message');
});
