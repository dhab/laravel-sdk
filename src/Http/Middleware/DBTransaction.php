<?php
namespace DreamHack\SDK\Http\Middleware;
use Closure;
use DB;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
class DBTransaction
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
        DB::beginTransaction();
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $response;
    }
}