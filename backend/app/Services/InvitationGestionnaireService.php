<?php

namespace App\Services;

use App\Exceptions\Emargement\JetonInvalideException;
use App\Exceptions\Invitation\TokenDejaUtiliseException;
use App\Exceptions\Invitation\TokenExpireException;
use App\Mail\InvitationGestionnaireEmail;
use App\Models\InvitationGestionnaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationGestionnaireService
{
    protected const DUREE_VALIDITE_INVITATION = 48; // en heures

    public function creerInvitation(string $email): InvitationGestionnaire
    {
        $token = Str::random(32);
        $expiresAt = Carbon::now()->addHours(self::DUREE_VALIDITE_INVITATION);

        $invitation = InvitationGestionnaire::updateOrCreate(
            ['email' => $email],
            ['token' => $token, 'expires_at' => $expiresAt, 'used_at' => null, 'demande' => false]
        );

        Mail::to($email)->send(new InvitationGestionnaireEmail($invitation));

        return $invitation;
    }

    public function creerDemande(string $email): InvitationGestionnaire
    {
        $dejaEnAttente = InvitationGestionnaire::where('email', $email)
            ->where('demande', true)
            ->exists();

        if ($dejaEnAttente) {
            abort(422, 'Une demande existe déjà pour cet email.');
        }

        return InvitationGestionnaire::create([
            'email' => $email,
            'token' => Str::random(32),
            'expires_at' => null,
            'demande' => true,
        ]);
    }

    public function validerToken(string $token): InvitationGestionnaire
    {
        $invitation = InvitationGestionnaire::where('token', $token)->first();

        if (! $invitation || $invitation->demande) {
            throw new JetonInvalideException();
        }

        if ($invitation->expires_at?->isPast()) {
            throw new TokenExpireException();
        }

        if ($invitation->used_at) {
            throw new TokenDejaUtiliseException();
        }

        return $invitation;
    }

    public function inscrireViaToken(string $token, array $data): string
    {
        $invitation = $this->validerToken($token);

        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $invitation->email,
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole('gestionnaire');

        $invitation->used_at = Carbon::now();
        $invitation->save();

        return $user->createToken('api-token')->plainTextToken;
    }

    public function getInvitations(int $perPage = 10)
    {
        return InvitationGestionnaire::where('demande', false)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getDemandes()
    {
        return InvitationGestionnaire::where('demande', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
