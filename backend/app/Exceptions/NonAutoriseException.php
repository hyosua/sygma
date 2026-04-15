<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class NonAutoriseException extends Exception
{
    public function __construct()
    {
        parent::__construct('Non autorisé.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 403);
    }
}
