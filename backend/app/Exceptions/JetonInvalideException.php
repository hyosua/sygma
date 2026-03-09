<?php

namespace App\Exceptions;

use Exception;

class JetonInvalideException extends Exception
{
    protected $message = "Jeton d'émargement invalide.";
}
