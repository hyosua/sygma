<?php

namespace App\Http\Controllers;

use App\Exports\StatutExport;
use App\Models\Presence;
use Barryvdh\DomPDF\Facade\Pdf;  // AJOUTEZ CETTE LIGNE
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function getSessionByDate(Request $request)
    {
        // Vérifier si la date est fournie
        if (! $request->has('date')) {
            return response()->json([
                'error' => 'Date manquante',
                'message' => 'Veuillez fournir une date au format YYYY-MM-DD',
            ], 400);
        }

        try {
            $date = Carbon::parse($request->input('date'))->format('Y-m-d');

            $presences = DB::table('presences as p')
                ->join('sessions_emargement as se', 'p.session_emargement_id', '=', 'se.id')
                ->join('users as u', 'p.etudiant_id', '=', 'u.id')
                ->whereDate('p.created_at', $date)
                ->select(
                    'u.id as user_id',
                    'u.nom as user_nom',           // Ajout du nom
                    'u.prenom as user_prenom',      // Ajout du prénom
                    'u.email as user_email',
                    'u.created_at as user_created_at',
                    'u.updated_at as user_updated_at'
                )
                ->get();

            if ($presences->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'date' => $date,
                    'count' => $presences->count(),
                    'data' => $presences,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun étudiant trouvé pour cette date',
                    'date' => $date,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du traitement',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAbsencesToDay()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');

            $absences = DB::table('presences as p')
                ->join('sessions_emargement as se', 'p.session_emargement_id', '=', 'se.id')
                ->join('users as u', 'p.etudiant_id', '=', 'u.id')
                ->whereDate('p.created_at', $today)
                ->where('p.statut', 'absent')  // Correction: utilisez 'statut' au lieu de 'presence'
                ->select(
                    'u.id as user_id',
                    'u.nom as user_nom',
                    'u.prenom as user_prenom',
                    'u.email as user_email',
                    'p.id as presence_id',
                    'p.statut',
                    'p.created_at as presence_date',
                    'se.id as session_id',
                    'se.seance_id',
                    'se.created_at',
                )
                ->get();

            if ($absences->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'date' => $today,
                    'count' => $absences->count(),
                    'data' => $absences,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun étudiant absent trouvé pour aujourd\'hui',
                    'date' => $today,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du traitement',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStatutAndByDate(Request $request)
    {
        try {
            $date = $request->input('date', Carbon::today()->format('Y-m-d'));
            $statut = $request->input('statut');
            $type = $request->input('type');

            $absences = DB::select('
                SELECT
                    users.id,
                    users.nom,
                    users.prenom,
                    users.email,
                    cours.nom AS cours_nom,
                    seances.debut_a AS presence_date,
                    groupes.nom AS groupe_nom
                FROM presences
                JOIN sessions_emargement ON presences.session_emargement_id = sessions_emargement.id
                JOIN seances ON sessions_emargement.seance_id = seances.id
                JOIN users ON presences.etudiant_id = users.id
                JOIN cours ON seances.cours_id = cours.id
                LEFT JOIN groupes ON users.groupe_id = groupes.id
                WHERE seances.debut_a::date = ?
                  AND presences.statut = ?
            ', [$date, $statut]);

            $data = array_map(fn ($row) => (array) $row, $absences);

            if ($type === 'E') {
                return Excel::download(new StatutExport($date, $statut), "statut_{$statut}_{$date}.xlsx");
            } elseif ($type === 'P') {
                $nombre = count($data);

                $pdf = Pdf::loadView('pdf.liste-personnes', [
                    'items' => $data,
                    'date' => $date,
                    'statut' => $statut,
                    'Nombre' => $nombre,
                ]);

                return $pdf->stream("statut_{$statut}_{$date}.pdf");
            }

            return response()->json([
                'success' => true,
                'date' => $date,
                'statut' => $statut,
                'count' => count($data),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du traitement',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
