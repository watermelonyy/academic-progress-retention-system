<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLogout
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK LAST ACTIVITY
        |--------------------------------------------------------------------------
        */

        if ($request->session()->has('last_activity')) {

            $inactiveTime =
                time() - $request->session()->get('last_activity');


            /*
            |--------------------------------------------------------------------------
            | LOG OUT AFTER 10 MINUTES OF INACTIVITY
            |--------------------------------------------------------------------------
            */

            if ($inactiveTime > 600) {

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
                    ->route('login')
                    ->with(
                        'error',
                        'You were logged out due to inactivity.'
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
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE LAST ACTIVITY
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'last_activity',
            time()
        );


        /*
        |--------------------------------------------------------------------------
        | CONTINUE REQUEST
        |--------------------------------------------------------------------------
        */

        $response = $next($request);


        /*
        |--------------------------------------------------------------------------
        | PREVENT BROWSER CACHING
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