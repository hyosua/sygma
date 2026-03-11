<?php

namespace App\Exceptions\Emargement;

use Exception;

class DejaEmargeException extends Exception
{
    protected $message = 'Vous avez déjà émargé pour cette séance.';
}
