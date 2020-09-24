<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Resource not found'
            ], 404);
        }

        if($request->wantsJson()){
            return $this->handleApiException($request, $exception);
        }else{
            $err = parent::render($request, $exception);
        }

        return $err;
    }

    private function handleApiException($request, Exception $exception){

        $exception = $this->prepareException($exception);

        if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
            $exception = $exception->getResponse();
        }
    
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }
    
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }
    
        return $this->customApiResponse($exception);
   }

   private function customApiResponse($exception)
{
    if (method_exists($exception, 'getStatusCode')) {
        $statusCode = $exception->getStatusCode();
    } else {
        $statusCode = 500;
    }

    $response = [];

    switch ($statusCode) {
        case 401:
            $response['message'] = 'Unauthorized';
            break;
        case 403:
            $response['message'] = 'Forbidden';
            break;
        case 404:
            $response['message'] = 'Not Found';
            break;
        case 405:
            $response['message'] = 'Method Not Allowed';
            break;
        case 422:
            $response['message'] = $exception->original['message'];
            $response['errors'] = $exception->original['errors'];
            break;
        default:
            $response['message'] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
            break;
    }

    if (config('app.debug')) {
        //$response['trace'] = $exception->getTrace();
        $response['code'] = $exception->getCode();
    }

    $response['status'] = $statusCode;

    return response()->json($response, $statusCode);
}

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
