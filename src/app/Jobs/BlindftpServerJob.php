<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;

/**
 * Job used the launch the BlindFTP program asynchronously from the rest of the code.
 */
class BlindftpServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The command to run the BlindFTP program.
     * 
     * @var string
     */
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
            $this->command = "sudo sh -c '" . 
                "python /var/www/data-diode/BlindFTP_0.37/bftp.py " . 
                "-r /var/www/data-diode/src/storage/app/files " . 
                "-a " . env("INTERNAL_IP") . " " . 
                ">> /var/www/data-diode/src/storage/app/bftp-diodeout.log'";
        } else {
            // DIODE IN
            $this->command = "sudo sh -c '" . 
                "python /var/www/data-diode/BlindFTP_0.37/bftp.py " . 
                "-s /var/www/data-diode/src/storage/app/files " . 
                "-a " . env("DIODE_OUT_IP") . " -b -P 5 -x" . 
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
    }
}
