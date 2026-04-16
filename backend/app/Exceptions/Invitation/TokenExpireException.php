<?php

namespace App\Exceptions\Invitation;

use Exception;
use Illuminate\Http\JsonResponse;

class TokenExpireException extends Exception
{
    public function __construct()
    {
        parent::__construct('Token d\'invitation expiré, veuillez demander une nouvelle invitation.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
