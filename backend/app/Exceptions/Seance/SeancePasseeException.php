<?php

namespace App\Exceptions\Seance;

use Exception;

class SeancePasseeException extends Exception
{
    protected $message = 'Impossible de démarrer une session pour une séance passée.';
}
