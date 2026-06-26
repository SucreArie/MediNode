<?php

namespace App\Http\Controllers;

use App\Models\Consultations; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Centre_medicaux;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;

class ConsultationsController extends Controller
{
    /**
     * Liste des consultations
     */
    public function index(Request $request)
    {
        try {
            $query = Consultations::with(['patient', 'medecin', 'centreMedical', 'prescriptions', 'examens']);

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            $consultations = $query->get();
            Log::info('Consultations chargées avec succès', ['count' => $consultations->count()]);

            return response()->json($consultations);
        } catch (\Exception $e) {
            Log::error('Erreur dans Consultations@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur serveur lors du chargement des consultations',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function getPatients()
    {
        try {
            $patients = \App\Models\Patient::select('id', 'firstName', 'lastName', 'name', 'date_naissance', 'gender')
                            ->get();
            Log::info('Patients chargés', ['count' => $patients->count()]);
            return response()->json($patients);
        } catch (\Exception $e) {
            Log::error('Erreur getPatients', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Erreur chargement patients'], 500);
        }
    }

    public function getDoctors()
    {
        try {
            $doctors = User::where('role', 'doctor')
                           ->select('id', 'name', 'email')
                           ->get();
            Log::info('Doctors chargés', ['count' => $doctors->count()]);
            return response()->json($doctors);
        } catch (\Exception $e) {
            Log::error('Erreur getDoctors', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Erreur chargement médecins'], 500);
        }
    }
    public function getCentresMedicaux()
    {
        $centres = Centre_medicaux::select('id', 'nom', 'ville', 'adresse', 'telephone')
                                 ->get();

        return response()->json($centres);
    }

    /**
     * Ajout d'une consultation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id'    => 'required|exists:patients,id',
            'medecin_id'    => 'required|exists:users,id',
            'centre_medical_id' => 'required|exists:centre_medicauxes,id',
            'date'          => 'required|date',
            'motif'         => 'required|string|max:255',
            'symptomes'     => 'nullable|string',
            'diagnostic'    => 'nullable|string|max:500',
            'traitement'    => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $consultation = Consultations::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Consultation créée avec succès',
            'data' => $consultation->load(['patient', 'medecin', 'centreMedical'])
        ], 201);
    }

    /**
     * Détails d'une consultation
     */
    public function show(Consultations $consultation)
    {
        try {
            // Chargement des relations au pluriel pour correspondre aux méthodes définies dans le modèle Consultations
            $data = $consultation->load(['patient', 'medecin', 'centreMedical', 'prescriptions', 'examens']);
            return response()->json($data);
        } catch (\Exception $e) {
            // Log de l'erreur réelle dans storage/logs/laravel.log
            Log::error("Erreur lors de la récupération de la consultation #{$consultation->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Erreur lors du chargement des relations de la consultation.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modification d'une consultation
     */
    public function update(Request $request, Consultations $consultation)
    {
        $validator = Validator::make($request->all(), [
            'date'          => 'sometimes|date',
            'motif'         => 'sometimes|string|max:255',
            'symptomes'     => 'nullable|string',
            'diagnostic'    => 'nullable|string|max:500',
            'traitement'    => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $consultation->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Consultation mise à jour avec succès',
            'data' => $consultation->load(['patient', 'medecin', 'centreMedical'])
        ]);
    }

    /**
     * Suppression d'une consultation
     */
    public function destroy(Consultations $consultation)
    {
        $consultation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consultation supprimée avec succès'
        ]);
    }
}
