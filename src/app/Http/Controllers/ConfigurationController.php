<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller used for getting the configuration web page
 */
class ConfigurationController extends Controller
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
     * Gets the view for the configuration page
     * @return mixed the view
     */
    public function index()
    {
        return view("config");
    }
}
