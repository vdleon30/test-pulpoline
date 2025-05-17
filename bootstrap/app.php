<?php

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if (App::environment() != "testing") {
                    App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                }
                return response()->json(['message' => __('Resource not found.')], 404);
            }
        });

        $exceptions->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                $modelName = class_basename($e->getModel());
                return response()->json(['message' => __(':modelName not found.', ['modelName' => $modelName])], 404);
            }
        });

        $exceptions->renderable(function (UnauthorizedException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if (App::environment() != "testing") {
                    App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                }
                return response()->json(['message' => __('User does not have the right permissions.')], 403);
            }
        });

        $exceptions->renderable(function (HttpException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if (App::environment() != "testing") {
                    App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                }
                return response()->json(['message' => __($e->getMessage())], $e->getStatusCode());
            }
        });

        $exceptions->renderable(function (HttpResponseException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if (App::environment() != "testing") {
                    App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                }
                return response()->json(['message' => __($e->getMessage())], 422);
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if (App::environment() != "testing") {
                    App::setLocale($request->header('Accept-Language') ?? config('app.locale'));
                }
                return $request->expectsJson()
                    ? response()->json(['message' => __($e->getMessage())], 401)
                    : redirect()->guest($e->redirectTo() ?? route('login'));
            }
        });


        // Catch-all for other exceptions in production to avoid leaking sensitive info
        $exceptions->renderable(function (Throwable $e, $request) {
            if (($request->is('api/*') || $request->wantsJson()) && !config('app.debug')) {
                return response()->json(['message' => __('Server Error')], 500);
            }
        });

        $exceptions->reportable(function (Throwable $e) {
            return response()->json(['message' => __('Server Error')], 500);
        });


    })->create();
