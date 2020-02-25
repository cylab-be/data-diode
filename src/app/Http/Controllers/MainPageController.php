<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller used to show all the options of the web interface.
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
                    'icon'=> 'fa-cogs',
                    'url'=> '/config',
                ],
                [
                    'name'=> 'FTP SERVER',
                    'icon'=> 'fa-toggle-on',
                    'url'=> '/ftpserver',
                ],
                [   'name'=> 'STORAGE',
                    'icon'=> 'fa-box-open',
                    'url'=> '/storage',
                ],
            ];            
        } else {
            // DIODE IN
            $routes = [
                [
                    'name'=> 'CONFIG',
                    'icon'=> 'fa-cogs',
                    'url'=> '/config',
                ],
                [
                    'name'=> 'FTP CLIENT',
                    'icon'=> 'fa-toggle-on',
                    'url'=> '/ftpclient',
                ],
                [   'name'=> 'UPLOAD',
                    'icon'=> 'fa-file-upload',
                    'url'=> '/upload',
                ],
            ];
        }

        return view('main', ['routes' => $routes]);
    }

}
