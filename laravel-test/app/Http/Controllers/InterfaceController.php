<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NetworkInterface;

class InterfaceController extends Controller
{
    
    public function __construct()
    {
        $this->middleware("auth");
    }
    
    public function current()
    {
        option(["input_interface" => "enp0s3"]);
        if(option_exists("input_interface")){
            return response()->json(option("input_interface"), 200);
        } else {
            return response()->json(null, 204);
        }
    }
    
    public function retrieveAll()
    {
        return response()->json(NetworkInterface::getAllInterfaces(), 200);
    }

    public function edit(Request $request)
    {
        $rule->update($request->all());
        return response()->json($rule, 200);
    }
}
