<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateNetworkRequest;
use App\NetworkConfiguration;
use App\Jobs\RestartNetworkJob;

/**
 * Controller used for network configuration operations
 */
class NetworkController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(["auth", "default-password"]);
    }

    /**
     * Gets the current configuration of the external NIC of the diode
     * @return mixed the json response
     */
    public function get()
    {
        $config = env("DIODE_IN", true) ? NetworkConfiguration::getInput() : NetworkConfiguration::getOutput();
        return response()->json($config->toArray(), 200);
    }

    /**
     * Updates the current configuration of the external NIC of the diode
     * @param  UpdateNetworkRequest $request The request made by the user
     * @return mixed                         The json response
     */
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
