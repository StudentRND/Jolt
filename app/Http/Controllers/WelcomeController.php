<?php
namespace Jolt\Http\Controllers;

use Jolt\Models;
use Jolt\Exceptions;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function getIndex()
    {
        return \View::make("welcome");
    }

    public function getLogin()
    {
        if (Models\User::IsLoggedIn()) {
            return \redirect('/dash');
        }
        return \View::make("login");
    }

    public function postLogin(Request $request)
    {
        if (Models\User::IsLoggedIn()) {
            return \redirect('/dash');
        }
        
        if (!Models\User::VerifyLogin($request->input('username'), $request->input('password'))) {
            throw new Exceptions\UserInput("I couldn't find a user with that username and password.");
        } else {
            Models\User::where('username', '=', $request->input('username'))->first()->Login();
            return \redirect('/dash');
        }
    }

    public function postRegister(Request $request)
    {
        if (Models\User::IsLoggedIn()) {
            return \redirect('/dash');
        }

        $user = new Models\User;
        $user->username = $request->input('username');
        $user->password = $request->input('password');
        $user->email = $request->input('email');
        $user->save();

        $user->Login();

        return \redirect('/dash');
    }

    public function getLogout()
    {
        Models\User::Me()->Logout();
        return \redirect('/');
    }
}
