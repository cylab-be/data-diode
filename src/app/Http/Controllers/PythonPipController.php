<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Http\Requests\RunPipRequest;
use App\Uploader;

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
     * Download a Python package and place it in the uploader's
     * folder.
     * 
     * @param RunPipRequest $request    The request made by the user
     * @param  Uploader $uploader       The uploader
     * @return mixed                    The json command output
     */
    public function runPip(RunPipRequest $request, Uploader $uploader)
    {
        $cmd = 'sudo -H ' . base_path('app/Scripts') . "/sendpip.sh '";
        $cmd .= $request->input("package") . "'" . ' ' . $uploader->name;
        $process = new Process($cmd);
        $process->setTimeout(0);
        $process->setIdleTimeout(365 * 24 * 3600); // one year should be long enough...
        try {
            $process->mustRun();
            return response()->json(['output' => $process->getOutput()]);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }

}