<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use App\FileServer;

class BlindftpServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dbName;
    protected $command;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (!env('DIODE_IN', false)) {
            // DIODE OUT
            $this->dbName = 'ftpserver';
            $this->command = "sudo sh -c '" . 
                "python /var/www/data-diode/BlindFTP_0.37/bftp.py " . 
                "-r /var/www/data-diode/src/storage/app/files " . 
                "-a " . env("INTERNAL_IP") . " " . 
                ">> /var/www/data-diode/src/storage/app/bftp-diodeout.log'";
        } else {
            // DIODE IN
            $this->dbName = 'ftpclient';
            $this->command = "sudo sh -c '" . 
                "python /var/www/data-diode/BlindFTP_0.37/bftp.py " . 
                "-s /var/www/data-diode/src/storage/app/files " . 
                "-a " . env("DIODE_OUT_IP") . " -b -P 5 " . 
                ">> /var/www/data-diode/src/storage/app/bftp-diodein.log'";
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $process = new Process($this->command);
        $process->disableOutput();
        $process->start();
        // $process->start(); $pid = $process->getPid(); // -> I think this does not return the pid of the process but the one that launches it
        $pid_process = new Process("PID=`ps auxw | grep bftp.py | grep -v grep | awk '{ print $2 }'` && echo \$PID");
        $pid_process->mustRun();
        // $fileServer = FileServer::find(1);
        $count = FileServer::where('name', '=', $this->dbName)->count();
        if ($count == 0) {
            // TODO
        } else if  ($count == 1) {
            $fileServer = FileServer::where('name', '=', $this->dbName)->firstOrFail();
        } else {
            // Should be impossible, name is unique
        }
        $fileServer->pid = intval($pid_process->getOutput());
        $fileServer->save();
    }
}
