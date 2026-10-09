<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SHOW LOGIN
    |--------------------------------------------------------------------------
    */

    public function showLogin(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | IF ALREADY LOGGED IN
        |--------------------------------------------------------------------------
        |
        | Do not allow an already logged-in admin to return to
        | the login page.
        |
        */

        if ($request->session()->has('user_id')) {

            return redirect()
                ->route('home')
                ->header(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0'
                )
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }


        /*
        |--------------------------------------------------------------------------
        | SHOW LOGIN PAGE
        |--------------------------------------------------------------------------
        |
        | Prevent the browser from caching the login page.
        |
        */

        return response()
            ->view('auth.login')
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0'
            )
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE INPUT
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CLEAN USERNAME
        |--------------------------------------------------------------------------
        */

        $username = trim($request->username);


        /*
        |--------------------------------------------------------------------------
        | FIND USER
        |--------------------------------------------------------------------------
        */

        $user = User::whereRaw(
            'LOWER(username) = ?',
            [strtolower($username)]
        )->first();


        /*
        |--------------------------------------------------------------------------
        | USERNAME NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            return back()
                ->withInput($request->only('username'))
                ->with(
                    'error',
                    'Invalid username or password.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK PASSWORD
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($request->password, $user->password)) {

            return back()
                ->withInput($request->only('username'))
                ->with(
                    'error',
                    'Invalid username or password.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | REGENERATE SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | STORE USER LOGIN INFORMATION
        |--------------------------------------------------------------------------
        */

        $request->session()->put([
            'user_id' => $user->user_id,
            'username' => $user->username,
        ]);


        /*
        |--------------------------------------------------------------------------
        | SAVE SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->save();


        /*
        |--------------------------------------------------------------------------
        | REDIRECT TO HOME
        |--------------------------------------------------------------------------
        */

        return redirect()->route('home');
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK LOGIN
        |--------------------------------------------------------------------------
        */

        if (!$request->session()->has('user_id')) {

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please login first.'
                );
        }


        return view('dashboard');
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | LOGOUT USER
        |--------------------------------------------------------------------------
        */

        $request->session()->flush();


        /*
        |--------------------------------------------------------------------------
        | INVALIDATE SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->invalidate();


        /*
        |--------------------------------------------------------------------------
        | GENERATE NEW CSRF TOKEN
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerateToken();


        return redirect()
            ->route('login');
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */

    public function showChangePassword()
    {
        return view('auth.change-password');
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */

    public function changePassword(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | GET THE ADMIN ACCOUNT
        |--------------------------------------------------------------------------
        |
        | Change Password is available from the Login page, so the admin
        | does not have a login session yet. Therefore, do not use
        | session('user_id') here.
        |
        | The system uses the account stored in the users table.
        |
        */

        $user = User::first();


        /*
        |--------------------------------------------------------------------------
        | USER NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            return back()
                ->with(
                    'error',
                    'User account not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CURRENT PASSWORD
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CHECK CURRENT PASSWORD
        |--------------------------------------------------------------------------
        */

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {

            return back()
                ->withInput()
                ->withErrors([
                    'current_password' =>
                        'Current password is incorrect. Your password was not changed.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE NEW PASSWORD
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:16',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],

            'new_password_confirmation' => [
                'required',
                'string',
            ],

        ], [

            'new_password.required' =>
                'Please enter a new password.',

            'new_password.min' =>
                'Password must be at least 16 characters.',

            'new_password.regex' =>
                'Password must contain uppercase, lowercase, number and special character.',

            'new_password.confirmed' =>
                'The new password and confirmation password do not match.',

            'new_password_confirmation.required' =>
                'Please confirm your new password.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | EXTRA SERVER-SIDE PASSWORD MATCH CHECK
        |--------------------------------------------------------------------------
        |
        | This prevents the password from being changed if the two
        | password fields do not match, even if JavaScript is disabled.
        |
        */

        if (
            $request->new_password !==
            $request->new_password_confirmation
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'new_password_confirmation' =>
                        'The new password and confirmation password do not match.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE NEW PASSWORD
        |--------------------------------------------------------------------------
        */

        $user->password = Hash::make(
            $request->new_password
        );

        $user->save();


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Password changed successfully. You can now login using your new password.'
            );
    }
}
