<?php

namespace App\Exceptions\Seance;

use Exception;
use Illuminate\Http\JsonResponse;

class SeancePasseeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Impossible de démarrer une session pour une séance passée.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
