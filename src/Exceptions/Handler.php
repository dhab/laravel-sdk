<?php

namespace DreamHack\SDK\Exceptions;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        $message = $e->getMessage();
        $headers = [];

        if (is_object($message)) {
            $message = $message->toArray();
        }

        $payload = [
            "status" => 500,
            "error" => $message
        ];
        if (is_callable(array($e, 'getStatusCode'))) {
            $payload["status"] = $e->getStatusCode();
        }
        if (app()->environment('local')) {
            $payload['file'] = $e->getFile();
            $payload['line'] = $e->getLine();
            $payload['trace'] = $e->getTrace();
        }
        if ($e instanceof AuthorizationException) {
            $payload['status'] = 403;
        }
        if ($e instanceof ModelNotFoundException) {
            $payload['status'] = 404;
            $payload['error'] = "Model Not Found";
        }
        if ($e instanceof ValidationException) {
            $payload['status'] = 422;
            $payload['error'] = $e->validator->errors()->getMessages();
        }
        if ($payload['status'] == 404 && empty($payload['error'])) {
            $payload['error'] = "Not Found";
        }
        if ($payload['status'] == 405 && empty($payload['error'])) {
            $payload['error'] = "Method Not Allowed";
        }
        if (is_callable([$e, 'getHeaders'])) {
            $headers = $e->getHeaders();
        }

        return response()->json($payload, $payload["status"])->withHeaders($headers);
    }
}
