<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(['message' => __('Resource not found.')], 404);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $modelName = class_basename($e->getModel());
                return response()->json(['message' => __(':modelName not found.', ['modelName' => $modelName])], 404);
            }
        });

        $this->renderable(function (UnauthorizedException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(['message' => __('User does not have the right permissions.')], 403);
            }
        });

        $this->renderable(function (HttpException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(['message' => __($e->getMessage())], $e->getStatusCode());
            }
        });

        // Catch-all for other exceptions in production to avoid leaking sensitive info
        $this->renderable(function (Throwable $e, $request) {
            if (($request->is('api/*') || $request->wantsJson()) && !config('app.debug')) {
                return response()->json(['message' => __('Server Error')], 500);
            }
        });

        $this->reportable(function (Throwable $e) {
                return response()->json(['message' => __('Server Error')], 500);
        });
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => __($exception->getMessage())], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}