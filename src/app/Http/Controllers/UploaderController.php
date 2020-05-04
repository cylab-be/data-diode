<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Uploader;
use App\Http\Resources\UploaderResource;
use App\Http\Requests\UploaderPortRequest;
use App\Http\Requests\CreateUploaderRequest;

/**
 * Controller used to manipulate the uploaders: their programs & their data.
 */
class UploaderController extends Controller
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
     * Retrieve all uploaders data and their program statuses.
     * 
     * @return mixed the uploaders data
     */
    public function retrieveAll()
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
        return response()->json([
            'uploaders' => UploaderResource::collection(Uploader::all()),
            'statuses' => $statuses,
        ], 200);
    }

    /**
     * Get one specific uploader
     * @param  Uploader $uploader   The uploader
     * @return UploaderResource     The uploader transformed into a resource for json formatting
     */
    public function retrieve(Uploader $uploader) {
        return new UploaderResource($uploader);
    }

    /**
     * Stop the BFTP program associated to a given uploader.
     * @param  Uploader $uploader   The uploader whose BFTP program must be stopped
     * @return mixed                The json uploader
     */
    public function stop(Uploader $uploader)
    {
        $cmd = 'supervisorctl stop blindftp-' . $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'This process could not be stopped.'], 451);
        }
    }

    /**
     * Restart the BFTP program associated to a given uploader.
     * @param  Uploader $uploader   The uploader whose BFTP program must be restarted
     * @return mixed                The json uploader
     */
    public function restart(Uploader $uploader)
    {
        $cmd = 'supervisorctl restart blindftp-' . $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'This process could not be restarted.'], 400);
        }
    }

    /**
     * Empty the folder synchronized by a BFTP program associated to a given uploader.
     * @param  Uploader $uploader   The uploader whose folder must be emptied
     * @return mixed                The json uploader
     */
    public function empty(Uploader $uploader)
    {
        $cmd = 'sudo ' . base_path('app/Scripts') . '/rm-folder-content.sh ' . $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $content = 'The content of the ' . $uploader->name . '\'s folder could not be deleted.';
            return response()->json(['message' => $content], 400);
        }
    }

    /**
     * Create an uploader using a Python script.
     * @param  CreateUploaderRequest $request   The request made by the user
     * @return mixed                            The json uploader
     */
    public function create(CreateUploaderRequest $request)
    {
        $cmd = 'sudo /var/www/data-diode/src/app/Scripts/add-supervisor-in.sh ';
        $cmd .= $request->input('name') . ' ' . strval($request->input('port'));
        $process = new Process($cmd);
        try {
            $process->mustRun();
            //$output = $process->getOutput();
            $uploader = Uploader::where('name', '=', $request->input('name'))->first();
            return response()->json($uploader, 201);
        } catch (ProcessFailedException $exception) {
            $output = $process->getOutput();
            return response()->json(['message' => $output], 400);
        }
    }

    /**
     * Delete an uploader using a Python script.
     * @param  Uploader $uploader   The uploader to delete
     * @return mixed                The json uploader
     */
    public function delete(Uploader $uploader)
    {
        // first removing all modules
        $status = $this::removePip($uploader)->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $message = 'Could not remove ' . $uploader->name . '\'s channel: failed to remove the PIP module.';
            return response()->json(['message' => $message], 400);
        }
        $status = $this::removeApt($uploader)->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $message = 'Could not remove ' . $uploader->name . '\'s channel: failed to remove the APT module.';
            return response()->json(['message' => $message], 400);
        }
        // then deleting uploader
        $cmd = 'sudo /var/www/data-diode/src/app/Scripts/del-supervisor-in.sh ' . $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $message = 'Successfully deleted ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 204);
        } catch (ProcessFailedException $exception) {
            $message = 'Failed to delete ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 400);
        }
    }

    /**
     * Add a PIP module to an uploader.
     * @param  UploaderPortRequest $request The request made by the user
     * @param  Uploader $uploader           The uploader
     * @return mixed                        The json uploader
     */
    public function addPip(UploaderPortRequest $request, Uploader $uploader)
    {
        $cmd = 'sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py pipadd ';
        $cmd .= $uploader->name . ' ' . strval($request->input('port'));
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $message = 'Successfully added a pip module to ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 200);
        } catch (ProcessFailedException $exception) {
            $message = 'Failed to add a pip module to ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 400);
        }
    }

    /**
     * Add an APT module to an uploader.
     * @param  UploaderPortRequest $request The request made by the user
     * @param  Uploader $uploader           The uploader
     * @return mixed                        The json uploader
     */    
    public function addApt(UploaderPortRequest $request, Uploader $uploader)
    {
        $cmd = 'sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py aptadd ';
        $cmd .= $uploader->name . ' ' . strval($request->input('port'));
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $message = 'Successfully added an apt module to ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 200);
        } catch (ProcessFailedException $exception) {
            $message = 'Failed to add an apt module to ' . $uploader->name . '\'s channel.';
            return response()->json(['message' => $message], 400);
        }
    }

    /**
     * Remove an uploader's PIP module.
     * @param  Request $request     The request made by the user
     * @param  Uploader $uploader   The uploader
     * @return mixed                The json uploader
     */
    public function removePip(Uploader $uploader)
    {
        $cmd = 'sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py pipremove ';
        $cmd .= $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $message = 'Successfully removed ' . $uploader->name . '\'s channel PIP module.';
            return response()->json(['message' => $message], 200);
        } catch (ProcessFailedException $exception) {
            $message = 'Failed to remove ' . $uploader->name . '\'s channel PIP module.';
            return response()->json(['message' => $message], 400);
        }        
    }

    /**
     * Remove an uploader's APT module.
     * @param  Uploader $uploader   The uploader
     * @return mixed                The json uploader
     */
    public function removeApt(Uploader $uploader)
    {
        $cmd = 'sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py aptremove ';
        $cmd .= $uploader->name;
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $message = 'Successfully removed ' . $uploader->name . '\'s channel APT module.';
            return response()->json(['message' => $message], 200);
        } catch (ProcessFailedException $exception) {
            $message = 'Failed to remove ' . $uploader->name . '\'s channel APT module.';
            return response()->json(['message' => $message], 400);
        }
    }

}
