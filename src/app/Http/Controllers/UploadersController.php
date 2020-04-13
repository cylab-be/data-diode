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
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $request->uploader)) {
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
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $request->uploader)) {
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
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $request->uploader)) {
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

    public function add(Request $request)
    {
        // uploader check
        if ($request->uploader == null) {
            return response()->json(['message' => 'You must specify an uploader\'s name.'], 422);
        } else if (!is_string($request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be a string of characters.'], 422);
        } else if (!preg_match("/^[a-zA-Z0-9]+$/", $request->uploader)) {
            return response()->json(['message' => 'The uploader\'s name must be composed of alphabetical characters only.'], 422);
        }
        // port check
        if ($request->port == null) {
            return response()->json(['message' => 'You must specify an uploader\'s port.'], 422);
        } else if (!is_integer($request->port)) {
            return response()->json(['message' => 'The uploader\'s port must be an integer.'], 422);
        } else if ($request->port < 1025 || $request->port > 65535) {
            return response()->json(['message' => 'The uploader\'s port must be between 1025 and 65535.'], 422);
        }
        // checking uploader's name not already added
        $count = Uploader::where('name', '=', $request->uploader)->count();
        if ($count > 0) {
            return response()->json(['message' => 'This uploader already exists.'], 400);
        }
        // checking port not already used
        $uploaders = Uploader::all();
        $portUsedByUploaders = false;
        foreach ($uploaders as $u) {
            if ($u->port == $request->port) {
                $portUsedByUploaders = true;
                break;
            }
        }
        if (!$portUsedByUploaders) {
            $cmd = 'sudo netstat -peanut | grep ":' . strval($request->port) . ' "';
            $process = new Process($cmd);
            try {
                $process->mustRun();
                $output = $process->getOutput();
                if (strlen($output) > 1) {
                    return response()->json(['message' => 'This port number is already used by another program.'], 400);
                }
            } catch (ProcessFailedException $exception) {
                $output = $process->getOutput();
                if (strlen($output) > 1) {
                    return response()->json(['message' => 'A problem appeared when checking if the port number is already used.'], 400);
                } // else there is no real error, just an empty output considered as one.
            }
        } else {
            return response()->json(['message' => 'This port number is already used by another uploader.'], 400);
        }
        // adding uploader
        $cmd = 'sudo /var/www/data-diode/src/app/Scripts/add-supervisor-in.sh ' . $request->uploader . ' ' . strval($request->port);
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $output = $process->getOutput();
            return response()->json(['message' => $output], 200);
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            return response()->json(['message' => $output], 400);
        }
    }

    public function del(Request $request)
    {
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
        // deleting uploader
        $cmd = 'sudo /var/www/data-diode/src/app/Scripts/del-supervisor-in.sh ' . $request->uploader;
        $process = new Process($cmd);
        try {
            $process->mustRun();
            return response()->json(['message' => 'Successfully deleted ' . $request->uploader . '\'s channel.'], 200);
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'Failed to delete ' . $request->uploader . '\'s channel.'], 400);
        }
    }
}
