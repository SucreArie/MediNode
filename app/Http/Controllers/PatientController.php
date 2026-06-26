<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // LISTE
    public function index()
    {
        return response()->json(
            Patient::with('centreMedical')->get()
        );
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email',
            'phone' => 'nullable|string|max:20',
    
            'birthDate' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
    
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:20',
    
            'bloodType' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
    
            'emergencyName' => 'nullable|string|max:255',
            'emergencyPhone' => 'nullable|string|max:20',
    
            'insuranceId' => 'nullable|string|max:255',
    
            'condition' => 'nullable|string',
            'notes' => 'nullable|string',
    
            'status' => 'nullable|string|max:50',
    
            'centre_medical_id' => 'required|exists:centre_medicauxes,id',
        ]);
    
        $patient = Patient::create([
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
    
            'birthDate' => $validated['birthDate'] ?? null,
            'gender' => $validated['gender'] ?? null,
    
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'postalCode' => $validated['postalCode'] ?? null,
    
            'bloodType' => $validated['bloodType'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
    
            'emergencyName' => $validated['emergencyName'] ?? null,
            'emergencyPhone' => $validated['emergencyPhone'] ?? null,
    
            'insuranceId' => $validated['insuranceId'] ?? null,
    
            'condition' => $validated['condition'] ?? null,
            'notes' => $validated['notes'] ?? null,
    
            'status' => $validated['status'] ?? 'stable',
    
            'centre_medical_id' => $validated['centre_medical_id'],
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Patient enregistré avec succès',
            'data' => $patient
        ], 201);
    }

    // AFFICHER
    public function show($id)
    {
        return response()->json(
            Patient::with('centreMedical')->findOrFail($id)
        );
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $patient->update($request->all());

        return response()->json([
            'message' => 'Patient mis à jour',
            'patient' => $patient
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Patient::destroy($id);

        return response()->json([
            'message' => 'Patient supprimé'
        ]);
    }
}