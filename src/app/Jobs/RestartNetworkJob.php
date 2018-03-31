<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;

class RestartNetworkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $process = new Process("sudo " . base_path("app/Scripts")
          . "/datadiode.sh fluship " . env("INPUT_INTERFACE", "lo"));
        $process->mustRun();
        $process = new Process("sudo " . base_path("app/Scripts")
          . "/datadiode.sh fluship " . env("OUTPUT_INTERFACE", "lo"));
        $process->mustRun();
        $process = new Process("sudo " . base_path("app/Scripts")
          . "/datadiode.sh restartnetwork");
        $process->mustRun();
    }
}
