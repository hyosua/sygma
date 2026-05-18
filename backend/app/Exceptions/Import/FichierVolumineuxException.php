<?php

namespace App\Exceptions\Import;

use Exception;
use Illuminate\Http\JsonResponse;

class FichierVolumineuxException extends Exception
{
    public function __construct()
    {
        parent::__construct('Le fichier est trop volumineux. Taille maximale: 2MB.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
