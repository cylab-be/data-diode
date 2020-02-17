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
    public function index(Request $request)
    {
        return view("upload");
    }

    /**
     * Add the file in the /files storage directory,
     * transmits it through the data diode and shows
     * the user that the upload worked
     * 
     * @param Resquest $request
     * 
     * @return mixed
     */
    public function uploadFile(Request $request) {
        $file = $request->file('image');
        $fileName = $file->getClientOriginalName();
        $path = $file->storeAs('files', $fileName);
        return view('uploaded', ['fileName'=>$fileName]);
    }

}
