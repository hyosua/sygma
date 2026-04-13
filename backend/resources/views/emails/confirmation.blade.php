<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmez votre email</title>
</head>
<body style="font-family: sans-serif; padding: 32px;">
    <h2>Bienvenue sur Sygma !</h2>
    <p>Cliquez sur le lien ci-dessous pour confirmer votre adresse email et activer votre compte :</p>
    <p>
        <a href="{{ $lienVerification }}" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;">
            Confirmer mon email
        </a>
    </p>
    <p style="color:#888;font-size:13px;">Ce lien expire dans 24 heures. Si vous n'avez pas créé de compte, ignorez cet email.</p>
</body>
</html>
