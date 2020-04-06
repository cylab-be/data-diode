<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     * Get the view for page used to download new python modules.
     *
     * @return mixed the view.
     */
    public function index()
    {
        return view('pythonpip');
    }

    /**
     * Execute a Python package download.
     * 
     * @param Request the request.
     * 
     * @return mixed json the output of the executed command.
     */
    public function runPip(Request $request)
    {
        if ($request->name == null) {
            return response()->json(['message' => 'You must specify a package name!'], 400);
        } else if (!is_string($request->name)) {
            return response()->json(['message' => 'The package name must be a string!'], 400);
        } else if (strlen(preg_replace('/\s+/', '', $request->name)) == 0) {
            return response()->json(['message' => 'You must specify a package name!'], 400);
        }
        $cmd = "sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py pip 1";
        $processCmd = new Process($cmd);
        $processCmd->mustRun();
        $name = $request->name;
        $process = new Process('sudo -H ' . base_path('app/Scripts') . "/sendpip.sh '" . $name . "'");
        $process->setTimeout(0);
        $process->setIdleTimeout(120);
        try {
            $process->mustRun();
            return response()->json(['output' => $process->getOutput()]);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => $exception], 400);
        }
    }

}