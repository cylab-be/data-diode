<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\NetworkConfiguration;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:network';

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
        /*$config = new NetworkConfiguration();
        $config->setStatic();
        $config->setOption("address", "192.168.4.5");
        $config->setOption("netmask", "255.255.255.0");
        $config->setOption("gateway", "192.168.4.1");
        $config->saveInput();
        $config->saveOutput();
        NetworkConfiguration::get("enp0s3");*/
        NetworkConfiguration::getInput()->saveInput();
        NetworkConfiguration::getOutput()->saveOutput();
    }
}
