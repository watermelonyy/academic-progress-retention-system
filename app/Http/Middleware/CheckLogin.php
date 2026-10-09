<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK IF ADMIN IS LOGGED IN
        |--------------------------------------------------------------------------
        */

        if (!$request->session()->has('user_id')) {

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please login first.'
                )
                ->header(
                    'Cache-Control',
                    'no-store, no-cache, must-revalidate, max-age=0'
                )
                ->header(
                    'Pragma',
                    'no-cache'
                )
                ->header(
                    'Expires',
                    '0'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | ALLOW REQUEST
        |--------------------------------------------------------------------------
        */

        $response = $next($request);


        /*
        |--------------------------------------------------------------------------
        | PREVENT BROWSER FROM CACHING ADMIN PAGES
        |--------------------------------------------------------------------------
        */

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        $response->headers->set(
            'Pragma',
            'no-cache'
        );

        $response->headers->set(
            'Expires',
            '0'
        );


        return $response;
    }
}