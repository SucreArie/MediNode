<?php

namespace App\Http\Controllers;

use App\Models\Sync_logs;
use App\Models\Centre_medicaux;
use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $centers = Centre_medicaux::withCount(['dossiers', 'users'])
            ->get()
            ->map(function ($center) {
                // On récupère le dernier log de synchronisation réussi pour ce centre spécifique
                $lastLog = Sync_logs::where('node_id', $center->nom)
                    ->whereNotNull('synced_at')
                    ->latest('synced_at')
                    ->first();

                // On injecte dynamiquement la date et le statut pour le composant SyncStatusCard
                $center->synced_at = $lastLog ? $lastLog->synced_at : null;
                $center->sync_status = $lastLog ? ($lastLog->sync_status === 'acknowledged' ? 'synced' : $lastLog->sync_status) : 'synced';
                return $center;
            });

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
        $syncedDossiersCount = Sync_logs::where('table_name', 'dossiers')->where('sync_status', 'acknowledged')->distinct('record_id')->count();
        $pendingSync = Sync_logs::where('sync_status', 'pending')->count();

        return response()->json([
            'networkStats' => [
                'totalNodes' => $centers->count(),
                'activeNodes' => $centers->count(), // Ici, on considère tous les centres créés comme actifs
                'totalRecords' => $totalRecords,
                'syncedRecords' => $syncedDossiersCount,
                'pendingSync' => $pendingSync,
                'avgLatency' => '34ms', // Ceci peut être dynamisé plus tard
                'consistency' => $totalRecords > 0 ? round(($syncedDossiersCount / $totalRecords) * 100, 2) . '%' : '100%'
            ],
            'history' => $history,
            'centers' => $centers
        ]);
    }

    /**
     * Récupère l'intégralité du journal d'activité pour l'audit
     */
    public function getFullActivityLog()
    {
        $logs = Sync_logs::latest()->take(100)->get()->map(function($log) {
            // Mapping intelligent du type selon la table impactée
            $type = 'record';
            if ($log->table_name === 'system') $type = 'sync';
            if ($log->table_name === 'users') $type = 'access';
            
            // Détermination de la sévérité selon le statut de synchro
            $severity = 'info';
            if ($log->sync_status === 'failed') $severity = 'error';
            if ($log->sync_status === 'acknowledged') $severity = 'success';
            if ($log->sync_status === 'pending') $severity = 'warning';

            return [
                'id' => $log->id,
                'type' => $type,
                'action' => $log->operation . ' ' . strtoupper($log->table_name),
                'description' => "Transaction sur " . $log->table_name . " (ID:" . $log->record_id . ") provenant de " . $log->node_id,
                'user' => $log->node_id,
                'time' => $log->created_at->format('H:i'),
                'date' => $log->created_at->format('Y-m-d'),
                'ip' => '127.0.0.1',
                'severity' => $severity,
            ];
        });

        return response()->json($logs);
    }

    /**
     * Déclenche une synchronisation manuelle et enregistre le log
     */
    public function triggerSync(Request $request)
    {
        try {
            // Validation simple de l'entrée
            $center = Centre_medicaux::findOrFail($request->center_id);

            // Tentative de création du log
            $log = Sync_logs::create([
                'node_id'     => $center->nom,
                'table_name'  => 'system',
                'record_id'   => 0,
                'operation'   => 'INSERT',
                'data'        => json_encode(['action' => 'manual_sync_triggered', 'timestamp' => now()]),
                'sync_status' => 'acknowledged',
                'synced_at'   => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Synchronisation réussie',
                'log'     => $log
            ]);

        } catch (\Exception $e) {
            // Capturer la vraie erreur dans storage/logs/laravel.log
            Log::error("Erreur critique lors de la synchronisation : " . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur lors de la synchronisation',
                'error'   => $e->getMessage()
            ], 500);
        }
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
