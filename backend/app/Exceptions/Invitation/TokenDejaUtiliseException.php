<?php

namespace App\Exceptions\Invitation;

use Exception;
use Illuminate\Http\JsonResponse;

class TokenDejaUtiliseException extends Exception
{
    public function __construct()
    {
        parent::__construct('Token déjà utilisé.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
