<?php

namespace App\Exceptions\Emargement;

use Exception;
use Illuminate\Http\JsonResponse;

class JetonInvalideException extends Exception
{
    public function __construct()
    {
        parent::__construct("Jeton d'émargement invalide.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
