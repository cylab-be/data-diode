<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    public function index()
    {
        return view("config");
    }
}
