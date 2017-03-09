<?php
namespace Jolt\Http\Controllers;

use Jolt\Models;
use Jolt\Exceptions;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function getIndex()
    {
        if (Models\User::IsLoggedIn()) return \redirect('/dash');
        return \View::make("welcome");
    }

    public function getDash()
    {
        return \View::make("dash");
    }

    public function getLogin()
    {
        if (Models\User::IsLoggedIn()) {
            return \redirect('/dash');
        }
        $redirect = parse_url(\URL::previous(), PHP_URL_PATH);
        if ($redirect === '/login') $redirect = null;
        return \View::make("login", ['redirect' => $redirect]);
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
            return \redirect($request->input('redirect')??'/dash');
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

        return \redirect($request->input('redirect')??'/dash');
    }

    public function getLogout()
    {
        Models\User::Me()->Logout();
        return \redirect('/');
    }
}
