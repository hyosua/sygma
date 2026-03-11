<?php

namespace App\Exceptions\Seance;

use Exception;

class ConflitSeanceException extends Exception
{
    protected $message = 'Une séance existe déjà dans ce créneau.';
}
