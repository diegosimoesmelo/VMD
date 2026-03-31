<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if ($user && $user->hasRole(...$roles)) {
            return $next($request);
        }

        $message = 'Voce nao tem permissao para acessar esta area.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'title' => 'Acesso negado',
                'type' => 'warning',
            ], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('global_alert', [
                'message' => $message,
                'title' => 'Acesso negado',
                'type' => 'warning',
            ]);
    }
}
