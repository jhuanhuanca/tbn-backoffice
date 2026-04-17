<script setup>
import { ref, computed, onMounted } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import { fetchReferrals, fetchUnilevelTree } from "@/services/me";

const loading = ref(true);
const error = ref("");
const items = ref([]);
const summary = ref({ total: 0, activos: 0, pendientes: 0, izquierda: 0, derecha: 0 });
const uniTree = ref(null);

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const data = await fetchReferrals();
    items.value = data.items || [];
    summary.value = data.summary || summary.value;
    uniTree.value = await fetchUnilevelTree(3);
  } catch {
    error.value = "No se pudieron cargar los referidos.";
  } finally {
    loading.value = false;
  }
});

function iniciales(nombre) {
  const parts = String(nombre || "")
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  const a = parts[0]?.[0] || "U";
  const b = parts[1]?.[0] || "";
  return `${a}${b}`.toUpperCase();
}

function formatPv(v) {
  if (v === null || v === undefined) return "—";
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return `${n.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`;
}

function estadoLabel(status) {
  if (status === "active") return "Activo";
  if (status === "pending") return "Pendiente";
  if (status === "inactive") return "Inactivo";
  return status ? String(status) : "—";
}

function legSortKey(pierna) {
  if (pierna === "left") return 0;
  if (pierna === "right") return 1;
  return 2;
}

const referidos = computed(() =>
  items.value.map((r) => ({
    id: r.id,
    nombre: r.name,
    email: r.email,
    fechaAlta: r.fecha_alta,
    joinedAtMs: r.joined_at ? Date.parse(r.joined_at) : 0,
    pierna: r.pierna,
    volumen: formatPv(r.monthly_qualifying_pv),
    estado: estadoLabel(r.account_status),
    estadoRaw: r.account_status,
    rango: r.rank_name || "—",
    iniciales: iniciales(r.name),
    memberCode: r.member_code || "—",
  }))
);

/** Un nivel: todos los directos, orden por fecha de ingreso (primero llega, primero se muestra), luego pierna I/D. */
const referidosOrdenados = computed(() => {
  return [...referidos.value].sort((a, b) => {
    if (a.joinedAtMs !== b.joinedAtMs) return a.joinedAtMs - b.joinedAtMs;
    return legSortKey(a.pierna) - legSortKey(b.pierna);
  });
});

// Matriz unilevel (solo 1ª línea): TU -> S1 -> S2 -> S3 ... en orden de ingreso.
const unilevelChain = computed(() => referidosOrdenados.value);

const childrenBySponsor = computed(() => uniTree.value?.children_by_sponsor || {});

function mapUniNode(n) {
  return {
    id: n.id,
    sponsorId: n.sponsor_id,
    nombre: n.name,
    fechaAlta: n.fecha_alta || "—",
    joinedAtMs: n.joined_at ? Date.parse(n.joined_at) : 0,
    estadoRaw: n.account_status,
    rango: n.rank_name || "—",
    iniciales: iniciales(n.name),
    pv: Number(n.monthly_qualifying_pv || 0),
  };
}

const gen1 = computed(() => (uniTree.value?.levels?.["1"] || []).map(mapUniNode));
const gen2 = computed(() => (uniTree.value?.levels?.["2"] || []).map(mapUniNode));

function statusChipClass(raw) {
  if (raw === "active") return "bg-gradient-success";
  if (raw === "pending") return "bg-gradient-warning";
  return "bg-gradient-secondary";
}

const stats = computed(() => ({
  total: summary.value.total ?? referidos.value.length,
  izquierda: summary.value.izquierda ?? referidos.value.filter((r) => r.pierna === "left").length,
  derecha: summary.value.derecha ?? referidos.value.filter((r) => r.pierna === "right").length,
  activos: summary.value.activos ?? referidos.value.filter((r) => r.estadoRaw === "active").length,
  pendientes: summary.value.pendientes ?? referidos.value.filter((r) => r.estadoRaw === "pending").length,
}));

function piernaEtiqueta(p) {
  if (p === "left") return "Izquierda";
  if (p === "right") return "Derecha";
  return "Sin pierna";
}

