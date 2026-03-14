<?php

namespace App\Exceptions\Emargement;

use Exception;
use Illuminate\Http\JsonResponse;

class EtudiantNonInscritException extends Exception
{
    public function __construct()
    {
        parent::__construct("L'étudiant n'est pas inscrit à cette séance.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
