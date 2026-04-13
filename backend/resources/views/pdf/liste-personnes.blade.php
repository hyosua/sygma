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
        caption { margin-bottom: 1rem; font-weight: bold; }
        .Myimage { width: 80px; height: 80px; margin: 0 auto; display: block; }
    </style>
</head>
<body>
<img class="Myimage" src="{{ public_path('/image.png') }}" alt="Logo">
    <h1>Liste des personnes {{$statut}}</h1>
    <p>Date : {{ $date }}</p>
    <p>Statut : {{ $statut }}</p>
    <p>Nombre d'absent: {{ $Nombre }}</p>

    @if (!empty($items) && count($items) > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Cours</th>
                    <th>Date de présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['id'] ?? '' }}</td>
                        <td>{{ $item['nom'] ?? '' }}</td>
                        <td>{{ $item['prenom'] ?? '' }}</td>
                        <td>{{ $item['email'] ?? '' }}</td>
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
