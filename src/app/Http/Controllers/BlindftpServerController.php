<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\FileServer;
use App\Jobs\BlindftpServerJob;

/**
 * Controller used for getting and updating the state of the BLindFTP server
 */
class BlindftpServerController extends Controller
{
    protected $dbName;
    protected $showedName;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
        if (!env('DIODE_IN', false)) {
            $this->dbName = 'ftpserver';
            $this->showedName = 'SERVER';
        } else {
            $this->dbName = 'ftpclient';
            $this->showedName = 'CLIENT';
        }
    }

    /**
     * Inform about the state of the BlindFTP server
     * 
     * @param FileServer $server: the server object retrived from the database
     * @return boolean true if the server is active from the point of vue of the database,
     * meaning its pid attribute is different than 0, false otherwise
     */
    public function isActive(FileServer $server)
    {
        return $server->pid != 0;
    }

    /**
     * Get the view showing the state of the server (ON/OFF) and one of the two
     * buttons (start/stop) depending in the state of the server. Also create
     * a database entry for the BlindFTP server if it's not already done
     * 
     * @return mixed the view 
     */
    public function index()
    {        
        $onStyle = "display:none";
        $offStyle = "display:none";

        $count = FileServer::where('name', '=', $this->dbName)->count();
        if ($count == 0) {
            FileServer::create(array('name' => $this->dbName));
        }

        $fileServer = FileServer::where('name', '=', $this->dbName)->firstOrFail();
        $serverState = self::isActive($fileServer) ? "ACTIVE WITH PID = " . $fileServer->pid : "OFF";
        $onStyle = self::isActive($fileServer) ? "display:none" : "";
        $offStyle = self::isActive($fileServer) ? "" : "display:none";
        $serverState = $this->showedName . " " . $serverState;

        return view('ftpview', ['serverState'=>$serverState, 'onStyle'=>$onStyle, 'offStyle'=>$offStyle]);
    }

    /**
     * Invert the state of the server (kills its process if it is on and replace its 
     * pid in the database by 0, or replace its pid in the database by the pid of the
     * server that was launched just after launching it. Also notifies the vue of all
     * the changes that have been made
     * 
     * @return mixed The json response containing data about the state of the server and which button
     * (ON/OFF) to show
     */
    public function toggle(Request $request)
    {        
        $serverState = "";
        $onStyle = "";
        $offStyle = "";
        $fileServer = FileServer::where('name', '=', $this->dbName)->firstOrFail();
        if ($request->command == 'on') {
            if (!self::isActive($fileServer)) {                
                BlindftpServerJob::dispatch()->onConnection('database')->onQueue('async');
                while ($fileServer->pid == 0) {
                    // The fileServer data is retrieved from the DB because
                    // its pid attribute is updated by an async process run
                    // by a worker from  the laravel queue.
                    $fileServer = FileServer::where('name', '=', $this->dbName)->firstOrFail();
                    sleep(1);
                    // TODO: afficher erreur apres un certain nombre de tours de boucle
                }
                $serverState = "ACTIVE WITH PID = " . $fileServer->pid;
                $onStyle = "none";
            }
        }
        if ($request->command == 'off') {
            if (self::isActive($fileServer)) {                
                $serverState = "OFF";
                $offStyle = "none";
                $process = new Process("sudo kill -15 " . $fileServer->pid);
                try  {
                    $process->mustRun();
                } catch (ProcessFailedException $exception) {
                    // TODO: show to user that server's process has already been killed
                }
                $fileServer->pid = 0;
            }
        }
        $fileServer->save();
        $serverState = $this->showedName . " " . $serverState;
        return response()->json(['showedName' => $this->showedName, 'serverState'=>$serverState, 'onStyle'=>$onStyle, 'offStyle'=>$offStyle]);
    }
}