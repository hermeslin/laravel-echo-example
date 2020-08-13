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
        //
        $list = [
            // party_id
            '1' => [
                // room_id
                '1' => [
                    // user.role_id
                    '3', '7'
                ]
            ],
        ];

        $userIdList = $list[$partyId][$roomId];
        if (is_array($userIdList) && count($userIdList) > 0) {
            return in_array($user->role_id, $userIdList);
        }
        return false;
    }
}
