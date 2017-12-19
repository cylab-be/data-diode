<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use App\Rule;
use App\NetworkInterface;

class CreateIptablesRuleJob extends ChangeIptablesRuleJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Rule $rule)
    {
        $this->process = new Process("sudo " . base_path("app/Scripts") . "/datadiode.sh add "
            . NetworkInterface::getCurrentInterface() . " " . $rule->input_port . " "
            . $rule->destination . " " . $rule->output_port);
    }
}
