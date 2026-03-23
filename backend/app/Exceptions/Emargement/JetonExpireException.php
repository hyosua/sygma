<?php

namespace App\Exceptions\Emargement;

use Exception;
use Illuminate\Http\JsonResponse;

class JetonExpireException extends Exception
{
    public function __construct()
    {
        parent::__construct('QR Code expiré, veuillez scanner le nouveau.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
