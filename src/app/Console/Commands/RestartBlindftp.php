<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Jobs\BlindftpServerJob;

/**
 * Command to restart the BLindFTP program
 */
class RestartBlindftp extends Command
{
    /**
     * The command to kill the BlindFTP program (whithout the pids).
     * 
     * @var string
     */
    protected $killCommand;

    /**
     * The command to get the pids of the the BlindFTP program running 
     * processes.
     * 
     * @var string
     */
    protected $pidCommand;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bftp:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart the BlindFTP program';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pidCommand = "PID=`ps auxw | grep bftp.py | grep -v grep | awk '{ print $2 }'` && echo \$PID";
        if (!env('DIODE_IN', false)) {
            // DIODE OUT
            $this->killCommand = 'sudo kill -15 ';
        } else {
            // DIODE IN
            $this->killCommand = 'sudo kill -9 ';
        }
    }

    /**
     * Get the pids corresponding the BlindFTP running processes.
     * 
     * @return string the pids.
     */
    private function getPids() {
        $pidsProcess = new Process($this->pidCommand);
        $pidsProcess->mustRun();
        return $pidsProcess->getOutput();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pids = self::getPids();

        $killProcess = new Process($this->killCommand . $pids);
        $killProcess->mustRun();
        
        BlindftpServerJob::dispatch()->onConnection('database')->onQueue('async');

        while (empty($pids)) {
            $pids = self::getPids();
            sleep(1);
            // TODO: show error after a certain number of loops
        }        
    }
}
