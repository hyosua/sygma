<?php

namespace App\Exceptions\Emargement;

use Exception;
use Illuminate\Http\JsonResponse;

class DejaEmargeException extends Exception
{
    public function __construct()
    {
        parent::__construct('Vous avez déjà émargé pour cette séance.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
