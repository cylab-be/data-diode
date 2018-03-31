<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateNetworkRequest;
use App\NetworkConfiguration;
use App\Jobs\RestartNetworkJob;

class NetworkController extends Controller
{

    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    public function get()
    {
        $config = env("DIODE_IN", true) ? NetworkConfiguration::getInput() : NetworkConfiguration::getOutput();
        return response()->json($config->toArray(), 200);
    }

    public function update(UpdateNetworkRequest $request)
    {
        $config = env("DIODE_IN", true) ? NetworkConfiguration::getInput() : NetworkConfiguration::getOutput();
        if ($request->get("mode") === "static") {
            $config->setStatic()
                ->setOption("address", $request->get("ip"))
                ->setOption("netmask", $request->get("netmask"));
        } else {
            $config->setDHCP();
        }
        if (env("DIODE_IN", true)) {
            $config->saveInput();
        } else {
            $config->saveOutput();
        }
        RestartNetworkJob::dispatch();
        return response()->json($config->toArray(), 200);
        ;
    }
}
