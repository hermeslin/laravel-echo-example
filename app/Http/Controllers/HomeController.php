<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only('index');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $partyId = 1;
        $roomId = 1;

        return view('home', compact(
            'user',
            'partyId',
            'roomId'
        ));
    }

    /**
     *
     */
    public function chatWithToken()
    {
        // $partyId = 1;
        // $roomId = 1;

        return view('chatWithToken', compact(
            'partyId',
            'roomId'
        ));
    }
}
