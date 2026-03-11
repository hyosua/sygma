<?php

namespace App\Exceptions\Seance;

use Exception;

class SeanceNonActiveException extends Exception
{
    protected $message = "La séance associée n'est pas active.";
}
