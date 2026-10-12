<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Name the missing route in 404 messages, as Laravel 9+ does.
     * abort(404) inside a matched route keeps its empty message.
     *
     * @return \Throwable
     */
    protected function prepareException(Throwable $e)
    {
        $e = parent::prepareException($e);
        $request = request();

        if ($e instanceof NotFoundHttpException && $e->getMessage() === '' && $request->route() === null) {
            return new NotFoundHttpException(sprintf('The route %s could not be found.', $request->path()), $e);
        }

        return $e;
    }

    /**
     * Use the Laravel 12 error pages (app/Exceptions/views) ahead of Laravel 8's.
     * They live outside resources/ so Tailwind does not scan them, as in Laravel 12.
     *
     * @return void
     */
    protected function registerErrorViewPaths()
    {
        View::replaceNamespace('errors', collect(config('view.paths'))->map(function ($path) {
            return "{$path}/errors";
        })->push(__DIR__.'/views')->push(base_path('vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views'))->all());
    }

    /**
     * Render validation errors as JSON using the Laravel 9+ summary message
     * ("First error. (and N more errors)"), which the frontend shows to users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => $this->summarizeValidationErrors($exception),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    private function summarizeValidationErrors(ValidationException $exception): string
    {
        $messages = $exception->validator->errors()->all();

        if (! count($messages) || ! is_string($messages[0])) {
            return __('The given data was invalid.');
        }

        $message = array_shift($messages);

        if ($count = count($messages)) {
            $pluralized = $count === 1 ? 'error' : 'errors';

            $message .= ' '.trans_choice("(and :count more {$pluralized})", $count, compact('count'));
        }

        return $message;
    }
}
