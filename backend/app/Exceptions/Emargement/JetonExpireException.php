<?php

namespace App\Exceptions\Emargement;

use Exception;

class JetonExpireException extends Exception
{
    protected $message = 'QR Code expiré, veuillez scanner le nouveau.';
}
