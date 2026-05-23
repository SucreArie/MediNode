<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CentreMedicauxController;
use App\Http\Controllers\ConsultationsController;
use App\Http\Controllers\PrescriptionsController;
use App\Http\Controllers\ExamensController;
use App\Http\Controllers\DossierController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'update']);

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AuthController::class, 'index']);
        Route::post('/users', [AuthController::class, 'store']);
        Route::get('/users/{user}', [AuthController::class, 'show']);
        Route::put('/users/{user}', [AuthController::class, 'updateUser']);
        Route::delete('/users/{user}', [AuthController::class, 'destroy']);

        Route::get('/centres-medicaux', [CentreMedicauxController::class, 'index']);
        Route::post('/centres-medicaux', [CentreMedicauxController::class, 'store']);
        Route::put('/centres-medicaux/{centre_medicaux}', [CentreMedicauxController::class, 'update']);
        Route::delete('/centres-medicaux/{centre_medicaux}', [CentreMedicauxController::class, 'destroy']);

    });

    // Consultations, Prescriptions, Examens - Admin, Doctor, Receptionist
    Route::middleware('role:admin,doctor,receptionist')->group(function () {
        Route::apiResource('consultations', ConsultationsController::class)->only(['index', 'store']);

        // Gestion des patients accessible par le personnel
        Route::get('/patients', [AuthController::class, 'index']);
        Route::post('/patients', [AuthController::class, 'store']);
        Route::get('/patients/{user}', [AuthController::class, 'show']);
        Route::put('/patients/{user}', [AuthController::class, 'updateUser']);

        Route::get('/doctors', [ConsultationsController::class, 'getDoctors']);
        Route::get('/centres-medicaux', [ConsultationsController::class, 'getCentresMedicaux']);
        Route::apiResource('prescriptions', PrescriptionsController::class)->only(['index', 'store']);
        Route::apiResource('examens', ExamensController::class)->only(['index', 'store']);
        Route::apiResource('dossiers', DossierController::class);
    });

    // Doctor routes
    Route::middleware('role:doctor')->prefix('doctor')->group(function () {
        Route::get('/records', function () { return response()->json(['message' => 'Dossiers médicaux (doctor)']); });
        Route::get('/consultations', function () { return response()->json(['message' => 'Consultations (doctor)']); });
        Route::get('/prescriptions', function () { return response()->json(['message' => 'Prescriptions (doctor)']); });
    });

    // Receptionist routes
    Route::middleware('role:receptionist')->prefix('receptionist')->group(function () {
        Route::get('/patients', function () { return response()->json(['message' => 'Patients (receptionist)']); });
        Route::get('/appointments', function () { return response()->json(['message' => 'Rendez-vous (receptionist)']); });
    });

    // Patient routes (own data)
    Route::middleware('role:patient')->prefix('patient')->group(function () {
        Route::get('/me', function (\Illuminate\Http\Request $request) { return response()->json(['message' => 'Profil patient', 'user' => $request->user()]); });
        Route::get('/my-records', function (\Illuminate\Http\Request $request) { return response()->json(['message' => 'Mes dossiers', 'user' => $request->user()]); });
    });

    
});
