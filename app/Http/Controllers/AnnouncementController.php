<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exceptions\ApiValidationExcepion;
use App\Events\AnnouncementCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        broadcast(new AnnouncementCreated($message));

        return response()->json([
            'status' => 'success',
            'message' => 'message brocasted...'
        ]);
    }
}
