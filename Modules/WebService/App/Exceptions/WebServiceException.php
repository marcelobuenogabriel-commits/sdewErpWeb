<?php
namespace Modules\WebService\App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class WebServiceException extends Exception
{
    protected int $status;

    public function __construct(string $message = 'Webservice error', int $status = 500, Throwable $previous = null)
    {
        parent::__construct($message, $status, $previous);
        $this->status = $status;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->status ?: 500);
    }
}
