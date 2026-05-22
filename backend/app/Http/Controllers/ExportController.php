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
                    'u.nom as user_nom',
                    'u.prenom as user_prenom',
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
            $dateDebut = $request->input('date_debut', Carbon::today()->format('Y-m-d'));
            $dateFin = $request->input('date_fin', $dateDebut);
            $statut = $request->input('statut');
            $type = $request->input('type');
            $groupeId = $request->input('groupe_id');
            $coursId = $request->input('cours_id');
            $etudiant = $request->input('etudiant');

            $query = DB::table('presences')
                ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
                ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
                ->join('users', 'presences.etudiant_id', '=', 'users.id')
                ->join('cours', 'seances.cours_id', '=', 'cours.id')
                ->leftJoin('groupes', 'users.groupe_id', '=', 'groupes.id')
                ->select(
                    'users.id',
                    'users.nom',
                    'users.prenom',
                    'users.email',
                    'presences.statut',
                    'cours.nom as cours_nom',
                    'seances.debut_a as presence_date',
                    'groupes.nom as groupe_nom'
                )
                ->when($statut, fn ($q) => $q->where('presences.statut', $statut))
                ->whereRaw('seances.debut_a::date BETWEEN ? AND ?', [$dateDebut, $dateFin])
                ->when($groupeId, fn ($q) => $q->where('users.groupe_id', $groupeId))
                ->when($coursId, fn ($q) => $q->where('seances.cours_id', $coursId))
                ->when($etudiant, fn ($q) => $q->where(function ($q2) use ($etudiant) {
                    $q2->whereRaw('LOWER(users.nom) LIKE ?', ['%' . strtolower($etudiant) . '%'])
                        ->orWhereRaw('LOWER(users.prenom) LIKE ?', ['%' . strtolower($etudiant) . '%']);
                }))
                ->orderBy('seances.debut_a', 'desc');

            $suffixe = $statut ?: 'tous';

            if ($type === 'E') {
                return Excel::download(
                    new StatutExport($dateDebut, $dateFin, $statut, $groupeId, $coursId, $etudiant),
                    "statut_{$suffixe}_{$dateDebut}_{$dateFin}.xlsx"
                );
            } elseif ($type === 'P') {
                $data = $query->get()->map(fn ($row) => (array) $row)->all();
                $pdf = Pdf::loadView('pdf.liste-personnes', [
                    'items' => $data,
                    'date' => "{$dateDebut} au {$dateFin}",
                    'statut' => $statut,
                    'Nombre' => count($data),
                ]);

                return $pdf->stream("statut_{$suffixe}_{$dateDebut}_{$dateFin}.pdf");
            }

            $data = $query->get()->map(fn ($row) => (array) $row)->all();

            return response()->json([
                'success' => true,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
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
