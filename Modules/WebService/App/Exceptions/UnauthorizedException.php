<?php
namespace Modules\WebService\App\Exceptions;

class UnauthorizedException extends WebServiceException
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message, 401);
    }
}
