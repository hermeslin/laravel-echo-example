<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exceptions\ApiValidationExcepion;
use App\Events\AnnouncementCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class AnnouncementController extends Controller
{
    /**
     * broadcast message to announcement channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function broadcast(Request $request)
    {
        //
        $validator = Validator::make(
            $request->all(),
            $rules = [
                'message' => 'bail|required|string|min:1',
                'mode' => 'bail|required|string|in:directly,horizon'
            ]
        );

        if ($validator->fails()) {
            throw new ApiValidationExcepion($validator);
        }

        // broadcast message
        $carbonNow = Carbon::now();
        $message = (object) [
            'id' => (int) $carbonNow->getPreciseTimestamp(3),
            'content' => $request->message,
            'created_at' => $carbonNow,
        ];

        if ($request->mode === 'horizon') {
            broadcast(new AnnouncementCreated($message));
        } else {
            // publish message directly, not through horizon
            $announcementCreatedEvent = new AnnouncementCreated($message);
            $channel =  $announcementCreatedEvent->broadcastOn();
            $event = $announcementCreatedEvent->broadcastAs();
            $data = $announcementCreatedEvent->broadcastWith();

            Redis::publish($channel, json_encode([
                'event' => $event,
                'data' => $data,
            ]));
        }


        return response()->json([
            'status' => 'success',
            'message' => 'announcement message created.'
        ]);
    }
}
