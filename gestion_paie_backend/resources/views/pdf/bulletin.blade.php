<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletin de paie — {{ $employe->prenom }} {{ $employe->nom }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #1F4E79; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #1F4E79; font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; color: #555; }
        .infos { width: 100%; margin-bottom: 20px; }
        .infos td { padding: 4px 0; vertical-align: top; }
        .infos .label { color: #555; width: 140px; }
        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.detail th { background: #1F4E79; color: #fff; text-align: left; padding: 6px 8px; font-size: 11px; }
        table.detail td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        table.detail tr:nth-child(even) { background: #F5F9FC; }
        .totaux { width: 50%; margin-left: auto; margin-top: 10px; }
        .totaux td { padding: 5px 8px; }
        .totaux .net { background: #1F4E79; color: #fff; font-weight: bold; font-size: 13px; }
        .footer { margin-top: 30px; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Bulletin de Paie</h1>
            <p>Période : {{ \Carbon\Carbon::create()->month($paie->mois)->translatedFormat('F') }} {{ $paie->annee }}</p>
        </div>
        <div style="text-align:right;">
            <p><strong>ENTSI</strong></p>
            <p>Généré le {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <table class="infos">
        <tr>
            <td class="label">Employé</td>
            <td><strong>{{ $employe->prenom }} {{ $employe->nom }}</strong></td>
            <td class="label">CIN</td>
            <td>{{ $employe->cin }}</td>
        </tr>
        <tr>
            <td class="label">Date d'embauche</td>
            <td>{{ \Carbon\Carbon::parse($employe->date_embauche)->format('d/m/Y') }}</td>
            <td class="label">Statut</td>
            <td>{{ ucfirst($paie->statut) }}</td>
        </tr>
    </table>

    <table class="detail">
        <thead>
            <tr><th>Élément</th><th style="text-align:right;">Montant (MAD)</th></tr>
        </thead>
        <tbody>
            <tr><td>Salaire de base</td><td style="text-align:right;">
                {{ number_format($paie->salaire_brut - $paie->primes->sum('pivot.montant_applique') - $paie->montant_heures_supplementaires, 2) }}
            </td></tr>
            @foreach ($paie->primes as $prime)
                <tr><td>{{ $prime->libelle }}</td><td style="text-align:right;">{{ number_format($prime->pivot->montant_applique, 2) }}</td></tr>
            @endforeach
            @if ($paie->nombre_heures_supplementaires > 0)
                <tr><td>Heures supplémentaires ({{ $paie->nombre_heures_supplementaires }}h × {{ $paie->taux_majoration_heures_sup * 100 }}%)</td>
                    <td style="text-align:right;">{{ number_format($paie->montant_heures_supplementaires, 2) }}</td></tr>
            @endif
            <tr><td><strong>Salaire brut</strong></td><td style="text-align:right;"><strong>{{ number_format($paie->salaire_brut, 2) }}</strong></td></tr>

            @foreach ($paie->cotisations as $cotisation)
                <tr><td>Cotisation {{ $cotisation->nom }}</td><td style="text-align:right;">- {{ number_format($cotisation->pivot->montant_calcule, 2) }}</td></tr>
            @endforeach
            <tr><td>Impôt sur le revenu</td><td style="text-align:right;">- {{ number_format($paie->impot_revenu, 2) }}</td></tr>

            @foreach ($paie->retenues as $retenue)
                <tr><td>Retenue — {{ $retenue->libelle }}</td><td style="text-align:right;">- {{ number_format($retenue->montant, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <table class="totaux">
        <tr><td>Total cotisations</td><td style="text-align:right;">{{ number_format($paie->total_cotisations, 2) }} MAD</td></tr>
        <tr><td>Total retenues</td><td style="text-align:right;">{{ number_format($paie->total_retenues, 2) }} MAD</td></tr>
        <tr class="net"><td>Salaire net à payer</td><td style="text-align:right;">{{ number_format($paie->salaire_net, 2) }} MAD</td></tr>
    </table>

    <div class="footer">
        Document généré automatiquement — Application de Gestion de Paie ENTSI.
    </div>
</body>
</html>
