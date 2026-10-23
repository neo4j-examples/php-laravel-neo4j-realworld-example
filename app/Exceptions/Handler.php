<?php

namespace App\Exceptions;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use function class_basename;
use function explode;
use function response;

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
     *
     * @return void
     */
    public function register()
    {
        $this->shouldRenderJsonWhen(static fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                return self::errors(self::resourceName($previous->getModel()), 'not found', 404);
            }

            return null;
        });

        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            $model = Arr::last(Arr::where($request->route()?->parameters() ?? [], static fn ($x) => $x instanceof Model));

            return $model === null ? null : self::errors(self::resourceName($model::class), 'forbidden', 403);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return self::errors('token', 'is missing', 401);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = [];
        foreach ($exception->errors() as $key => $messages) {
            $field = explode('.', $key)[1] ?? $key;
            $errors[$field] = [...($errors[$field] ?? []), ...$messages];
        }

        return response()->json(['errors' => $errors], $exception->status);
    }

    private static function errors(string $field, string $message, int $status): JsonResponse
    {
        return response()->json(['errors' => [$field => [$message]]], $status);
    }

    private static function resourceName(string $model): string
    {
        return $model === User::class ? 'profile' : Str::lower(class_basename($model));
    }
}
