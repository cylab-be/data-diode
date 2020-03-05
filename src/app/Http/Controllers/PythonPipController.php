<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller used to install new python modules named in the Web interface.
 */
class PythonPipController extends Controller
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
     * Get the view for page used to install new python modules.
     *
     * @return mixed the view.
     */
    public function index()
    {
        return view('pythonpip');
    }
}
