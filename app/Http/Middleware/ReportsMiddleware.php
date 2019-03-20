<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class ReportsMiddleware
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $manageThisReport    = "manage_{$request->segment(3)}";
        $canManageThisReport = auth()->user()->isAbleTo(['manage_everything', $manageThisReport]);
        if ($request->segment(2) === 'reports' && ! $canManageThisReport) {
            abort(404);
        }

        return $next($request);
    }
}
