<script setup>
import { ref, computed, onMounted } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import DefaultInfoCard from "@/examples/Cards/DefaultInfoCard.vue";
import { fetchBinaryTree, fetchReferrals } from "@/services/me";

const loading = ref(true);
const error = ref("");
const payload = ref(null);
const directs = ref([]);

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const [bin, refs] = await Promise.all([fetchBinaryTree(), fetchReferrals()]);
    payload.value = bin;
    directs.value = refs.items || [];
  } catch {
    error.value = "No se pudo cargar el árbol binario. Inicia sesión e inténtalo de nuevo.";
  } finally {
    loading.value = false;
  }
});

const tree = computed(() => payload.value?.tree);
const stats = computed(() => payload.value?.stats || {});
const birPct = computed(() => payload.value?.bir_percentages || { 1: 0.21, 2: 0.15, 3: 0.06 });

function initials(nombre) {
  const parts = String(nombre || "")
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  const a = parts[0]?.[0] || "U";
  const b = parts[1]?.[0] || "";
  return `${a}${b}`.toUpperCase();
}

const directNodes = computed(() =>
  directs.value.map((r) => ({
    id: r.id,
    nombre: r.name,
    memberCode: r.member_code || "—",
    fechaAlta: r.fecha_alta || "—",
    joinedAtMs: r.joined_at ? Date.parse(r.joined_at) : 0,
    pv: Number(r.monthly_qualifying_pv || 0),
    rank: r.rank_name || "—",
    status: r.account_status || "—",
    prefer: r.preferred_binary_leg || "auto",
    placedLeg: r.pierna || null,
    iniciales: initials(r.name),
  }))
);

const directByPref = computed(() => {
  const left = [];
  const right = [];
  let sinAsignarCount = 0;
  for (const d of directNodes.value) {
    // Lado final: si eligió izquierda/derecha, usar eso. Si fue auto, usar la pierna asignada en binario.
    const finalLeg =
      d.prefer === "left" || d.prefer === "right"
        ? d.prefer
        : d.placedLeg === "left" || d.placedLeg === "right"
          ? d.placedLeg
          : null;

    if (finalLeg === "left") left.push(d);
    else if (finalLeg === "right") right.push(d);
    else sinAsignarCount += 1;
  }
  // FIFO real: primero entra primero se muestra (joined_at), fallback por id
  const sorter = (a, b) => {
    if (a.joinedAtMs !== b.joinedAtMs) return a.joinedAtMs - b.joinedAtMs;
    return (Number(a.id) || 0) - (Number(b.id) || 0);
  };
  left.sort(sorter);
  right.sort(sorter);
  return { left, right, sinAsignarCount };
});

function statusLabel(s) {
  if (s === "active") return "Activo";
  if (s === "pending") return "Pendiente";
  if (s === "inactive") return "Inactivo";
  return s ? String(s) : "—";
}

function placedLabel(leg) {
  if (leg === "left") return "Colocado: Izq";
  if (leg === "right") return "Colocado: Der";
  return "Sin colocación";
}

const birRows = computed(() => {
  const p = birPct.value;
  return [
    {
      level: 1,
      name: "Línea 1 (patrocinio directo)",
      pct: `${Math.round((p[1] ?? 0.21) * 100)}%`,
      note: "Bono inicio rápido (BIR)",
    },
    {
      level: 2,
      name: "Línea 2",
      pct: `${Math.round((p[2] ?? 0.15) * 100)}%`,
      note: "BIR",
    },
    {
      level: 3,
      name: "Línea 3",
      pct: `${Math.round((p[3] ?? 0.06) * 100)}%`,
      note: "BIR",
    },
  ];
});

function formatPv(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return `${n.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`;
}

