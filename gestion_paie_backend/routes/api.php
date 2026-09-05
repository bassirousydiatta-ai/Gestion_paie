<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BulletinController;
use App\Http\Controllers\Api\ContratController;
use App\Http\Controllers\Api\CotisationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartementController;
use App\Http\Controllers\Api\EmployeController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\PaieController;
use App\Http\Controllers\Api\PosteController;
use App\Http\Controllers\Api\PrimeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Routes protégées (token Bearer requis via Sanctum)
|--------------------------------------------------------------------------
| L'autorisation fine (qui a le droit de faire quoi) est gérée par les
| Policies via $this->authorize() dans chaque contrôleur.
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Authentification ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- Tableau de bord ---
    Route::get('/dashboard/statistiques', [DashboardController::class, 'index']);

    // --- Employés ---
    Route::apiResource('employes', EmployeController::class);
    Route::patch('/employes/{employe}/archiver', [EmployeController::class, 'archiver']);
    Route::get('/employes/{employe}/attestation-travail', [EmployeController::class, 'attestationTravail']);
    Route::get('/employes/{employe}/anomalies', [EmployeController::class, 'anomalies']);
    Route::get('/employes/{employe}/bulletins', [BulletinController::class, 'index']);

    // --- Contrats ---
    Route::apiResource('contrats', ContratController::class);

    // --- Organisation ---
    Route::apiResource('departements', DepartementController::class);
    Route::apiResource('postes', PosteController::class);

    // --- Primes & Cotisations (catalogues) ---
    Route::apiResource('primes', PrimeController::class);
    Route::apiResource('cotisations', CotisationController::class);

    // --- Paies ---
    Route::apiResource('paies', PaieController::class)->only(['index', 'show', 'destroy']);
    Route::post('/paies/calculer', [PaieController::class, 'calculer']);
    Route::post('/paies/{paie}/valider', [PaieController::class, 'valider']);
    Route::get('/paies/{paie}/anomalies', [PaieController::class, 'anomalies']);

    // --- Bulletins ---
    Route::get('/bulletins/{bulletin}', [BulletinController::class, 'show']);
    Route::get('/bulletins/{bulletin}/pdf', [BulletinController::class, 'download']);
    Route::post('/paies/{paie}/bulletin', [BulletinController::class, 'store']);
    Route::delete('/bulletins/{bulletin}', [BulletinController::class, 'destroy']);

    // --- Utilisateurs (Admin) ---
    Route::apiResource('users', UserController::class);

    // --- Exports Excel / PDF ---
    Route::get('/exports/paies/xlsx', [ExportController::class, 'paiesExcel']);
    Route::get('/exports/paies/pdf', [ExportController::class, 'paiesPdf']);
    Route::get('/exports/employes/xlsx', [ExportController::class, 'employesExcel']);
});
