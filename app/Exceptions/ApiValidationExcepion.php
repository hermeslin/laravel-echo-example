<?php

namespace App\Exceptions;

use Exception;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class ApiValidationExcepion extends Exception
{
    public $validator;

    public function __construct(Validator $validator)
    {
        parent::__construct('The given data was invalid.');

        $this->validator = $validator;
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
            'message' => 'The given data was invalid.',
            'time' => Carbon::now()->toIso8601String(),
            'errors' => $this->validator->errors()
        ], 422);
    }
}