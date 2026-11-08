<?php

namespace App\Exceptions;

class OrderException extends ApiException
{
     public static function onlyNationalService(): self
     {
          return new self('Only National Service Personnel can do this.', 403);
     }

     public static function notAssigned(): self
     {
          return new self('This order is not assigned to you.', 403);
     }

     // 409 conflict: valid request, but clashes with the order's current state
     public static function alreadyCompleted(): self
     {
          return new self('This order has already been completed.', 409);
     }
}