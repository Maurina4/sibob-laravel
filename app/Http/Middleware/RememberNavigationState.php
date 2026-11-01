<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RememberNavigationState
{
    /**
     * Map of route names to their navigation state keys.
     *
     * @var array<string, string>
     */
    protected array $routeModuleMap = [
        'bmns.index' => 'bmns',
        'pengembalians.index' => 'pengembalians',
        'bmns-tik.index' => 'bmns-tik',
        'bmns-lab.index' => 'bmns-lab',
        'ba-perpindahan.index' => 'ba-perpindahan',
        'distribusi.index' => 'distribusi',
        'laporan-kerusakan.index' => 'laporan-kerusakan',
        'lokasis.index' => 'lokasis',
        'data-terhapus.index' => 'data-terhapus',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->ajax() && ! $request->wantsJson()) {
            $routeName = $request->route()?->getName();
            if ($routeName && isset($this->routeModuleMap[$routeName])) {
                $key = $this->routeModuleMap[$routeName];
                session()->put("nav_last_url.{$key}", $request->fullUrl());
            }
        }

        return $response;
    }
}
