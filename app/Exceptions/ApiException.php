<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class ApiException extends Exception
{
     public function __construct(string $message,protected int $status)
     {
          parent::__construct($message);
     }

     // laravel calls this automatically when the exception reaches its handler
     public function render(): JsonResponse
     {
          return response()->json([
               'message' =>   $this->getMessage()
          ], $this->status);
     }
}