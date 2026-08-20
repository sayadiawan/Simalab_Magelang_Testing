<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        // Log error details but don't expose sensitive info
        if ($this->shouldReport($exception)) {
            Log::error('Exception occurred', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => config('app.debug') ? $exception->getTraceAsString() : 'Trace hidden in production',
            ]);
        }
        
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // In production, don't expose detailed error messages
        if (!config('app.debug') && !$request->expectsJson()) {
            // Return generic error page for non-API requests
            if ($this->isHttpException($exception) && $exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                return response()->view('errors.' . $statusCode, [], $statusCode);
            }
            
            // For other exceptions, return 500 error page
            return response()->view('errors.500', [], 500);
        }
        
        return parent::render($request, $exception);
    }
}
