<?php

namespace App\Exceptions;

use Exception;

class DejaEmargeException extends Exception
{
    protected $message = 'Vous avez déjà émargé pour cette séance.';
}
