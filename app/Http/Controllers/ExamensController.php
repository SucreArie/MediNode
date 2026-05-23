<?php

namespace App\Http\Controllers;

use App\Models\Examens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamensController extends Controller
{
    /**
     * Liste des examens
     */
    public function index()
    {
        $examens = Examens::with('consultation')->get();

        return response()->json([
            'success' => true,
            'data' => $examens
        ]);
    }

    /**
     * Création d'un examen
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'required|exists:consultations,id',
            'type_examen'     => 'required|string|max:100',
            'laboratoire'     => 'nullable|string|max:150',
            'urgence'         => 'boolean',
            'resultat'        => 'nullable|string',
            'date_resultat'   => 'nullable|date',
            'fichier_joint'   => 'nullable|string', // chemin du fichier si upload
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $examen = Examens::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Examen créé avec succès',
            'data' => $examen->load('consultation')
        ], 201);
    }

    /**
     * Modification d'un examen
     */
    public function update(Request $request, Examens $examen)
    {
        $validator = Validator::make($request->all(), [
            'type_examen'   => 'sometimes|string|max:100',
            'laboratoire'   => 'nullable|string|max:150',
            'urgence'       => 'boolean',
            'resultat'      => 'nullable|string',
            'date_resultat' => 'nullable|date',
            'fichier_joint' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $examen->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Examen mis à jour avec succès',
            'data' => $examen->load('consultation')
        ]);
    }

    /**
     * Suppression d'un examen
     */
    public function destroy(Examens $examen)
    {
        $examen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Examen supprimé avec succès'
        ]);
    }
}