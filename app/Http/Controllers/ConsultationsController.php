<?php

namespace App\Http\Controllers;

use App\Models\Consultations; // Correction : Utilise le nom singulier du Model
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Centre_medicaux; // Ajout de l'import pour le modèle Centre_medicaux

class ConsultationsController extends Controller
{
    /**
     * Liste des consultations
     */
    public function index()
    {
        $consultations = Consultations::with(['patient', 'medecin', 'centreMedical'])->get();
        
        return response()->json($consultations);
    }

    /**
     * Récupérer la liste des patients pour le formulaire
     */
    public function getPatients()
    {
        $patients = User::where('role', 'patient')
                        ->get();

        return response()->json($patients);
    }

    /**
     * Récupérer la liste des médecins pour le formulaire
     */
    public function getDoctors()
    {
        $doctors = User::where('role', 'doctor')
                       ->select('id', 'name', 'email')
                       ->get();

        return response()->json($doctors);
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
            'patient_id'    => 'required|exists:users,id',
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