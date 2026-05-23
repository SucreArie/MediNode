<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dossiers = Dossier::with(['patient', 'medecin', 'examen', 'prescription', 'consultation'])->get();
        return response()->json($dossiers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'description'     => 'required|string',
            'date'            => 'required|date',
            'patient_id'      => 'required|exists:users,id',
            'medecin_id'      => 'required|exists:users,id',
            'examen_id'       => 'nullable|exists:examens,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'consultation_id' => 'required|exists:consultations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $dossier = Dossier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Dossier créé avec succès',
            'data'    => $dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dossier $dossier)
    {
        return response()->json($dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dossier $dossier)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'sometimes|string|max:255',
            'description'     => 'sometimes|string',
            'date'            => 'sometimes|date',
            'patient_id'      => 'sometimes|exists:users,id',
            'medecin_id'      => 'sometimes|exists:users,id',
            'examen_id'       => 'nullable|exists:examens,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'consultation_id' => 'sometimes|exists:consultations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $dossier->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Dossier mis à jour avec succès',
            'data'    => $dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dossier $dossier)
    {
        $dossier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dossier supprimé avec succès'
        ]);
    }
}
