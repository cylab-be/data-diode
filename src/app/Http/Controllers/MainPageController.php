<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function indexIn() {
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
        return view('main', ['routes' => $routes]);
    }

    public function indexOut() {
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
                'icon'=> 'fa-file-upload',
                'url'=> '/storage',
            ],
        ];
        return view('main', ['routes' => $routes]);
    }
}
