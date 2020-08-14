<?php

namespace App\Broadcasting;

use App\User;

class PartyRoomMessageChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @param  integer  $partyId party id
     * @param  integer  $roomId room id
     * @return array|bool
     */
    public function join(User $user, $partyId, $roomId)
    {
        $result = false;
        if ($user->canJoin($partyId, $roomId)) {
            $result = [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }

        return $result;
    }
}
