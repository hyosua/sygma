<?php

namespace App\Exceptions\Emargement;

use Exception;

class EtudiantNonInscritException extends Exception
{
    protected $message = "L'étudiant n'est pas inscrit à cette séance.";
}
