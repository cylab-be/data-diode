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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = "sudo " . 
        "python /var/www/data-diode/BlindFTP_0.37/bftp.py " . 
        "-r /var/www/data-diode/src/storage/app/files " . 
        "-a " . env("INTERNAL_IP") . " &";
        $process = new Process($command);        
        $process->disableOutput();
        $process->start();
        // $process->start(); $pid = $process->getPid(); // -> I think this does not return the pid of the process but the one that launches it        
        $pid_process = new Process("PID=`ps auxw | grep bftp.py | grep -v grep | awk '{ print $2 }'` && echo \$PID");        
        $pid_process->mustRun();
        $fileServer = FileServer::find(1);
        $fileServer->pid = intval($pid_process->getOutput());
        $fileServer->save();
    }
}
