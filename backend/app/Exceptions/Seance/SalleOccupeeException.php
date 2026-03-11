<?php

namespace App\Exceptions\Seance;

use Exception;

class SalleOccupeeException extends Exception
{
    protected $message = 'Cette salle est occupée.';
}
