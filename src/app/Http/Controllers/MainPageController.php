<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller used to show all the options of the Web interface.
 */
class MainPageController extends Controller
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
        $routes = [];
        if (!env('DIODE_IN', false)) {
            // DIODE OUT
            $routes = [
                [
                    'name'=> 'CONFIG',
                    'icon'=> 'fa fa-cogs',
                    'url'=> '/config',
                ],
                [   'name'=> 'STORAGE',
                    'icon'=> 'fa fa-box-open',
                    'url'=> '/storage',
                ],
            ];            
        } else {
            // DIODE IN
            $routes = [
                [
                    'name'=> 'CONFIG',
                    'icon'=> 'fa fa-cogs',
                    'url'=> '/config',
                ],
                [   'name'=> 'UPLOAD',
                    'icon'=> 'fa fa-file-upload',
                    'url'=> '/upload',
                ],
                [
                    'name'=> 'PYTHON PIP',
                    'icon'=> 'fab fa-python',
                    'url'=> '/pipin',
                ],
            ];
        }

        return view('main', ['routes' => $routes]);
    }

}
