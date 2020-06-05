<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->expectsJson()) {
            return $this->renderJson($e);
        }
        return parent::render($request, $e);
    }

    /**
     * Render an exception into an HTTP response as JSON.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderJson(Exception $e)
    {
        // The generic response returned in production
        $response = [
            'message' => 'Sorry, something went wrong.',
        ];
        // The default status code is 500 / Internal Server Error
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        // Handle known exception types
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
        } elseif ($e instanceof ModelNotFoundException) {
            $status = Response::HTTP_NOT_FOUND;
        } elseif ($e instanceof AuthorizationException) {
            $status = Response::HTTP_FORBIDDEN;
        } elseif ($e instanceof ValidationException) {
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            $response['errors'] = $e->errors();
        }
        // Append additional exception info when in debug mode
        if (env('APP_DEBUG')) {
            $response['message'] = $e->getMessage();
            $response['exception'] = get_class($e);
            $response['trace'] = $e->getTraceAsString();
        }
        // Return the JSON response
        return response()->json($response, $status);
    }
}
