<?php

namespace App\Exceptions\Seance;

use Exception;
use Illuminate\Http\JsonResponse;

class SessionEmargementActiveException extends Exception
{
    public function __construct()
    {
        parent::__construct("La session d'émargement associée est active.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
