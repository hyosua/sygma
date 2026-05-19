<?php

namespace App\Services;

use App\Mail\ConfirmationEmail;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;

class ImportCompteService
{
    public function importer(UploadedFile $fichier): array
    {
        $lignes = $this->parser($fichier);
        $erreurs = [];

        // étape 1: on valide si toute les lignes sont valides
        foreach ($lignes as $i => $ligne) {
            $numLigne = $i + 1;
            if ($i === 0) {
                continue;
            } // ignore l'en tête

            $erreur = $this->traiterLigne($ligne, $numLigne);
            if ($erreur) {
                $erreurs[] = $erreur;
            }
        }

        // étape 2: si erreurs: on n'insère rien
        if (! empty($erreurs)) {
            return ['success' => 0, 'erreurs' => $erreurs];
        }

        // création de comptes
        $success = DB::transaction(function () use ($lignes) {
            $compteur = 0;
            foreach ($lignes as $i => $ligne) {
                if ($i === 0) {
                    continue;
                }

                $nom = $ligne[0];
                $prenom = $ligne[1];
                $email = $ligne[2];
                $role = $ligne[3];
                $nomGroupe = isset($ligne[4]) ? trim($ligne[4]) : null;

                $tokenVerification = Str::uuid()->toString();

                $donnees = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => Hash::make(Str::random(12)),
                    'verification_token' => $tokenVerification,
                    'verification_token_expires_at' => now()->addHours(24),
                ];

                if ($nomGroupe) {
                    $groupe = Groupe::whereRaw('LOWER(nom) = LOWER(?)', [$nomGroupe])->first()
                        ?? Groupe::create(['nom' => $nomGroupe]);
                    $donnees['groupe_id'] = $groupe->id;
                }

                $user = User::create($donnees);
                $user->assignRole($role);

                $lienVerification = config('app.frontend_url') . '/email/verify/' . $tokenVerification;
                Mail::to($user->email)->send(new ConfirmationEmail($lienVerification));

                $compteur++;
            }

            return $compteur;
        });

        return ['success' => $success, 'erreurs' => $erreurs];
    }

    private function parser(UploadedFile $fichier): array
    {
        $cheminReel = $fichier->getRealPath();
        $extension = $fichier->getClientOriginalExtension();

        if ($extension === 'csv') {
            return $this->parseCSV($cheminReel);
        }

        return $this->parseExcel($cheminReel);
    }

    private function parseCSV(string $chemin): array
    {
        $reader = new CsvReader();
        $spreadsheet = $reader->load($chemin);
        $feuille = $spreadsheet->getActiveSheet();

        return $feuille->toArray();
    }

    private function parseExcel(string $chemin): array
    {
        $reader = new ExcelReader();
        $spreadsheet = $reader->load($chemin);
        $feuille = $spreadsheet->getActiveSheet();

        return $feuille->toArray();
    }

    private function traiterLigne(array $donnees, int $numLigne): ?string
    {
        $nbColonnes = count($donnees);
        if ($nbColonnes < 4) {
            return "La ligne $numLigne doit contenir au moins 4 colonnes.";
        }

        $email = $donnees[2];
        $role = $donnees[3];

        if ($donnees[0] === null || $donnees[1] === null || $donnees[2] === null || $donnees[3] === null) {
            return "La ligne $numLigne contient des champs vides.";
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "L'email $email n'est pas valide.";
        }

        if (User::where('email', $email)->exists()) {
            return "L'email $email est déjà utilisé.";
        }

        if (! in_array($role, ['etudiant', 'enseignant'])) {
            return "Le rôle doit être 'etudiant' ou 'enseignant'.";
        }

        return null;
    }
}
