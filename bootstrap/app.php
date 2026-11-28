<?php

use App\Http\Middleware\SetAppLanguage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan middleware SetAppLanguage ke kelompok web
        $middleware->web(append: [
            SetAppLanguage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Render custom error page dari folder resources/views/admin/errors/ untuk request Web
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();

            if (view()->exists("admin.errors.{$status}")) {
                return response()->view("admin.errors.{$status}", ['exception' => $e], $status);
            }

            return null;
        });
    })->create();
