<?php
namespace Modules\WebService\App\Exceptions;

class ServiceUnavailableException extends WebServiceException
{
    public function __construct(string $message = 'Service unavailable')
    {
        parent::__construct($message, 503);
    }
}
