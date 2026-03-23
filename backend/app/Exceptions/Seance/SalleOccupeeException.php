<?php

namespace App\Exceptions\Seance;

use Exception;
use Illuminate\Http\JsonResponse;

class SalleOccupeeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cette salle est occupée.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
