<?php

use App\Contracts\OtpSenderInterface;
use App\Providers\AppServiceProvider;
use App\Services\Otp\EmailOtpSender;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AppServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(fn () => true);

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });


        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Forbidden.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Resource not found.',
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Not found.',
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Too many requests.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        });

        $exceptions->render(function (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        });

        $exceptions->render(function (\Throwable $e) {
            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
            $body = ['message' => $e->getMessage() ?: 'Server error.'];
            if (app()->hasDebugModeEnabled()) {
                $body['exception'] = get_class($e);
                $body['file'] = $e->getFile().':'.$e->getLine();
                $body['trace'] = collect($e->getTrace())
                    ->take(15)
                    ->map(fn ($f) => ($f['file'] ?? '?').':'.($f['line'] ?? '?').' '.($f['class'] ?? '').($f['type'] ?? '').($f['function'] ?? ''))
                    ->all();
            }

            return response()->json($body, $status);
        });

    })->create();
