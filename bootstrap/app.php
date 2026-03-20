<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    //
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (AuthorizationException $e, $request) {
      return redirect('/home')->with('warning', 'Você não tem permissão para acessar essa página.');
    });

    $exceptions->render(function (AccessDeniedHttpException $e, $request) {
      return redirect('/home')->with('warning', 'Você não tem permissão para acessar essa página.');
    });
  })->create();
