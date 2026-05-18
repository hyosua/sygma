<?php

namespace App\Http\Controllers;

use App\Services\ImportCompteService;
use Illuminate\Http\Request;

class ImportCompteController extends Controller
{
    public function __construct(private ImportCompteService $importCompteService)
    {
    }

    public function importer(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        $resultat = $this->importCompteService->importer($request->file('fichier'));

        return response()->json($resultat);
    }
}
