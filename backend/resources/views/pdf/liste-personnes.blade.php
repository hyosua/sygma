<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des personnes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; margin-left: auto; margin-right: auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .logo { width: 80px; height: 80px; margin: 0 auto; display: block; }
    </style>
</head>
<body>
    <img class="logo" src="{{ public_path('sygma-logo-noir.png') }}" alt="Logo Sygma">

    @php
        $labelStatut = $statut === 'present' ? 'présents' : 'absents';
    @endphp

    <h1>Liste des étudiants {{ $labelStatut }}</h1>
    <p>Date : {{ $date }}</p>
    <p>Nombre d'étudiants : {{ $Nombre }}</p>

    @if (!empty($items) && count($items) > 0)
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Groupe</th>
                    <th>Cours</th>
                    <th>Date de séance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['nom'] ?? '' }}</td>
                        <td>{{ $item['prenom'] ?? '' }}</td>
                        <td>{{ $item['email'] ?? '' }}</td>
                        <td>{{ $item['groupe_nom'] ?? '—' }}</td>
                        <td>{{ $item['cours_nom'] ?? '' }}</td>
                        <td>{{ $item['presence_date'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Aucun résultat trouvé pour ce statut et cette date.</p>
    @endif
</body>
</html>
