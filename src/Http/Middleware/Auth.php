<?php
namespace DreamHack\SDK\Http\Middleware;

use Closure;
use DB;
use DreamHack\SDK\Auth\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        // dd(get_class_methods(app()));
        $user = app()->make(User::class);
        if($user->isLoggedIn()) {
            AuthFacade::guard()->setUser($user);
        }
        return $next($request);
    }
}
