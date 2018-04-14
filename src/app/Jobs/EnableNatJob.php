<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;

/**
 * Enables NATing in iptables
 */
class EnableNatJob extends ChangeIptablesRuleJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->process = new Process("sudo " . base_path("app/Scripts")
          . "/datadiode.sh nat " . option("OUTPUT_INTERFACE"));
    }
}
