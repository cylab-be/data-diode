<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        // package check
        if ($request->name == null) {
            return response()->json(['message' => 'You must specify a package name!'], 400);
        } else if (!is_string($request->name)) {
            return response()->json(['message' => 'The package name must be a string!'], 400);
        } else if (strlen(preg_replace('/\s+/', '', $request->name)) == 0) {
            return response()->json(['message' => 'You must specify a package name!'], 400);
        }
        // uploader check
        if ($request->uploader == null) {
            return response()->json(['message' => 'You must specify an uploader\'s name.'], 422);
        } else if (!is_string($request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be a string of characters.'], 422);
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be composed of alphabetical characters only.'], 422);
        }
        // checking uploader's name existing
        $count = Uploader::where('name', '=', $request->uploader)->count();
        if ($count == 0) {
            return response()->json(['message' => 'This uploader does not exist.'], 400);
        }
        // running script
        $name = $request->name;
        $process = new Process('sudo -H ' . base_path('app/Scripts') . "/sendpip.sh '" . $name . "'" . ' ' . $request->uploader);
        $process->setTimeout(0);
        $process->setIdleTimeout(365 * 24 * 3600);
        try {
            $process->mustRun();
            return response()->json(['output' => $process->getOutput()]);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }

}