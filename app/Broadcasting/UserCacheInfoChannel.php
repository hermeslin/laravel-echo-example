<?php

namespace App\Broadcasting;

use App\User;

class UserCacheInfoChannel
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
    public function join(User $user)
    {
        $result = [
            'id' => $user->id,
            'name' => $user->name,
            'email' =>  $user->email,
        ];

        return $result;
    }
}
