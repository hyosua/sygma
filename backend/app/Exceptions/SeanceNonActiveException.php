<?php

namespace App\Exceptions;

use Exception;

class SeanceNonActiveException extends Exception
{
    protected $message = "La séance associée à ce jeton n'est pas active.";
}
