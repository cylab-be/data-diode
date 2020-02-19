<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class UploadController extends Controller
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
     * Gets the view for the file upload page
     * @return mixed the view
     */
    public function index()
    {
        return view("upload");
    }

    /**
     * Add the file in the diode_local filesystem
     * 
     * @param Resquest $request
     * 
     * @return 
     */
    public function uploadFile(Request $request) {
        $file = $request->file('input_file');
        // $fileName = $file->getClientOriginalName();
        $fileName = $request->input_file_full_path;
        $path = $file->storeAs('files', $fileName);
    }

}
