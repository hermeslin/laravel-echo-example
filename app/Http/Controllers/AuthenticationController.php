<?php

namespace App\Http\Controllers;

use GuzzleHttp;
use App\Exceptions\ApiValidationExcepion;
use App\Exceptions\UserLoginExcepion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    /**
     * broadcast message to announcement channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function oAutheExchangeToken(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $rules = [
                'email' => 'bail|required',
                'password' => 'bail|required',
            ]
        );

        if ($validator->fails()) {
            throw new ApiValidationExcepion($validator);
        }

        // exchange access token
        $http = new GuzzleHttp\Client;

        try {
            $response = $http->post(config('auth.oauth.request_token_url'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('auth.oauth.client_id'),
                    'client_secret' => config('auth.oauth.client_secret'),
                    'username' => $request->email,
                    'password' => $request->password,
                    'scope' => '',
                ],
            ]);

            return response()->json(
                array_merge(
                    [
                        'status' => 'success',
                        'message' => 'user authorized.',
                    ],
                    json_decode((string) $response->getBody(), true)
                )
            );
        } catch (\Exception $exception) {
            throw new UserLoginExcepion();
        }
    }
}
