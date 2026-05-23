<?php

namespace App\Http\Controllers;

use App\Models\Prescriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrescriptionsController extends Controller
{
    /**
     * Liste des prescriptions
     */
    public function index()
    {
        $prescriptions = Prescriptions::with('consultation')->get();

        return response()->json([
            'success' => true,
            'data' => $prescriptions
        ]);
    }

    /**
     * Création d'une prescription
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'required|exists:consultations,id',
            'medicament'      => 'required|string|max:255',
            'dosage'          => 'required|string|max:100',
            'frequence'       => 'required|string|max:100',
            'duree_jours'     => 'required|integer|min:1',
            'observations'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $prescription = Prescriptions::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prescription créée avec succès',
            'data' => $prescription->load('consultation')
        ], 201);
    }

    /**
     * Modification d'une prescription
     */
    public function update(Request $request, Prescriptions $prescription)
    {
        $validator = Validator::make($request->all(), [
            'medicament'   => 'sometimes|string|max:255',
            'dosage'       => 'sometimes|string|max:100',
            'frequence'    => 'sometimes|string|max:100',
            'duree_jours'  => 'sometimes|integer|min:1',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $prescription->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prescription mise à jour avec succès',
            'data' => $prescription->load('consultation')
        ]);
    }

    /**
     * Suppression d'une prescription
     */
    public function destroy(Prescriptions $prescription)
    {
        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription supprimée avec succès'
        ]);
    }
}