import { useState, useEffect } from 'react'
import {
  RefreshCw, Database, Server, Activity, ArrowRight,
  CheckCircle2, AlertCircle, Clock, Zap, Globe,
  TrendingUp, ArrowUpRight, ArrowDownRight, Save
} from 'lucide-react'
import Sidebar from '../components/Sidebar'
import Topbar from '../components/Topbar'
import Card from '../components/Card'
import Badge from '../components/Badge'
import SyncStatusCard from '../components/SyncStatusCard'
import Button from '../components/Button'
import api from '../services/api'
import { cn } from '../utils/helpers'
import { useToast } from '../context/ToastContext'
import Modal from '../components/Modal'

export default function Synchronization() {
  const { showToast } = useToast()
  const [activeFlows, setActiveFlows] = useState([])
  const [currentTime, setCurrentTime] = useState(new Date())
  const [data, setData] = useState({ networkStats: {}, history: [], centers: [] })
  const [loading, setLoading] = useState(true)
  const [syncing, setSyncing] = useState(false)
  const [isSyncModalOpen, setIsSyncModalOpen] = useState(false)
  const [syncingCenterId, setSyncingCenterId] = useState(null)
  const [selectedCenterForSync, setSelectedCenterForSync] = useState('')

  const fetchData = async () => {
    try {
      setLoading(true)
      const res = await api.get('/admin/sync/dashboard')
      setData(res.data)
      // Initialiser selectedCenterForSync si des centres existent
      if (res.data.centers.length > 0 && !selectedCenterForSync) {
        setSelectedCenterForSync(res.data.centers[0].id);
      }
    } catch (err) {
      console.error("Erreur sync data", err)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchData()
  }, [])

  const handleTriggerSync = async (centerId) => {
    if (!centerId) return showToast("Veuillez sélectionner un centre.", "error");

    const targetCenter = data.centers.find(c => c.id === centerId);
    if (!targetCenter) return showToast("Centre sélectionné introuvable.", "error");

    setSyncing(true)
    setSyncingCenterId(centerId)
    setIsSyncModalOpen(false) // Fermer la modale

    try {
      await api.post('/admin/sync/trigger', { center_id: centerId })
      showToast(`Synchronisation réussie avec ${targetCenter.nom}`)
      await fetchData()
    } catch (err) {
      showToast("Erreur lors de la synchronisation", "error")
    } finally {
      setSyncing(false)
      setSyncingCenterId(null)
    }
  }

  // Simulate active sync flows
  useEffect(() => {
    const interval = setInterval(() => {
      setActiveFlows(prev => {
        const newFlows = [...prev]
        if (Math.random() > 0.7 && newFlows.length < 3 && data.centers.length > 1) {
          const from = data.centers[Math.floor(Math.random() * data.centers.length)]
          const to = data.centers.filter(c => c.id !== from.id)[Math.floor(Math.random() * (data.centers.length - 1))]
          newFlows.push({ id: Date.now(), from: from.nom, to: to.nom, progress: 0 })
        }
        return newFlows.map(f => ({ ...f, progress: Math.min(f.progress + 10, 100) })).filter(f => f.progress < 100)
      })
      setCurrentTime(new Date())
    }, 2000)
    return () => clearInterval(interval)
  }, [data.centers])

  const onlineCenters = data.centers

  return (
    <div className="min-h-screen bg-[#F6FAFB]">
      <Sidebar />
      <div className="ml-64">
        <Topbar />
        <main className="p-6">
          {/* Header */}
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="text-2xl font-heading font-bold text-[#1D2D35]">Synchronisation</h1>
              <p className="text-[#5E7480]">Vue en temps réel du système distribué MediNode</p>
            </div>
            <div className="flex items-center gap-4">
              <Button onClick={() => setIsSyncModalOpen(true)} loading={syncing} variant="accent">
                <RefreshCw size={18} className={cn(syncing && "animate-spin")} />
                Lancer Synchronisation
              </Button>
              <div className="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#4FAF8F]/10">
                <div className="w-2 h-2 rounded-full bg-[#4FAF8F] animate-pulse" />
                <span className="text-sm font-medium text-[#4FAF8F]">Système actif</span>
              </div>
              <span className="text-sm text-[#5E7480]">
                {currentTime.toLocaleTimeString('fr-FR')}
              </span>
            </div>
          </div>

          {/* Network Stats - Hero Section */}
          <div className="grid grid-cols-6 gap-4 mb-6">
            <Card className="col-span-2 relative overflow-hidden bg-gradient-to-br from-[#0F4C5C] to-[#3BA7B8] text-white">
              <div className="absolute top-0 right-0 w-32 h-32 rounded-full blur-3xl opacity-20 bg-white" />
              <div className="relative">
                <div className="flex items-center gap-2 mb-4">
                  <Globe size={20} />
                  <span className="text-white/80 text-sm">Réseau Global</span>
                </div>
                <div className="flex items-end gap-2 mb-2">
                  <span className="text-4xl font-bold">{data.networkStats.totalNodes || 0}</span>
                  <span className="text-white/60 mb-1">noeuds</span>
                </div>
                <div className="flex items-center gap-4 text-sm">
                  <span className="flex items-center gap-1">
                    <div className="w-2 h-2 rounded-full bg-[#58D6C3]" />
                    {data.networkStats.activeNodes || 0} actifs
                  </span>
                  <span className="text-white/60">
                    Consistance: {data.networkStats.consistency}
                  </span>
                </div>
              </div>
            </Card>

            {[
              { label: 'Dossiers Totaux', value: (data.networkStats.totalRecords || 0).toLocaleString(), icon: Database, color: 'from-[#3BA7B8] to-[#58D6C3]' },
              { label: 'Synchronisés', value: (data.networkStats.syncedRecords || 0).toLocaleString(), icon: CheckCircle2, color: 'from-[#4FAF8F] to-[#58D6C3]', trend: '+234' },
              { label: 'En attente', value: (data.networkStats.pendingSync || 0).toString(), icon: Clock, color: 'from-[#F4B860] to-[#D96C6C]' },
              { label: 'Latence Moy.', value: data.networkStats.avgLatency || '0ms', icon: Zap, color: 'from-[#0F4C5C] to-[#3BA7B8]' },
            ].map((stat, i) => (
              <Card key={i} className="relative overflow-hidden">
                <div className={cn('absolute -top-4 -right-4 w-16 h-16 rounded-full blur-2xl opacity-30 bg-gradient-to-br', stat.color)} />
                <div className="flex items-center gap-2 mb-2">
                  <stat.icon size={16} className="text-[#5E7480]" />
                  <span className="text-xs text-[#5E7480]">{stat.label}</span>
                </div>
                <div className="flex items-end gap-2">
                  <span className="text-2xl font-bold text-[#1D2D35]">{stat.value}</span>
                  {stat.trend && (
                    <span className="text-xs text-[#4FAF8F] flex items-center">
                      <ArrowUpRight size={12} />
                      {stat.trend}
                    </span>
                  )}
                </div>
              </Card>
            ))}
          </div>

          {/* Main Visualization */}
          <div className="grid grid-cols-3 gap-6 mb-6">
            {/* Network Topology */}
            <Card className="col-span-2">
              <Card.Header>
                <div className="flex items-center gap-2">
                  <Globe size={20} className="text-[#3BA7B8]" />
                  <Card.Title>Topologie du Réseau</Card.Title>
                </div>
                <Badge variant="success">{onlineCenters.length} connectés</Badge>
              </Card.Header>

            {/* Visual Network Map */}
            <div className="relative h-80 bg-slate-50 rounded-2xl overflow-hidden border border-slate-100">
                {/* Central Hub */}
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
                  <div className="relative">
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-[#0F4C5C] to-[#3BA7B8] flex items-center justify-center shadow-lg">
                      <Database size={32} className="text-white" />
                    </div>
                    {syncing && <div className="absolute inset-0 rounded-full bg-[#3BA7B8]/30 animate-ping" />}
                  </div>
                  <p className="text-center mt-2 text-xs font-medium text-[#1D2D35]">MediNode Core</p>
                </div>

                {/* Connected Nodes */}
              <svg className="absolute inset-0 w-full h-full">
                {onlineCenters.map((center, index) => {
                  const angle = (index * 360) / onlineCenters.length
                  const radius = 120
                  const x = 160 + Math.cos((angle * Math.PI) / 180) * radius
                  const y = 160 + Math.sin((angle * Math.PI) / 180) * radius
                  const isCurrentlySyncing = syncingCenterId === center.id && syncing

                  return (
                    <g key={center.id}>
                      {/* Dotted Signal Line */}
                      <line
                        x1="160"
                        y1="160"
                        x2={x}
                        y2={y}
                        stroke={isCurrentlySyncing ? '#3BA7B8' : '#CBD5E1'}
                        strokeWidth="2"
                        strokeDasharray="4,4"
                        className={isCurrentlySyncing ? "animate-dash-flow" : ""}
                      />

                      {/* HTML Node overlays */}
                      <foreignObject x={x - 24} y={y - 24} width="48" height="60">
                        <div className="flex flex-col items-center">
                          <div className={cn(
                            "w-10 h-10 rounded-lg flex items-center justify-center shadow-sm border transition-all",
                            center.status !== 'offline' ? "bg-white border-slate-200" : "bg-slate-100 border-transparent"
                          )}>
                            <Server size={18} className={center.status !== 'offline' ? "text-[#0F4C5C]" : "text-slate-400"} />
                          </div>
                          <div className={cn(
                            "w-2 h-2 rounded-full mt-1 border border-white",
                            center.status !== 'offline' ? "bg-emerald-500" : "bg-rose-500"
                          )} />
                          <span className="text-[9px] font-bold text-slate-600 mt-1 uppercase tracking-tighter">
                            {(center.nom || '').split(' ')[0]}
                          </span>
                        </div>
                      </foreignObject>
                    </g>
                  )
                })}
              </svg>
            </div> {/* Fermeture de la div "Visual Network Map" */}
            </Card> {/* Fermeture de la Card "Topologie du Réseau" */}

            {/* Real-time Activity */}
            <Card>
              <Card.Header>
                <div className="flex items-center gap-2">
                  <RefreshCw size={20} className="text-[#3BA7B8] animate-spin" style={{ animationDuration: '3s' }} />
                  <Card.Title>Activité Temps Réel</Card.Title>
                </div>
              </Card.Header>

              <div className="space-y-3 max-h-72 overflow-y-auto">
                {data.history.map((sync, index) => (
                  <div
                    key={sync.id}
                    className={cn(
                      'flex items-center gap-3 p-3 rounded-xl transition-all',
                      index === 0 ? 'bg-[#3BA7B8]/10 border border-[#3BA7B8]/20' : 'bg-[#F6FAFB]'
                    )}
                  >
                    <div className={cn(
                      'w-8 h-8 rounded-lg flex items-center justify-center',
                      sync.status === 'success' || sync.status === 'acknowledged' ? 'bg-[#4FAF8F]/10' : 'bg-[#D96C6C]/10'
                    )}>
                      {sync.status === 'success' || sync.status === 'acknowledged' ? (
                        <CheckCircle2 size={16} className="text-[#4FAF8F]" />
                      ) : (
                        <AlertCircle size={16} className="text-[#D96C6C]" />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-1 text-xs text-[#5E7480]">
                        <span className="font-medium text-[#1D2D35] truncate">{sync.from.split(' ')[0]}</span>
                        <ArrowRight size={12} />
                        <span className="font-medium text-[#1D2D35] truncate">{sync.to.split(' ')[0]}</span>
                      </div>
                      <p className="text-xs text-[#5E7480]">{sync.records} fichiers - {sync.duration}</p>
                    </div>
                    <span className="text-xs text-[#5E7480]">{sync.time}</span>
                  </div>
                ))}
              </div>
            </Card>
          </div>

          {/* Node Status Grid */}
          <Card>
            <Card.Header>
              <div className="flex items-center gap-2">
                <Server size={20} className="text-[#3BA7B8]" />
                <Card.Title>État des Noeuds</Card.Title>
              </div>
              <div className="flex items-center gap-4 text-sm">
                <span className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-[#4FAF8F]" />
                  Synchronisé
                </span>
                <span className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-[#3BA7B8] animate-pulse" />
                  En cours
                </span>
                <span className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-[#F4B860]" />
                  En pause
                </span>
              </div>
            </Card.Header>

            <div className="grid grid-cols-3 gap-4">
              {data.centers.map((center) => (
                <SyncStatusCard
                  key={center.id}
                  node={center.nom}
                  status={center.sync_status || 'synced'}
                  lastSync="Il y a 5 min"
                  records={center.dossiers_count || 0}
                />
              ))}
            </div>

          </Card>
        </main>
      </div>

      {/* Modal de sélection du centre pour la synchronisation */}
      <Modal
        isOpen={isSyncModalOpen}
        onClose={() => setIsSyncModalOpen(false)}
        title="Lancer la synchronisation"
      >
        <p className="text-sm text-[#5E7480] mb-4">Sélectionnez le centre avec lequel vous souhaitez lancer une synchronisation manuelle.</p>
        <div className="space-y-3">
          {data.centers.length === 0 ? (
            <p className="text-sm text-[#D96C6C]">Aucun centre médical disponible.</p>
          ) : (
            data.centers.map(center => (
              <label key={center.id} className="flex items-center gap-3 p-3 rounded-xl border border-[#EAF1F4] hover:bg-[#F6FAFB] cursor-pointer">
                <input
                  type="radio"
                  name="syncCenter"
                  value={center.id}
                  checked={selectedCenterForSync === center.id}
                  onChange={(e) => setSelectedCenterForSync(Number(e.target.value))}
                  className="form-radio text-[#3BA7B8] focus:ring-[#3BA7B8]"
                />
                <span className="font-medium text-[#1D2D35]">{center.nom}</span>
              </label>
            ))
          )}
        </div>
        <div className="mt-6 flex justify-end gap-3">
          <Button variant="outline" onClick={() => setIsSyncModalOpen(false)}>Annuler</Button>
          <Button onClick={() => handleTriggerSync(selectedCenterForSync)} loading={syncing} disabled={!selectedCenterForSync || syncing}>
            <RefreshCw size={18} className={cn(syncing && "animate-spin")} /> Synchroniser
          </Button>
        </div>
      </Modal>
    </div>
  )
}
