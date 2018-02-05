<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Hash;
use Auth;

class ChangePasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware("auth");
    }

    public function get()
    {
        return view("auth.changepassword");
    }

    public function post(ChangePasswordRequest $request)
    {
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            return redirect()->back()
                ->with("error", "Your current password does"
                    ." not matches with the password you provided. Please try again.");
        }
        $user = Auth::user();
        $user->password = Hash::make($request->get("new-password"));
        $user->save();
        return redirect("/");
    }
}
