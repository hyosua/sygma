<?php

namespace App\Exceptions\Emargement;

use Exception;

class JetonInvalideException extends Exception
{
    protected $message = "Jeton d'émargement invalide.";
}
