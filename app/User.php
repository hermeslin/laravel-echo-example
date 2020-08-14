<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * check user can join party room
     */
    public function canJoin($partyId, $roomId)
    {
        //
        $list = [
            // party_id
            '1' => [
                // room_id
                '1' => [
                    // user.id
                    1, 4, 16, 32, 34, 88, 2,10, 15, 41, 101, 127, 165, 200
                ]
            ],
        ];

        $userIdList = $list[$partyId][$roomId];
        if (is_array($userIdList) && count($userIdList) > 0) {
            return in_array($this->id, $userIdList);
        }
        return false;
    }
}
