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
        $dossiers = Dossier::with(['patient', 'medecin', 'examen', 'prescription', 'consultation', 'centreMedical'])->get();
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
            'centre_medical_id' => 'required|exists:centre_medicauxes,id',
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
            'data'    => $dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation', 'centreMedical'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dossier $dossier)
    {
        return response()->json($dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation', 'centreMedical']));
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
            'centre_medical_id' => 'sometimes|exists:centre_medicauxes,id',
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
            'data'    => $dossier->load(['patient', 'medecin', 'examen', 'prescription', 'consultation', 'centreMedical'])
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

    /**
     * Récupère les activités récentes pour le panel de notifications
     */
    public function getRecentActivity()
    {
        $notifications = collect();

        // Derniers patients ajoutés
        \App\Models\User::where('role', 'patient')->latest()->take(3)->get()->each(function ($user) use ($notifications) {
            $notifications->push([
                'id' => 'p' . $user->id,
                'message' => "Nouveau patient ajouté : " . $user->name,
                'time' => $user->created_at->diffForHumans(),
                'type' => 'success'
            ]);
        });

        // Dernières consultations
        \App\Models\Consultations::with('patient')->latest()->take(3)->get()->each(function ($cons) use ($notifications) {
            $notifications->push([
                'id' => 'c' . $cons->id,
                'message' => "Nouvelle consultation : " . ($cons->patient->name ?? 'Inconnu'),
                'time' => $cons->created_at->diffForHumans(),
                'type' => 'info'
            ]);
        });

        // Derniers dossiers
        Dossier::latest()->take(3)->get()->each(function ($dos) use ($notifications) {
            $notifications->push([
                'id' => 'd' . $dos->id,
                'message' => "Nouveau dossier créé : " . $dos->name,
                'time' => $dos->created_at->diffForHumans(),
                'type' => 'success'
            ]);
        });

        return response()->json($notifications->sortByDesc('id')->values());
    }

    /**
     * Statistiques de synchronisation pour le dashboard distribué
     */
    // public function getSyncDashboard()
    // {
    //     $centers = \App\Models\Centre_medicaux::withCount(['dossiers', 'users'])->get();

    //     // Simulation/Récupération des logs de synchronisation
    //     // Dans la pratique, on interroge la table sync_logs
    //     $logs = \Illuminate\Support\Facades\DB::table('sync_logs')
    //         ->latest()
    //         ->take(10)
    //         ->get()
    //         ->map(function($log) {
    //             return [
    //                 'id' => $log->id,
    //                 'from' => \App\Models\Centre_medicaux::find($log->source_center_id)->nom ?? 'Inconnu',
    //                 'to' => \App\Models\Centre_medicaux::find($log->target_center_id)->nom ?? 'Inconnu',
    //                 'records' => $log->records_synced,
    //                 'time' => \Carbon\Carbon::parse($log->created_at)->format('H:i'),
    //                 'status' => $log->status,
    //                 'duration' => $log->duration . 's'
    //             ];
    //         });

    //     return response()->json([
    //         'networkStats' => [
    //             'totalNodes' => $centers->count(),
    //             'activeNodes' => $centers->count(), // À filtrer selon un heartbeat
    //             'totalRecords' => \App\Models\Dossier::count(),
    //             'syncedRecords' => \App\Models\Dossier::count(), // Prototype
    //             'pendingSync' => 0,
    //             'avgLatency' => '42ms',
    //             'consistency' => '99.98%'
    //         ],
    //         'history' => $logs,
    //         'centers' => $centers
    //     ]);
    // }
}
