<?php

namespace App\Http\Controllers;

use App\Models\Centre_medicaux;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CentreMedicauxController extends Controller
{
    /**
     * Liste des centres médicaux
     */
    public function index()
    {
        $centres = Centre_medicaux::all();
        return response()->json([
            'success' => true,
            'data' => $centres
        ]);
    }

    /**
     * Ajout d'un centre médical
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'          => 'required|string|max:255',
            'adresse'      => 'required|string',
            'telephone'    => 'required|string|max:20',
            'ville'        => 'required|string|max:100',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'gps_capacite' => 'in:2G,3G,4G,5G,none',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $centre = Centre_medicaux::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Centre médical créé avec succès',
            'data' => $centre
        ], 201);
    }

    /**
     * Modification d'un centre médical
     */
    public function update(Request $request, Centre_medicaux $centreMedicaux)
    {
        $validator = Validator::make($request->all(), [
            'nom'          => 'sometimes|string|max:255',
            'adresse'      => 'sometimes|string',
            'telephone'    => 'sometimes|string|max:20',
            'ville'        => 'sometimes|string|max:100',
            'latitude'     => 'sometimes|numeric|between:-90,90',
            'longitude'    => 'sometimes|numeric|between:-180,180',
            'gps_capacite' => 'in:2G,3G,4G,5G,none',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $centreMedicaux->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Centre médical mis à jour avec succès',
            'data' => $centreMedicaux
        ]);
    }

    /**
     * Suppression d'un centre médical
     */
    public function destroy(Centre_medicaux $centreMedicaux)
    {
        $centreMedicaux->delete();

        return response()->json([
            'success' => true,
            'message' => 'Centre médical supprimé avec succès'
        ]);
    }
}