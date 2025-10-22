<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use App\Http\Responses\ApiResponse;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {

            $errors = [];

            foreach ($exception->errors() as $fieldErrors) {
                foreach ($fieldErrors as $fieldError) {
                    $errors[] = $fieldError;
                }
            }

            return ApiResponse::error(implode(PHP_EOL, $errors));
        }
    
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(sprintf("%s with ID=%s not found", class_basename($exception->getModel()), implode(", ", $exception->getIds())), 404);
        }

        if ($exception instanceof \InvalidArgumentException) {
            return ApiResponse::error($exception->getMessage());
        }

        return parent::render($request, $exception);
    }

}
