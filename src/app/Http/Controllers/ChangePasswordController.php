<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Hash;
use Auth;

/**
 * Controller used for changing the password
 */
class ChangePasswordController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * Gets the view for changing the password
     * @return mixed the view
     */
    public function get()
    {
        return view("auth.changepassword");
    }

    /**
     * Handles post request.
     * Checks if the password can be changed and changes it
     * @param  ChangePasswordRequest $request The request made by the user
     * @return mixed Redirects the user to the home page when password changed successfully. Displays errors otherwise
     */
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
