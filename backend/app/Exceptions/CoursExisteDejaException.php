<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CoursExisteDejaException extends Exception
{
    public function __construct()
    {
        parent::__construct('Le cours existe déjà.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
