<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response|JsonResponse|RedirectResponse
    {
        if ($this->shouldReturnFriendlyErrorResponse($request, $e)) {
            $status = $this->resolveResponseStatus($e);
            $type = $status === 422 ? 'warning' : 'error';
            $title = $status === 422 ? 'Aviso' : 'Erro no sistema';
            $message = $this->buildFriendlyErrorMessage($e, $status);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'title' => $title,
                    'type' => $type,
                    'error' => true,
                ], $status);
            }

            $targetUrl = url()->previous();

            if ($targetUrl === $request->fullUrl()) {
                $targetUrl = route('dashboard');
            }

            return redirect()
                ->to($targetUrl)
                ->with('global_alert', [
                    'message' => $message,
                    'title' => $title,
                    'type' => $type,
                ]);
        }

        return parent::render($request, $e);
    }

    private function shouldReturnFriendlyErrorResponse(Request $request, Throwable $e): bool
    {
        if ($this->isBusinessRuleQueryException($e)) {
            return true;
        }

        if ($e instanceof ValidationException || $e instanceof HttpResponseException) {
            return false;
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 422) {
            return true;
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return false;
        }

        return ! app()->runningInConsole();
    }

    private function resolveResponseStatus(Throwable $e): int
    {
        if ($this->isBusinessRuleQueryException($e)) {
            return 422;
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    private function buildFriendlyErrorMessage(Throwable $e, int $status): string
    {
        if ($this->isBusinessRuleQueryException($e)) {
            return $this->resolveQueryExceptionMessage($e);
        }

        if ($status === 422 && $e->getMessage() !== '') {
            return $e->getMessage();
        }

        if (config('app.debug') && $e->getMessage() !== '') {
            return $e->getMessage();
        }

        return 'Ocorreu um erro interno ao processar sua solicitacao. Tente novamente.';
    }

    private function isBusinessRuleQueryException(Throwable $e): bool
    {
        return $e instanceof QueryException
            && $this->resolveQueryExceptionMessage($e) !== null;
    }

    private function resolveQueryExceptionMessage(QueryException $e): ?string
    {
        $errorInfo = $e->errorInfo ?? [];
        $sqlState = (string) ($errorInfo[0] ?? '');
        $driverMessage = mb_strtolower((string) ($errorInfo[2] ?? $e->getMessage()));

        if ($sqlState !== '23505') {
            return null;
        }

        if (str_contains($driverMessage, 'appointments_teacher_id_starts_at_unique')) {
            return 'Este professor ja possui um agendamento neste horario.';
        }

        if (str_contains($driverMessage, 'appointments_vehicle_id_starts_at_unique')) {
            return 'Este veiculo ja possui um agendamento neste horario.';
        }

        return 'Ja existe um agendamento conflitante para este horario.';
    }
}
