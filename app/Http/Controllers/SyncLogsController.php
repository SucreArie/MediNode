<?php

namespace App\Http\Controllers;

use App\Models\Sync_logs;
use App\Models\Centre_medicaux;
use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Sync_logs::latest()->paginate(20));
    }

    /**
     * Fournit les données pour le tableau de bord de synchronisation
     */
    public function getSyncDashboard()
    {
        // Récupération des centres avec leurs statistiques
        $centers = Centre_medicaux::withCount(['dossiers', 'users'])->get();

        // Récupération des logs récents transformés pour le frontend
        $history = Sync_logs::latest()
            ->take(15)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'from' => $log->node_id, // Le nœud qui a généré la modification
                    'to' => 'MediNode Core',  // Dans ce prototype, on synchronise vers le centre
                    'table' => $log->table_name,
                    'operation' => $log->operation,
                    'records' => 1,
                    'time' => $log->created_at->format('H:i'),
                    'status' => $log->sync_status === 'acknowledged' ? 'success' :
                               ($log->sync_status === 'failed' ? 'error' : 'pending'),
                    'duration' => rand(15, 120) . 'ms' // Simulation de latence réseau
                ];
            });

        // Calcul des métriques réseau
        $totalRecords = Dossier::count();
        $syncedRecords = Sync_logs::where('sync_status', 'acknowledged')->count();
        $pendingSync = Sync_logs::where('sync_status', 'pending')->count();

        return response()->json([
            'networkStats' => [
                'totalNodes' => $centers->count(),
                'activeNodes' => $centers->count(), // Ici, on considère tous les centres créés comme actifs
                'totalRecords' => $totalRecords,
                'syncedRecords' => $syncedRecords,
                'pendingSync' => $pendingSync,
                'avgLatency' => '34ms',
                'consistency' => $totalRecords > 0 ? round((($totalRecords - $pendingSync) / $totalRecords) * 100, 2) . '%' : '100%'
            ],
            'history' => $history,
            'centers' => $centers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sync_logs $sync_logs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sync_logs $sync_logs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sync_logs $sync_logs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sync_logs $sync_logs)
    {
        //
    }
}
