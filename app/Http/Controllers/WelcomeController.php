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

    public function getLogin(Request $request)
    {
        if (Models\User::IsLoggedIn()) {
            return \redirect('/dash');
        }
        $redirect = parse_url(\URL::previous(), PHP_URL_PATH);
        if ($redirect === '/login') $redirect = null;
        if (!isset($redirect) && $request->session()->has('redirect')) $redirect = $request->session()->get('redirect');
        if (isset($redirect)) $request->session()->put('redirect', $redirect);
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

    public function getForgot(Request $request)
    {
        if ($request->jwt) {
            $user = Models\User::FromJwt($request->jwt, 'reset');
            if ($user) {
                $user->Login();
                return \redirect('/dash');
            }
        }

        return \View::make("forgot");
    }

    public function postForgot(Request $request)
    {
        $user = Models\User::where('username', '=', $request->lookup)->orWhere('email', '=', $request->lookup)->first();
        if ($user) {
            $jwt = $user->MintJwt('reset');

            \Mail::send('emails/reset', ['user' => $user, 'reset' => url('/forgot?jwt='.$jwt)], function ($m) use ($user) {
                $m->from('jolt@srnd.org', 'Jolt');
                $m->to($user->email, $user->username)->subject('Password Reset');
            });

            $emailHint = substr($user->email, 0, 1).'***@'.substr($user->email, strpos($user->email, '@')+1, 2).'**.***';
            throw new Exceptions\UserInput("Success! Your password reset link has been emailed to $emailHint");
        }
        throw new Exceptions\UserInput("Couldn't find anyone with that username/password.");
    }

    public function getLogout()
    {
        Models\User::Me()->Logout();
        return \redirect('/');
    }
}
