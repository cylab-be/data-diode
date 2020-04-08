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
        $statuses = array();        
        $uploaders = Uploader::all();
        foreach ($uploaders as $uploader) {
            $cmd = 'supervisorctl pid blindftp-' . $uploader->name;
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                if ($output == '0') {
                    array_push($statuses, 'stopped');
                } else {
                    array_push($statuses, 'running');
                }
            } catch (ProcessFailedException $exception) {
                array_push($statuses, 'no process');
            }
        }
        return view('uploaders', ['uploaders' => $uploaders, 'statuses' => $statuses]);
    }

    public function update()
    {
        $statuses = array();        
        $uploaders = Uploader::all();
        foreach ($uploaders as $uploader) {
            $cmd = 'supervisorctl pid blindftp-' . $uploader->name;
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                array_push($statuses, 'running');
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                if (strpos($output, 0) == '0') {
                    array_push($statuses, 'stopped');
                } else {
                    array_push($statuses, 'error');
                }
            }
        }
        return response()->json(['uploaders' => Uploader::all(), 'statuses' => $statuses], 200);
    }

    public function stop(Request $request)
    {
        if ($request->uploader == null) {
            return response()->json(['message' => 'You must specify an uploader\'s name.'], 422);
        } else if (!is_string($request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be a string of characters.'], 422);
        } else if (!preg_match("/^[a-zA-Z]*$/", $request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be composed of alphabetical characters only.'], 422);
        }
        $cmd = 'supervisorctl stop blindftp-' . $request->uploader;
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'This process could not be stopped.'], 400);
        }
    }

    public function restart(Request $request)
    {
        if ($request->uploader == null) {
            return response()->json(['message' => 'You must specify an uploader\'s name.'], 422);
        } else if (!is_string($request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be a string of characters.'], 422);
        } else if (!preg_match("/^[a-zA-Z]*$/", $request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be composed of alphabetical characters only.'], 422);
        }
        $cmd = 'supervisorctl restart blindftp-' . $request->uploader;
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'This process could not be restarted.'], 400);
        }
    }

    public function empty(Request $request)
    {
        if ($request->uploader == null) {
            return response()->json(['message' => 'You must specify an uploader\'s name.'], 422);
        } else if (!is_string($request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be a string of characters.'], 422);
        } else if (!preg_match("/^[a-zA-Z]*$/", $request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be composed of alphabetical characters only.'], 422);
        }
        $cmd = '';
        /*if ($request->uploader == 'apt' || $request->uploader == 'pip') {
            $cmd = 'sudo rm -rf /var/www/data-diode/src/storage/app/files/' . $request->uploader;
        } else {
            $cmd = 'sudo rm -rf /var/www/data-diode/src/storage/app/files/ftp/' . $request->uploader;
        }*/
        $cmd = 'sudo rm -rf /var/www/data-diode/src/storage/app/files/' . $request->uploader . '/*';
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'The content of the ' . $request->uploader . '\'s folder could not be deleted.'], 400);
        }
    }
}
