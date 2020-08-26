<?php

namespace App\Exceptions;

use Exception;
use Carbon\Carbon;

class UserLoginExcepion extends Exception
{
    public $message;

    public function __construct($message = null)
    {
        parent::__construct('The given data was invalid.');

        $this->message = $message;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json([
            'status' => 'fail',
            'message' => ($this->message) ?? 'Unauthorized user.',
            'time' => Carbon::now()->toIso8601String(),
        ], 401);
    }
}