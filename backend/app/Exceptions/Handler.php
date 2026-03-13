<?php

namespace App\Exceptions;

use App\Exceptions\Emargement\DejaEmargeException;
use App\Exceptions\Emargement\EtudiantNonInscritException;
use App\Exceptions\Emargement\JetonExpireException;
use App\Exceptions\Emargement\JetonInvalideException;
use App\Exceptions\Seance\ConflitSeanceException;
use App\Exceptions\Seance\SalleOccupeeException;
use App\Exceptions\Seance\SeanceNonActiveException;
use App\Exceptions\Seance\SeancePasseeException;
use App\Exceptions\Seance\SessionEmargementActiveException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
// use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if (! $request->is('api/*')) {
                return;
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }

            $conflits = [
                ConflitSeanceException::class,
                SalleOccupeeException::class,
                SessionEmargementActiveException::class,
                DejaEmargeException::class,
            ];

            $reglesMetier = [
                JetonInvalideException::class,
                JetonExpireException::class,
                SeanceNonActiveException::class,
                EtudiantNonInscritException::class,
                SeancePasseeException::class,
            ];

            foreach ($conflits as $classe) {
                if ($e instanceof $classe) {
                    return response()->json(['message' => $e->getMessage()], 409);
                }
            }

            foreach ($reglesMetier as $classe) {
                if ($e instanceof $classe) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }
            }

            // TODO (mise en prod) : catch-all OWASP — masquer les détails d'erreur au client
            // Décommenter avant déploiement (APP_DEBUG=false dans .env.production)
            // Log::error($e);
            // return response()->json(['message' => 'Une erreur inattendue est survenue.'], 500);
        });
    }
}
