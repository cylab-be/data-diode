<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;
use App\Jobs\DisableNatJob;
use App\Jobs\EnableNatJob;
use App\Rule;
use App\NetworkConfiguration;

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
        $this->refreshConfig();
        $this->refreshMasquerade();
        $this->refreshRules();
        //$this->refreshArp();
    }

    //TODO files must exist for simlinks
    private function refreshConfig()
    {
        NetworkConfiguration::getInput()->saveInput();
        NetworkConfiguration::getOutput()->saveOutput();
    }

    private function refreshMasquerade()
    {
        if (option_exists("OUTPUT_INTERFACE")) {
            DisableNatJob::dispatch();
        }
        option(['OUTPUT_INTERFACE' => env("OUTPUT_INTERFACE", "lo")]);
        EnableNatJob::dispatch();
    }

    private function refreshRules()
    {
        $rules = Rule::all();
        foreach ($rules as $rule) {
            DeleteIptablesRuleJob::dispatch($rule);
        }
        option(["INPUT_INTERFACE" => env("INPUT_INTERFACE", "lo")]);
        foreach ($rules as $rule) {
            CreateIptablesRuleJob::dispatch($rule);
            if (env("DIODE_IN", true)) {
                $rule->destination = env("DIODE_OUT_IP", "127.0.0.1");
                $rule->save();
            }
        }
    }

    private function refreshArp()
    {
      //TODO fix this
        $arpProcess = new Process("sudo " . base_path("app/Scripts") . "/datadiode.sh arp "
          . env("DIODE_OUT_MAC") . " " . env("DIODE_OUT_IP"));
        $arpProcess->mustRun();
    }
}
