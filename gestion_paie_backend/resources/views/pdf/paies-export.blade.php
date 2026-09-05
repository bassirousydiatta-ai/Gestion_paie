<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export des paies</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        .header { border-bottom: 2px solid #1F4E79; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { color: #1F4E79; font-size: 16px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1F4E79; color: #fff; text-align: left; padding: 5px 6px; font-size: 9px; }
        td { padding: 5px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:nth-child(even) { background: #F5F9FC; }
        .totaux { margin-top: 15px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Export des fiches de paie</h1>
        <p>{{ $periodeLabel }} — généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employé</th><th>CIN</th><th>Période</th>
                <th style="text-align:right;">Brut</th>
                <th style="text-align:right;">Cotisations</th>
                <th style="text-align:right;">IR</th>
                <th style="text-align:right;">Retenues</th>
                <th style="text-align:right;">Net</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paies as $paie)
                <tr>
                    <td>{{ $paie->employe->prenom }} {{ $paie->employe->nom }}</td>
                    <td>{{ $paie->employe->cin }}</td>
                    <td>{{ str_pad($paie->mois, 2, '0', STR_PAD_LEFT) }}/{{ $paie->annee }}</td>
                    <td style="text-align:right;">{{ number_format($paie->salaire_brut, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($paie->total_cotisations, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($paie->impot_revenu, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($paie->total_retenues, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($paie->salaire_net, 2) }}</td>
                    <td>{{ ucfirst($paie->statut) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="totaux">Total salaire net : {{ number_format($paies->sum('salaire_net'), 2) }} MAD — {{ $paies->count() }} fiche(s)</p>
</body>
</html>
