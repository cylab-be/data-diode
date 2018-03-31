<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use App\Jobs\CreateIptablesRuleJob;
use App\Jobs\DeleteIptablesRuleJob;
use App\Jobs\DisableNatJob;
use App\Jobs\EnableNatJob;
use App\Jobs\RestartNetworkJob;
use App\Rule;
use App\NetworkConfiguration;

class ConfigRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the network configuration based on .env values';

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
        if (!env("CONFIG_DRY_RUN", false)) {
            RestartNetworkJob::dispatch();
        }
    }

    private function refreshConfig()
    {
        $internalConfig = new NetworkConfiguration();
        if (env("INTERNAL_DHCP", true)) {
            $internalConfig->setDHCP();
        } else {
            $internalConfig->setStatic()
            ->setOption("address", env("INTERNAL_IP"))
            ->setOption("netmask", env("INTERNAL_NETMASK"));
        }
        if (env("DIODE_IN", true)) {
            $internalConfig->setOption("up", "arp -s " . env("DIODE_OUT_IP", "127.0.0.1")
                . " " . env("DIODE_OUT_MAC"));
        }
        $externalConfig = new NetworkConfiguration();
        if (env("EXTERNAL_DEFAULT_DHCP", true)) {
            $externalConfig->setDHCP();
        } else {
            $externalConfig->setStatic()
            ->setOption("address", env("EXTERNAL_DEFAULT_IP"))
            ->setOption("netmask", env("EXTERNAL_DEFAULT_NETMASK"));
        }
        if (env("DIODE_IN", true)) {
            $internalConfig->saveOutput();
            $externalConfig->saveInput();
        } else {
            $internalConfig->saveInput();
            $externalConfig->saveOutput();
        }
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
}
