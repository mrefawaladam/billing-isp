<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
    public function render($request, Throwable $exception)
    {
        // Handle API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException($request, Throwable $exception)
    {
        // Validation errors
        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError(
                $exception->errors(),
                $exception->getMessage()
            );
        }

        // Not found
        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::notFound('Resource not found');
        }

        // Unauthorized
        if ($exception instanceof UnauthorizedHttpException) {
            return ApiResponse::unauthorized('Unauthorized');
        }

        // Default error
        $message = config('app.debug')
            ? $exception->getMessage()
            : 'An error occurred';

        return ApiResponse::error($message, 500);
    }
}

