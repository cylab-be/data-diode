<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Http\Requests\AddMirrorRequest;
use App\Uploader;

class AptController extends Controller
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
     * Download an APT mirror into an uploader's folder.
     * 
     * @param  AddMirror $request   The request made by the user
     * @param  Uploader $uploader   The uploader
     * @return mixed                The json response
     */
    public function addMirror(AddMirrorRequest $request, Uploader $uploader)
    {
        $cmd = 'sudo ' . base_path('app/Scripts') . '/download-apt.sh ';
        $cmd .= $uploader->name . ' ' . $request->input('url');
        $process = new Process($cmd);
        // the following is needed to ensure larger downloads will end
        $process->setTimeout(0);
        $process->setIdleTimeout(365 * 24 * 3600); // one year should be long enough...
        try {
            $process->mustRun();
            //return response()->json(['output' => $process->getOutput()]);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }
}
