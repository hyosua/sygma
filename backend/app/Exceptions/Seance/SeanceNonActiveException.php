<?php

namespace App\Exceptions\Seance;

use Exception;
use Illuminate\Http\JsonResponse;

class SeanceNonActiveException extends Exception
{
    public function __construct()
    {
        parent::__construct("La séance associée n'est pas active.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
