<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

    public function addMirror(Request $request) {
        $cmd = 'sudo ' . base_path('app/Scripts') . '/download-apt.sh ' . $request->uploader . ' ' . $request->url;
        $process = new Process($cmd);
        // the following is needed to ensure larger downloads will end
        $process->setTimeout(0);
        $process->setIdleTimeout(365 * 24 * 3600);
        try {
            $process->mustRun();
            //return response()->json(['output' => $process->getOutput()]);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }
}
