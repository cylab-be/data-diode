<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Artisan;

/**
 * Controller used to restart the BLindFTP program and read its output.
 */
class BlindftpServerController extends Controller
{
    /**
     * The command to read the output of the BlindFTP program.
     * 
     * @var string
     */
    protected $catCommand;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
        if (!env('DIODE_IN', false)) {
            // DIODE OUT
            $this->catCommand = 'if [ -f /var/www/data-diode/src/storage/app/bftp-diodeout.log ]; ' . 
                'then cat /var/www/data-diode/src/storage/app/bftp-diodeout.log; ' . 
                'else echo "There is currently no log info."; ' . 
                'fi;';
        } else {
            // DIODE IN
            $this->catCommand = 'if [ -f /var/www/data-diode/src/storage/app/bftp-diodein.log ]; ' . 
            'then cat /var/www/data-diode/src/storage/app/bftp-diodein.log; ' . 
            'else echo "There is currently no log info."; ' . 
            'fi;';
        }
    }

    /**
     * Get the view showing the button to restart the server or the client.
     * 
     * @return mixed the view.
     */
    public function index()
    {        
        $catProcess = new Process($this->catCommand);
        $catProcess->mustRun();
        $logInfo = $catProcess->getOutput();

        return view('ftpview', [
            'logInfo' => $logInfo,
        ]);
    }    

    /**
     * Restart the server or the client (kills its processes launch it. Also notifies 
     * the vue of all  the changes that have been made. All that using a command
     * defined by App\Console\Commands\RestartBlindftp.php.
     * After that, it ensures that a queue worker is running to launch the BlindFTP 
     * program, otherwise it launches a worker via a command defined by 
     * App\Console\Commands\EnsureQueueWorkerIsRunning.php.
     * 
     * @param Request the request.
     * 
     * @return mixed an empty json response.
     */
    public function restart(Request $request)
    {
        exec('sudo php ' . base_path('artisan') . ' bftp:restart'); // Artisan::call('bftp:restart');
        exec('sudo php ' . base_path('artisan') . ' queue:checkup'); // Artisan::call('queue:checkup');

        return response()->json([]);
    }

}