function badgeClaseEstado(ref) {
  if (ref.estadoRaw === "active") return "bg-gradient-success";
  if (ref.estadoRaw === "pending") return "bg-gradient-warning";
  return "bg-gradient-secondary";
}
</script>

<template>
  <div class="py-4 container-fluid referidos-page">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm referidos-page__intro">
          <div class="card-body p-4">
            <h4 class="mb-2 text-dark font-weight-bolder">Red unilevel — primer nivel</h4>
            <p class="mb-0 text-sm text-secondary">
              Socios patrocinados directamente por ti. Orden: fecha de ingreso (más reciente primero), luego pierna
              binaria si ya está colocada.
            </p>
            <p v-if="error" class="text-danger text-sm mt-2 mb-0">{{ error }}</p>
            <p v-else-if="loading" class="text-muted text-sm mt-2 mb-0">Cargando…</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-md-6 col-xl-3">
        <mini-statistics-card
          title="Directos"
          :value="String(stats.total)"
          description="<span class='text-sm font-weight-bolder text-primary'>Primer nivel</span>"
          :icon="{
            component: 'ni ni-single-02',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-6 col-md-6 col-xl-3">
        <mini-statistics-card
          title="Activos"
          :value="String(stats.activos)"
          description="<span class='text-sm font-weight-bolder text-success'>Cuenta activa</span>"
          :icon="{
            component: 'ni ni-check-bold',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-6 col-md-6 col-xl-3">
        <mini-statistics-card
          title="Pierna I / D"
          :value="`${stats.izquierda} / ${stats.derecha}`"
          description="<span class='text-sm font-weight-bolder text-info'>Colocados</span>"
          :icon="{
            component: 'ni ni-chart-pie-35',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-6 col-md-6 col-xl-3">
        <mini-statistics-card
          title="Pendientes"
          :value="String(stats.pendientes)"
          description="<span class='text-sm font-weight-bolder text-warning'>Por activar</span>"
          :icon="{
            component: 'ni ni-time-alarm',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <!-- Matrices (solo primera línea) -->
    <div class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header border-0 pb-0 pt-3 px-3 px-md-4 bg-transparent">
            <h6 class="mb-0 font-weight-bolder text-dark">Matriz Unilevel (1.ª línea)</h6>
            <p class="text-xs text-secondary mb-0 mt-1">Primero llega → primero se muestra.</p>
          </div>
          <div class="card-body px-3 px-md-4 pb-4">
            <div class="matrix-unilevel">
              <div class="matrix-node matrix-node--root">
                <div class="matrix-avatar bg-gradient-dark text-white shadow">TU</div>
              </div>
              <template v-if="unilevelChain.length">
                <div v-for="r in unilevelChain" :key="'uni-' + r.id" class="matrix-row">
                  <div class="matrix-connector" aria-hidden="true" />
                  <div class="matrix-node">
                    <div
                      class="matrix-avatar text-white shadow"
                      :class="r.pierna === 'left' ? 'bg-gradient-primary' : r.pierna === 'right' ? 'bg-gradient-success' : 'bg-gradient-secondary'"
                    >
                      {{ r.iniciales }}
                    </div>
                    <div class="matrix-meta min-w-0">
                      <div class="text-sm font-weight-bolder text-dark text-truncate">{{ r.nombre }}</div>
                      <div class="text-xxs text-secondary">{{ r.fechaAlta }} · {{ r.volumen }}</div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-muted mt-3">Aún no tienes socios en 1.ª línea.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header border-0 pb-0 pt-3 px-3 px-md-4 bg-transparent">
            <h6 class="mb-0 font-weight-bolder text-dark">Matriz Unilevel (3 generaciones)</h6>
            <p class="text-xs text-secondary mb-0 mt-1">
              Directos (G1) y descendencias (G2, G3) por patrocinio, en estructura tipo árbol.
            </p>
          </div>
          <div class="card-body px-3 px-md-4 pb-4">
            <div class="uni3">
              <div class="uni3__rootRow">
                <div class="uni3__rootNode">
                  <div class="uni3__avatar bg-gradient-dark text-white shadow">TU</div>
                </div>
              </div>

              <div v-if="gen1.length" class="uni3__grid">
                <div class="uni3__col">
                  <div class="uni3__colTitle">G1</div>
                  <div class="d-grid gap-2">
                    <div v-for="a in gen1" :key="'g1-' + a.id" class="uni3__node">
                      <div class="uni3__avatar bg-gradient-primary text-white shadow">{{ a.iniciales }}</div>
                      <div class="uni3__meta min-w-0">
                        <div class="text-sm font-weight-bolder text-dark text-truncate">{{ a.nombre }}</div>
                        <div class="text-xxs text-secondary">
                          {{ a.fechaAlta }} · {{ a.rango }} · {{ formatPv(a.pv) }}
                        </div>
                      </div>
                      <span class="badge badge-sm ms-auto" :class="statusChipClass(a.estadoRaw)">
                        {{ a.estadoRaw === "active" ? "Activo" : a.estadoRaw === "pending" ? "Pendiente" : "—" }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="uni3__col">
                  <div class="uni3__colTitle">G2</div>
                  <div class="d-grid gap-2">
                    <div v-for="a in gen1" :key="'g2grp-' + a.id">
                      <div v-if="(childrenBySponsor[String(a.id)] || []).length" class="uni3__group">
                        <div class="uni3__groupHint text-xxs text-muted">↓ de {{ a.iniciales }}</div>
                        <div
                          v-for="b in (childrenBySponsor[String(a.id)] || []).map(mapUniNode)"
                          :key="'g2-' + b.id"
                          class="uni3__node"
                        >
                          <div class="uni3__avatar bg-gradient-info text-white shadow">{{ b.iniciales }}</div>
                          <div class="uni3__meta min-w-0">
                            <div class="text-sm font-weight-bolder text-dark text-truncate">{{ b.nombre }}</div>
                            <div class="text-xxs text-secondary">
                              {{ b.fechaAlta }} · {{ b.rango }} · {{ formatPv(b.pv) }}
                            </div>
                          </div>
                          <span class="badge badge-sm ms-auto" :class="statusChipClass(b.estadoRaw)">
                            {{ b.estadoRaw === "active" ? "Activo" : b.estadoRaw === "pending" ? "Pendiente" : "—" }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="uni3__col">
                  <div class="uni3__colTitle">G3</div>
                  <div class="d-grid gap-2">
                    <div v-for="b in gen2" :key="'g3grp-' + b.id">
                      <div v-if="(childrenBySponsor[String(b.id)] || []).length" class="uni3__group">
                        <div class="uni3__groupHint text-xxs text-muted">↓ de {{ b.iniciales }}</div>
                        <div
                          v-for="c in (childrenBySponsor[String(b.id)] || []).map(mapUniNode)"
                          :key="'g3-' + c.id"
                          class="uni3__node"
                        >
                          <div class="uni3__avatar bg-gradient-success text-white shadow">{{ c.iniciales }}</div>
                          <div class="uni3__meta min-w-0">
                            <div class="text-sm font-weight-bolder text-dark text-truncate">{{ c.nombre }}</div>
                            <div class="text-xxs text-secondary">
                              {{ c.fechaAlta }} · {{ c.rango }} · {{ formatPv(c.pv) }}
                            </div>
                          </div>
                          <span class="badge badge-sm ms-auto" :class="statusChipClass(c.estadoRaw)">
                            {{ c.estadoRaw === "active" ? "Activo" : c.estadoRaw === "pending" ? "Pendiente" : "—" }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-sm text-muted mt-2">Aún no tienes socios directos (G1).</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header border-0 pb-0 pt-3 px-3 px-md-4 bg-transparent">
            <h6 class="mb-0 font-weight-bolder text-dark">Línea directa (vista horizontal)</h6>
            <p class="text-xs text-secondary mb-0 mt-1">
              Desplaza horizontalmente en móvil. Cada tarjeta es un referido de primer nivel.
            </p>
          </div>
          <div class="card-body pt-2 pb-4 px-3 px-md-4">
            <div v-if="loading" class="text-muted text-sm py-3">Cargando red…</div>
            <div v-else-if="!referidosOrdenados.length" class="text-muted text-sm py-3">
              Aún no tienes referidos directos. Comparte tu enlace de patrocinio.
            </div>
            <div v-else class="unilevel-rail">
              <div class="unilevel-rail__root">
                <div
                  class="unilevel-avatar unilevel-avatar--root bg-gradient-dark text-white shadow d-flex align-items-center justify-content-center font-weight-bolder"
                >
                  Tú
                </div>
                <span class="unilevel-rail__caption text-xxs text-secondary text-uppercase font-weight-bolder"
                  >Patrocinador</span
                >
              </div>
              <div class="unilevel-rail__connector" aria-hidden="true" />
              <div class="unilevel-rail__scroll">
                <div
                  v-for="ref in referidosOrdenados"
                  :key="ref.id"
                  class="card border-0 shadow-sm unilevel-card h-100"
                  :class="{ 'unilevel-card--inactive': ref.estadoRaw !== 'active' }"
                >
                  <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex align-items-start gap-2 mb-2">
                      <div
                        class="unilevel-avatar unilevel-avatar--member rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-white font-weight-bolder text-xs"
                        :class="
                          ref.pierna === 'right' ? 'bg-gradient-success' : ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-secondary'
                        "
                      >
                        {{ ref.iniciales }}
                      </div>
                      <div class="min-w-0 flex-grow-1">
                        <h6 class="text-sm font-weight-bolder text-dark mb-0 text-truncate">{{ ref.nombre }}</h6>
                        <p class="text-xxs text-secondary mb-0 font-mono">{{ ref.memberCode }}</p>
                      </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                      <span class="badge badge-sm bg-gradient-warning">{{ ref.rango }}</span>
                      <span class="badge badge-sm" :class="badgeClaseEstado(ref)">{{ ref.estado }}</span>
                      <span
                        v-if="ref.pierna"
                        class="badge badge-sm"
                        :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
                      >
                        {{ piernaEtiqueta(ref.pierna) }}
                      </span>
                      <span v-else class="badge badge-sm bg-gradient-secondary">Sin pierna</span>
                    </div>
                    <div class="mt-auto pt-2 border-top border-light">
                      <p class="text-xxs text-secondary mb-0">PV calificación (mes)</p>
                      <p class="text-sm font-weight-bolder text-dark mb-0">{{ ref.volumen }}</p>
                      <p class="text-xxs text-secondary mb-0 mt-1">Ingreso: {{ ref.fechaAlta }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header border-0 pb-0 pt-3 px-3 px-md-4 bg-transparent">
            <h6 class="mb-0 font-weight-bolder text-dark">Listado completo</h6>
            <p class="text-xs text-secondary mb-0 mt-1">Misma información en formato tabla (desktop).</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0 referidos-table">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Socio</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ingreso</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Pierna</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">PV mes</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Rango</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center pe-3">
                    Estado
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ref in referidosOrdenados" :key="ref.id">
                  <td class="ps-3">
                    <div class="d-flex align-items-center py-1">
                      <div
                        class="avatar avatar-sm rounded-circle me-2 d-flex align-items-center justify-content-center text-white font-weight-bolder text-xs flex-shrink-0"
                        :class="
                          ref.pierna === 'right' ? 'bg-gradient-success' : ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-secondary'
                        "
                      >
                        {{ ref.iniciales }}
                      </div>
                      <div class="min-w-0">
                        <h6 class="mb-0 text-sm text-truncate">{{ ref.nombre }}</h6>
                        <p class="text-xxs text-secondary mb-0 text-truncate">{{ ref.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="text-xs font-weight-bold text-secondary">{{ ref.fechaAlta }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span v-if="!ref.pierna" class="badge badge-sm bg-gradient-secondary">—</span>
                    <span
                      v-else
                      class="badge badge-sm"
                      :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
                    >
                      {{ ref.pierna === "left" ? "Izq." : "Der." }}
                    </span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-sm font-weight-bold">{{ ref.volumen }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-warning">{{ ref.rango }}</span>
                  </td>
                  <td class="align-middle text-center pe-3">
                    <span class="badge badge-sm" :class="badgeClaseEstado(ref)">{{ ref.estado }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.referidos-page__intro {
  border-radius: 0.75rem;
}
.text-xxs {
  font-size: 0.65rem;
}
.matrix-unilevel {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}
.matrix-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.matrix-connector {
  width: 22px;
  height: 3px;
  border-radius: 999px;
  background: linear-gradient(90deg, #8898aa 0%, #54b144 100%);
  opacity: 0.9;
}
.matrix-node {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0.75rem;
  border-radius: 0.75rem;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  width: 100%;
  min-width: 0;
}
.matrix-node--root {
  background: transparent;
  border: none;
  padding: 0;
}
.matrix-avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  font-weight: 900;
  font-size: 0.75rem;
}
.matrix-meta {
  min-width: 0;
}
.matrix-binary__branches {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 0.75rem;
}
.matrix-binary__col {
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  padding: 0.75rem;
  background: #fff;
  min-width: 0;
}
.matrix-chip {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0;
  border-bottom: 1px solid #f1f3f5;
  min-width: 0;
}
.matrix-chip:last-child {
  border-bottom: none;
}
.unilevel-rail {
  display: flex;
  align-items: stretch;
  gap: 0.75rem;
  flex-wrap: nowrap;
}
.unilevel-rail__root {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  padding-top: 0.25rem;
}
.unilevel-rail__connector {
  width: 12px;
  flex-shrink: 0;
  align-self: center;
  height: 3px;
  border-radius: 2px;
  background: linear-gradient(90deg, #8898aa 0%, #54b144 100%);
  opacity: 0.85;
}
.unilevel-rail__scroll {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  gap: 0.75rem;
  overflow-x: auto;
  overflow-y: visible;
  padding-bottom: 0.35rem;
  -webkit-overflow-scrolling: touch;
  flex: 1;
  min-width: 0;
}
.unilevel-card {
  flex: 0 0 auto;
  width: 220px;
  max-width: 85vw;
  border-radius: 0.65rem;
  transition: box-shadow 0.2s ease, transform 0.15s ease;
}
.unilevel-card:hover {
  box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.1) !important;
  transform: translateY(-2px);
}
.unilevel-card--inactive {
  opacity: 0.92;
}
.unilevel-avatar {
  width: 44px;
  height: 44px;
  font-size: 0.7rem;
  border-radius: 0.65rem;
}
.unilevel-avatar--root {
  width: 52px;
  height: 52px;
  font-size: 0.75rem;
  border-radius: 50%;
}
.unilevel-avatar--member {
  width: 40px;
  height: 40px;
  font-size: 0.65rem;
}
.referidos-table th,
.referidos-table td {
  vertical-align: middle;
}

.uni3__rootRow {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 0.75rem;
}
.uni3__rootNode {
  display: inline-flex;
  align-items: center;
}
.uni3__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
}
.uni3__colTitle {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #6c757d;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}
.uni3__node {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.6rem;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 0.7rem;
  background: rgba(255, 255, 255, 0.85);
}
.uni3__avatar {
  width: 36px;
  height: 36px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.65rem;
  flex: 0 0 auto;
}
.uni3__meta {
  min-width: 0;
}
.uni3__group {
  padding: 0.4rem;
  border-radius: 0.75rem;
  background: rgba(0, 0, 0, 0.02);
  border: 1px dashed rgba(0, 0, 0, 0.12);
}
.uni3__groupHint {
  margin-bottom: 0.35rem;
}
@media (max-width: 991.98px) {
  .uni3__grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 575.98px) {
  .unilevel-rail {
    flex-direction: column;
    align-items: flex-start;
  }
  .unilevel-rail__connector {
    width: 3px;
    height: 16px;
    align-self: flex-start;
    margin-left: 24px;
    background: linear-gradient(180deg, #8898aa 0%, #54b144 100%);
  }
  .unilevel-rail__scroll {
    width: 100%;
    padding-left: 0;
  }
}
</style>
