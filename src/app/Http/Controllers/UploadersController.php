<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Uploader;

class UploadersController extends Controller
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

    public function index()
    {
        return view('uploaders', ['uploaders' => Uploader::all()]);
    }

    public function update()
    {
        return response()->json(['uploaders' => Uploader::all()], 200);
    }
}
