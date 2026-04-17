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
            ['token' => $token, 'expires_at' => $expiresAt, 'used_at' => null]
        );

        Mail::to($email)->send(new InvitationGestionnaireEmail($invitation));

        return $invitation;
    }

    public function validerToken(string $token): InvitationGestionnaire
    {
        $invitation = InvitationGestionnaire::where('token', $token)->first();

        if (! $invitation) {
            throw new JetonInvalideException();
        }

        if ($invitation->expires_at->isPast()) {
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
        return InvitationGestionnaire::orderBy('created_at', 'desc')->paginate($perPage);
    }
}
