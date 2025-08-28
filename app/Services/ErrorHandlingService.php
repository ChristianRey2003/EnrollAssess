<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Centralized error handling service
 * 
 * Provides consistent error handling, logging, and user feedback
 * across the entire application.
 */
class ErrorHandlingService
{
    /**
     * Handle and format exceptions for user consumption
     *
     * @param \Exception $exception
     * @param string $operation
     * @param array $context
     * @param bool $isAjax
     * @return JsonResponse|RedirectResponse
     */
    public function handle(\Exception $exception, string $operation = 'operation', array $context = [], bool $isAjax = false)
    {
        // Log the error with context
        $this->logError($exception, $operation, $context);

        // Handle different types of exceptions
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception, $isAjax);
        }

        if ($exception instanceof HttpException) {
            return $this->handleHttpException($exception, $isAjax);
        }

        // Generic exception handling
        return $this->handleGenericException($exception, $operation, $isAjax);
    }

    /**
     * Log error with structured information
     *
     * @param \Exception $exception
     * @param string $operation
     * @param array $context
     * @return void
     */
    private function logError(\Exception $exception, string $operation, array $context = []): void
    {
        Log::error("Error during {$operation}", [
            'exception_type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'user_id' => auth()->id(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'trace' => config('app.debug') ? $exception->getTraceAsString() : 'Hidden in production'
        ]);
    }

    /**
     * Handle validation exceptions
     *
     * @param ValidationException $exception
     * @param bool $isAjax
     * @return JsonResponse|RedirectResponse
     */
    private function handleValidationException(ValidationException $exception, bool $isAjax)
    {
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        }

        return back()->withErrors($exception->errors())->withInput();
    }

    /**
     * Handle HTTP exceptions
     *
     * @param HttpException $exception
     * @param bool $isAjax
     * @return JsonResponse|RedirectResponse
     */
    private function handleHttpException(HttpException $exception, bool $isAjax)
    {
        $statusCode = $exception->getStatusCode();
        $message = $this->getHttpErrorMessage($statusCode);

        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }

        return back()->with('error', $message);
    }

    /**
     * Handle generic exceptions
     *
     * @param \Exception $exception
     * @param string $operation
     * @param bool $isAjax
     * @return JsonResponse|RedirectResponse
     */
    private function handleGenericException(\Exception $exception, string $operation, bool $isAjax)
    {
        $userMessage = config('app.debug') 
            ? $exception->getMessage() 
            : "An error occurred during {$operation}. Please try again.";

        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => $userMessage
            ], 500);
        }

        return back()->with('error', $userMessage);
    }

    /**
     * Get user-friendly HTTP error messages
     *
     * @param int $statusCode
     * @return string
     */
    private function getHttpErrorMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Bad request. Please check your input.',
            401 => 'You are not authorized to perform this action.',
            403 => 'Access denied. You do not have permission.',
            404 => 'The requested resource was not found.',
            422 => 'The provided data is invalid.',
            429 => 'Too many requests. Please try again later.',
            500 => 'Internal server error. Please try again.',
            503 => 'Service temporarily unavailable.',
            default => 'An unexpected error occurred.'
        };
    }

    /**
     * Create standardized error response for APIs
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public function apiErrorResponse(string $message, mixed $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create standardized success response for APIs
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function apiSuccessResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}
