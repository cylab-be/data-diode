<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EditNetworkInterfaceRequest;
use App\NetworkInterface;

class InterfaceController extends Controller
{
    
    public function __construct()
    {
        $this->middleware("auth");
    }
    
    public function current()
    {
        return response()->json(NetworkInterface::getCurrentInterface(), 200);
    }
    
    public function retrieveAll()
    {
        return response()->json(NetworkInterface::getAllInterfaces(), 200);
    }

    public function edit(EditNetworkInterfaceRequest $request)
    {
        NetworkInterface::setCurrentInterface($request->input("id"));
        return response()->json(NetworkInterface::getCurrentInterface(), 200);
    }
}
