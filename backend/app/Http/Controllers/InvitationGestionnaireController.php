<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvitationResource;
use App\Models\InvitationGestionnaire;
use App\Services\InvitationGestionnaireService;
use Illuminate\Http\Request;

class InvitationGestionnaireController extends Controller
{
    public function __construct(private InvitationGestionnaireService $invitGestionnaireService)
    {
    }

    public function inviter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $invitation = $this->invitGestionnaireService->creerInvitation($request->email);

        return response()->json($invitation, 201);
    }

    public function getInvitations()
    {
        $invitations = $this->invitGestionnaireService->getInvitations();

        return InvitationResource::collection($invitations);
    }

    public function annuler(InvitationGestionnaire $invitation)
    {
        $invitation->delete();

        return response()->noContent();
    }

    public function inscrire(Request $request, string $token)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $tokenInscrit = $this->invitGestionnaireService->inscrireViaToken($token, $data);

        return response()->json([
            'message' => 'Inscription réussie.',
            'token' => $tokenInscrit,
        ], 201);
    }

    public function renvoyer(InvitationGestionnaire $invitation)
    {
        $this->invitGestionnaireService->creerInvitation($invitation->email);

        return response()->json(['message' => 'Invitation renvoyée.']);
    }

    public function verifierToken(string $token)
    {
        $invitation = $this->invitGestionnaireService->validerToken($token);

        return response()->json($invitation);
    }

    public function demanderAcces(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $demande = $this->invitGestionnaireService->creerDemande($request->email);

        return response()->json($demande, 201);
    }

    public function getDemandes()
    {
        $demandes = $this->invitGestionnaireService->getDemandes();

        return InvitationResource::collection($demandes);
    }

    public function approuver(InvitationGestionnaire $invitation)
    {
        $this->invitGestionnaireService->creerInvitation($invitation->email);

        return response()->json(['message' => 'Invitation envoyée.']);
    }

    public function refuser(InvitationGestionnaire $invitation)
    {
        $invitation->delete();

        return response()->noContent();
    }
}
