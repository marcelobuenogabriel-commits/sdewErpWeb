<?php
namespace Modules\WebService\App\Exceptions;

class NotFoundException extends WebServiceException
{
    public function __construct(string $message = 'Not found')
    {
        parent::__construct($message, 404);
    }
}
