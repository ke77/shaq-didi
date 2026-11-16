<?php

namespace App\Exceptions;

class OrderException extends ApiException
{
    public static function onlyNationalService(): self
    {
        return new self('Only National Service Personnel can do this.', 403);
    }

    public static function onlyPermanentStaff(): self
    {
        return new self('Only Permanent Staff can cancel orders.', 403);
    }

    public static function notAssigned(): self
    {
        return new self('This order is not assigned to you.', 403);
    }

    public static function notPlacedByUser(): self
    {
        return new self('You can only cancel orders you placed.', 403);
    }

    public static function cannotCancel(): self
    {
        return new self('Only pending orders can be cancelled.', 409);
    }

    // 409 conflict: valid request, but clashes with the order's current state
    public static function alreadyCompleted(): self
    {
        return new self('This order has already been completed.', 409);
    }

    public static function alreadyCancelled(): self
    {
        return new self('This order was cancelled and can no longer be completed.', 409);
    }
}
