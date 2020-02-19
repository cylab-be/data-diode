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
     * transmits it through the data diode
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
        $process = new Process("sudo " .
            "python /home/vagrant/BlindFTP_0.37/bftp.py " .
            "-e /var/www/data-diode/src/storage/app/files/".$fileName. " " .
            "-a ". env("DIODE_OUT_IP") . " &");
    }

}
