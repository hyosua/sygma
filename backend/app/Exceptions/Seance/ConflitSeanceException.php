<?php

namespace App\Exceptions\Seance;

use Exception;
use Illuminate\Http\JsonResponse;

class ConflitSeanceException extends Exception
{
    public function __construct()
    {
        parent::__construct('Une séance existe déjà dans ce créneau.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
