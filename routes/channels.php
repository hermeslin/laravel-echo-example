<?php
use App\Broadcasting\PartyRoomMessageChannel;
use App\Broadcasting\UserCacheInfoChannel;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
Broadcast::channel('User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Party.{party_id}.Room.{room_id}', PartyRoomMessageChannel::class);

Broadcast::channel('User.Cache.Info',  UserCacheInfoChannel::class);
