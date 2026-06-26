<?php

namespace App\Http\Controllers;

use App\Models\Examens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     * Création d'un examen avec gestion du fichier
     */
    public function store(Request $request)
    {
        Log::info('Début store examen', $request->all());

        $validator = Validator::make($request->all(), [
            'consultation_id' => 'required|exists:consultations,id',
            'type_examen'     => 'required|string|max:100',
            'laboratoire'     => 'nullable|string|max:150',
            'urgence'         => 'boolean',
            'resultat'        => 'nullable|string',
            'date_resultat'   => 'nullable|date',
            'fichier_joint'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Validation fichier
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée pour examen', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('fichier_joint');

        // Gestion du fichier joint
                
                if ($request->hasFile('fichier_joint')) {
                    $file = $request->file('fichier_joint');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('examens/resultats', $filename, 'public');
                    
                    // URL complète et fiable
                    $data['fichier_joint'] = asset('storage/' . $path);
                    
                    Log::info('Fichier joint sauvegardé', ['url' => $data['fichier_joint']]);
                }
        $examen = Examens::create($data);

        Log::info('Examen créé avec succès', ['id' => $examen->id]);

        return response()->json([
            'success' => true,
            'message' => 'Examen créé avec succès',
            'data'    => $examen->load('consultation')
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
            'fichier_joint' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            Log::error('Validation update examen échouée', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('fichier_joint');

        if ($request->hasFile('fichier_joint')) {
            $file = $request->file('fichier_joint');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('examens/resultats', $filename, 'public');
            $data['fichier_joint'] = Storage::url($path);
        }

        $examen->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Examen mis à jour avec succès',
            'data'    => $examen->load('consultation')
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