<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

date_default_timezone_set('America/Bogota');

class UserConfirmationController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.user-confirmation');
    }
}