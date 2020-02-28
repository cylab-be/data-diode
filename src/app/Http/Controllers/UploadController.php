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
     * Gets the view for the file upload page.
     * @return mixed the view.
     */
    public function index()
    {
        return view("upload");
    }

    /**
     * Add the file in the 'diode_local' filesystem.
     * 
     * @param Resquest the request containing the file(s) to upload.
     * 
     * @return 
     */
    public function uploadFile(Request $request) {
        $i = 0;
        while ($request->hasFile('input_file_' . $i)) {
            $file = $request->file('input_file_' . $i);
            $fileName = $request['input_file_full_path_' . $i];
            $path = $file->storeAs('files', $fileName);
            $i++;
        }
        return response()->json(['nbUploads'=>$i]);
    }

}
