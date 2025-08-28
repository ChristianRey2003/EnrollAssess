<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Base Controller with common functionality for all controllers
 * 
 * Provides standardized response methods, error handling,
 * and common utilities used across the application.
 */
abstract class BaseController extends Controller
{
    /**
     * Default pagination size
     */
    protected int $defaultPaginationSize = 15;

    /**
     * Return a successful JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error occurred', mixed $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a successful redirect with flash message
     *
     * @param string $route
     * @param string $message
     * @param string $type
     * @param array $routeParams
     * @return RedirectResponse
     */
    protected function successRedirect(string $route, string $message = 'Operation completed successfully', string $type = 'success', array $routeParams = []): RedirectResponse
    {
        return redirect()->route($route, $routeParams)->with($type, $message);
    }

    /**
     * Return an error redirect with flash message
     *
     * @param string $message
     * @param string $type
     * @return RedirectResponse
     */
    protected function errorRedirect(string $message = 'An error occurred', string $type = 'error'): RedirectResponse
    {
        return back()->withInput()->with($type, $message);
    }

    /**
     * Get pagination size from request or use default
     *
     * @param Request $request
     * @param int|null $default
     * @return int
     */
    protected function getPaginationSize(Request $request, ?int $default = null): int
    {
        $size = $request->get('per_page', $default ?? $this->defaultPaginationSize);
        
        // Ensure pagination size is within reasonable bounds
        return max(5, min(100, (int) $size));
    }

    /**
     * Apply search filters to query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param array $searchableFields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearch($query, Request $request, array $searchableFields = [])
    {
        if ($request->filled('search') && !empty($searchableFields)) {
            $searchTerm = $request->get('search');
            
            $query->where(function ($q) use ($searchableFields, $searchTerm) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'like', "%{$searchTerm}%");
                }
            });
        }

        return $query;
    }

    /**
     * Handle exceptions with consistent logging and user feedback
     *
     * @param \Exception $exception
     * @param string $operation
     * @param bool $isAjax
     * @return JsonResponse|RedirectResponse
     */
    protected function handleException(\Exception $exception, string $operation = 'operation', bool $isAjax = false)
    {
        // Log the exception for debugging
        Log::error("Error during {$operation}", [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Handle validation exceptions specially
        if ($exception instanceof ValidationException) {
            if ($isAjax) {
                return $this->errorResponse('Validation failed', $exception->errors(), 422);
            }
            return back()->withErrors($exception->errors())->withInput();
        }

        // Generic error messages for users
        $userMessage = config('app.debug') 
            ? $exception->getMessage() 
            : "An error occurred during {$operation}. Please try again.";

        if ($isAjax) {
            return $this->errorResponse($userMessage, null, 500);
        }

        return $this->errorRedirect($userMessage);
    }

    /**
     * Validate request data with custom rules
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [], array $attributes = []): array
    {
        return $request->validate($rules, $messages, $attributes);
    }

    /**
     * Check if request expects JSON response
     *
     * @param Request $request
     * @return bool
     */
    protected function expectsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }
}
