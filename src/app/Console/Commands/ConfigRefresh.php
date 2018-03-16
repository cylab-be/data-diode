<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;
use App\Rule;

class ConfigRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the rules in iptables according to .env';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->refreshMasquerade();
        $this->refreshRules();
        //$this->refreshArp();
    }

    private function refreshMasquerade()
    {
        if(option_exists("PREVIOUS_OUTPUT_INTERFACE")) {
          $disableNatProcess = new Process("sudo " . base_path("app/Scripts")
            . "/datadiode.sh disablenat " . option("PREVIOUS_OUTPUT_INTERFACE", "lo"));
          $disableNatProcess->run();
          if (!$disableNatProcess->isSuccessful()) {
              throw new ProcessFailedException($disableNatProcess);
          }
        }
        $enableNatProcess = new Process("sudo " . base_path("app/Scripts")
          . "/datadiode.sh nat " . env("OUTPUT_INTERFACE", "lo"));
        $enableNatProcess->run();
        if (!$enableNatProcess->isSuccessful()) {
            throw new ProcessFailedException($enableNatProcess);
        }
        option(['PREVIOUS_OUTPUT_INTERFACE' => env("OUTPUT_INTERFACE", "lo")]);
    }

    private function refreshRules()
    {
      //TODO fix this
      /*
      foreach (Rule::all() as $rule) {
          CreateIptablesRuleJob::dispatch($rule);
          if (env("DIODE_IN", true)) {
              $rule->destination = env("DIODE_OUT_IP");
              $rule->save();
          }
      }*/
    }

    private function refreshArp()
    {
      //TODO fix this
        $arpProcess = new Process("sudo " . base_path("app/Scripts") . "/datadiode.sh arp "
          . env("DIODE_OUT_MAC") . " " . env("DIODE_OUT_IP"));
        $arpProcess->run();
        if (!$arpProcess->isSuccessful()) {
            throw new ProcessFailedException($arpProcess);
        }
    }
}
