<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'party_id',
        'room_id',
        'user_id',
        'content',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /*
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
