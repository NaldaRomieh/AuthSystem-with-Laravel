<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Http\Requests;
use Illuminate\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Validation\ValidationException;

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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return response()->json(['message' => 'Too Many Requests'], 429);
        }

        if ($exception instanceof ValidationException) {
            return response()->json(['message' => 'Validation Error', 'errors' => $exception->errors()], 422);
        }

        if ($exception instanceof QueryException) {
            return response()->json(['message' => 'Database Query Error'], 500);
        }


        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'Resource Not Found'], 404);
        }

        // Default error response for other exceptions
        return response()->json(['message' => 'Server Error'], 500);
    }
}
