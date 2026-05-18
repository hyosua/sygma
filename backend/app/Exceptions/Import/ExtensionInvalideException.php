<?php

namespace App\Exceptions\Import;

use Exception;
use Illuminate\Http\JsonResponse;

class ExtensionInvalideException extends Exception
{
    public function __construct()
    {
        parent::__construct('Format de fichier non supporté.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
