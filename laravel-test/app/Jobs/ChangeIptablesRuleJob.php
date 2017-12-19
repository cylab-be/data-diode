<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Rule;

abstract class ChangeIptablesRuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $process;
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->process->run();
        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }
        $saveProcess = new Process("sudo " . base_path("app/Scripts") . "/datadiode.sh save");
        $saveProcess->run();
        if (!$saveProcess->isSuccessful()) {
            throw new ProcessFailedException($saveProcess);
        }
    }
}
