<?php

namespace DreamHack\SDK\Http\Middleware;

use M6Web\Component\Firewall\Firewall as FirewallClass;
use \Illuminate\Http\Request;
use Closure;

class Firewall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $firewall = new FirewallClass();
        $connAllowed = $firewall
        ->setDefaultState(false)
        ->addList(config('app.whitelist_ips'),'whitelist',true)
        ->setIpAddress($request->getClientIp())
        ->handle();
        if(!$connAllowed) {
            abort(403, "Forbidden, you do not have access to this resource.");
        }
        return $next($request);
    }
}
