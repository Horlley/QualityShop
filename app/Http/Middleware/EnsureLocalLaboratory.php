<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalLaboratory
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment(['local', 'testing']) && in_array($request->ip(), ['127.0.0.1', '::1'], true), 403, 'Laboratório disponível somente no computador local.');

        return $next($request);
    }
}
