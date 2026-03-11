<?php

namespace App\Exceptions\Seance;

use Exception;

class SessionEmargementActiveException extends Exception
{
    protected $message = "La session d'émargement associée est active.";
}
