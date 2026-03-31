<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordWasChanged
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->requiresPasswordChange()) {
            return $next($request);
        }

        if ($request->routeIs('password.change.*', 'logout')) {
            return $next($request);
        }

        $message = 'No primeiro acesso, voce precisa definir uma nova senha para continuar.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'title' => 'Troca de senha obrigatoria',
                'type' => 'warning',
                'redirect_to' => route('password.change.edit'),
            ], 409);
        }

        return redirect()
            ->route('password.change.edit')
            ->with('global_alert', [
                'message' => $message,
                'title' => 'Troca de senha obrigatoria',
                'type' => 'warning',
            ]);
    }
}
