<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exceptions\ApiValidationExcepion;
use App\Events\PartyRoomMessageCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class PartyRoomMessageController extends Controller
{
    /**
     * broadcast message to announcement channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $partyId, $roomId)
    {
        //
        $request->merge([
            'partyId' => $partyId,
            'roomId' => $roomId,
        ]);

        $validator = Validator::make(
            $request->all(),
            $rules = [
                'partyId' => 'bail|required|in:1',
                'roomId' => 'bail|required|in:1',
                'message' => 'bail|required|string|min:1',
                'mode' => 'bail|required|string|in:directly,horizon'
            ]
        );

        if ($validator->fails()) {
            throw new ApiValidationExcepion($validator);
        }

        // broadcast message
        $carbonNow = Carbon::now();
        $user = Auth::user();
        $message = (object) [
            'id' => (int) $carbonNow->getPreciseTimestamp(3),
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'party_id' => $request->partyId,
            'room_id' => $request->roomId,
            'content' => $request->message,
            'created_at' => $carbonNow,
        ];

        if ($request->mode === 'horizon') {
            broadcast(new PartyRoomMessageCreated($message));
        } else {
            // publish message directly, not through horizon
            $partyRoomMessageCreatedEvent = new PartyRoomMessageCreated($message);
            $channel =  $partyRoomMessageCreatedEvent->broadcastOn();
            $event = $partyRoomMessageCreatedEvent->broadcastAs();
            $data = $partyRoomMessageCreatedEvent->broadcastWith();

            Redis::publish($channel, json_encode([
                'event' => $event,
                'data' => $data,
            ]));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'chat roome message created.'
        ]);
    }
}
