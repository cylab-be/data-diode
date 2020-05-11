<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    /**
     * Get the view for the main page.
     *
     * @return mixed the view.
     */
    public function index()
    {
        return view('main');
    }
}