function formatBs(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(n);
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Red de árbol binario</h4>
            <p class="mb-0 text-sm text-secondary">
              Datos en tiempo real desde tu posición en la red (volumen semanal, comisiones y estructura).
            </p>
            <p v-if="error" class="text-danger text-sm mt-2 mb-0">{{ error }}</p>
            <p v-else-if="loading" class="text-muted text-sm mt-2 mb-0">Cargando…</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna izquierda"
          :value="formatPv(stats.left_pv)"
          description="<span class='text-sm font-weight-bolder text-primary'>Volumen</span> semanal"
          :icon="{
            component: 'ni ni-bold-left',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna derecha"
          :value="formatPv(stats.right_pv)"
          description="<span class='text-sm font-weight-bolder text-info'>Volumen</span> semanal"
          :icon="{
            component: 'ni ni-bold-right',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Bono binario (semana)"
          :value="formatBs(stats.binary_earnings_week)"
          description="<span class='text-sm font-weight-bolder text-success'>Periodo</span> actual"
          :icon="{
            component: 'ni ni-money-coins',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="BIR acumulado"
          :value="formatBs(stats.bir_total)"
          description="<span class='text-sm font-weight-bolder text-warning'>Inicio rápido</span>"
          :icon="{
            component: 'ni ni-chart-bar-32',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Rango"
          :value="stats.current_rank || '—'"
          description="<span class='text-sm font-weight-bolder text-dark'>Tu rango</span>"
          :icon="{
            component: 'ni ni-world-2',
            background: 'bg-gradient-dark',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Nodos en binario"
          :value="String(stats.binary_network_count ?? 0)"
          description="<span class='text-sm font-weight-bolder text-secondary'>Debajo de ti</span>"
          :icon="{
            component: 'ni ni-single-02',
            background: 'bg-gradient-secondary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-6 col-12 d-flex align-items-stretch">
        <default-info-card
          title="Residual unilevel"
          description="Suma histórica de comisiones residuales registradas en tu cuenta."
          :value="formatBs(stats.residual_total)"
          :icon="{
            component: 'ni ni-trophy',
            background: 'bg-gradient-success',
          }"
        />
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-6 col-12 mb-4 mb-lg-0">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Estructura del árbol binario</h6>
            <p class="text-xs text-secondary mt-1 mb-0">
              Pirámide por preferencia de colocación (solo socios directos) y PV por socio.
            </p>
          </div>
          <div class="p-3 card-body overflow-auto">
            <div v-if="tree" class="tree-diagram">
              <div class="tree-node root text-center mb-3">
                <span class="node-badge bg-gradient-primary">Tú</span>
                <div class="text-sm font-weight-bold mt-1">{{ tree.user?.name }}</div>
                <div class="text-xxs text-muted">{{ tree.user?.member_code }}</div>
              </div>
              <div class="tree-connector"></div>
              <div v-if="directByPref.sinAsignarCount" class="alert alert-light border text-xs mb-3 py-2">
                <strong>{{ directByPref.sinAsignarCount }}</strong> socio(s) aún <strong>sin pierna asignada</strong> (no aparecen en Izq/Der).
              </div>
              <div class="pyramid">
                <div class="pyramid__col">
                  <div class="pyramid__title text-primary">Izquierda</div>
                  <div v-if="directByPref.left.length" class="pyramid__stack">
                    <div
                      v-for="(d, idx) in directByPref.left"
                      :key="'pl-' + d.id"
                      class="pyramid__node"
                      :style="{ width: Math.max(62, 100 - idx * 6) + '%' }"
                    >
                      <div class="pyramid__badge bg-gradient-primary text-white shadow">{{ d.iniciales }}</div>
                      <div class="min-w-0">
                        <div class="text-sm font-weight-bolder text-dark text-truncate">{{ d.nombre }}</div>
                        <div class="text-xxs text-secondary">
                          {{ formatPv(d.pv) }} · {{ d.rank }} · {{ placedLabel(d.placedLeg) }}
                        </div>
                      </div>
                      <span class="badge badge-sm ms-auto bg-gradient-secondary">{{ statusLabel(d.status) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-sm text-muted">Sin socios</div>
                </div>

                <div class="pyramid__col">
                  <div class="pyramid__title text-success">Derecha</div>
                  <div v-if="directByPref.right.length" class="pyramid__stack">
                    <div
                      v-for="(d, idx) in directByPref.right"
                      :key="'pr-' + d.id"
                      class="pyramid__node"
                      :style="{ width: Math.max(62, 100 - idx * 6) + '%' }"
                    >
                      <div class="pyramid__badge bg-gradient-success text-white shadow">{{ d.iniciales }}</div>
                      <div class="min-w-0">
                        <div class="text-sm font-weight-bolder text-dark text-truncate">{{ d.nombre }}</div>
                        <div class="text-xxs text-secondary">
                          {{ formatPv(d.pv) }} · {{ d.rank }} · {{ placedLabel(d.placedLeg) }}
                        </div>
                      </div>
                      <span class="badge badge-sm ms-auto bg-gradient-secondary">{{ statusLabel(d.status) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-sm text-muted">Sin socios</div>
                </div>

              </div>
            </div>
            <p v-else class="text-sm text-muted mb-0">Sin datos de árbol.</p>
          </div>
        </div>
      </div>

      <div class="col-lg-6 col-12">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Bono inicio rápido (BIR) por línea</h6>
            <p class="text-xs text-secondary mt-1 mb-0">
              Porcentajes sobre monto comisionable del paquete (configuración MLM).
            </p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Línea</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">%</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acumulado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in birRows" :key="row.level">
                  <td>
                    <span class="text-sm font-weight-bold">{{ row.name }}</span>
                    <div class="text-xxs text-muted">{{ row.note }}</div>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-primary">{{ row.pct }}</span>
                  </td>
                  <td class="align-middle">
                    <span class="text-sm font-weight-bolder">{{ formatBs(stats.bir_by_level?.[row.level]) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="p-4 card-body">
            <h6 class="mb-3 font-weight-bolder text-dark">¿Cómo se calcula el binario y el BIR?</h6>
            <div class="row">
              <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-primary rounded-circle me-2 shadow">
                    <i class="ni ni-bold-left text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">Dos piernas</span>
                    <p class="text-xs text-secondary mb-0">
                      El volumen PV se acumula por pierna según la colocación en la red.
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-success rounded-circle me-2 shadow">
                    <i class="ni ni-money-coins text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">Bono binario</span>
                    <p class="text-xs text-secondary mb-0">
                      Comisiones tipo binario del periodo semanal mostradas arriba.
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-info rounded-circle me-2 shadow">
                    <i class="ni ni-chart-bar-32 text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">BIR (3 líneas)</span>
                    <p class="text-xs text-secondary mb-0">
                      21 % / 15 % / 6 % sobre el comisionable del paquete en las primeras tres generaciones de
                      patrocinio.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tree-diagram {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem 0;
  min-height: 220px;
}
.tree-node {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.tree-node.root {
  width: auto;
  height: auto;
  border-radius: 0;
}
.node-badge {
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  padding: 0.25rem 0.4rem;
  border-radius: 50%;
  min-width: 2rem;
  display: inline-block;
  text-align: center;
}
.tree-connector {
  width: 2px;
  height: 24px;
  background: linear-gradient(180deg, #5e72e4 0%, transparent 100%);
  margin: 0 auto;
}
.tree-children {
  width: 100%;
  margin-top: 0.25rem;
}
.text-xxs {
  font-size: 0.65rem;
}

.pyramid {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
  align-items: start;
}
.pyramid__col {
  min-width: 0;
}
.pyramid__title {
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}
.pyramid__stack {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: center;
}
.pyramid__node {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 0.65rem;
  border-radius: 0.85rem;
  border: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(255, 255, 255, 0.86);
  box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.06);
  transition: transform 0.12s ease, box-shadow 0.18s ease;
  max-width: 100%;
}
.pyramid__node:hover {
  transform: translateY(-1px);
  box-shadow: 0 0.45rem 1.25rem rgba(0, 0, 0, 0.08);
}
.pyramid__badge {
  width: 34px;
  height: 34px;
  border-radius: 0.75rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.65rem;
  flex: 0 0 auto;
}
@media (max-width: 991.98px) {
  .pyramid {
    grid-template-columns: 1fr;
  }
  .pyramid__stack {
    align-items: stretch;
  }
  .pyramid__node {
    width: 100% !important;
  }
}
</style>
