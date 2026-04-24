<script setup>
import { ref, computed, onMounted } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import { fetchReferrals, fetchBinaryChildren, searchBinaryTree } from "@/services/me";
import BinaryTreeBranch from "./BinaryTreeBranch.vue";

const loading = ref(true);
const error = ref("");
const items = ref([]);
const summary = ref({ total: 0, activos: 0, pendientes: 0, izquierda: 0, derecha: 0 });
const binaryRoot = ref(null);
const binaryLoading = ref(false);
const binaryError = ref("");
const treeQuery = ref("");
const treeSearching = ref(false);
const treeSearchError = ref("");
const treeContextLabel = ref("Tu árbol");

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const data = await fetchReferrals();
    items.value = data.items || [];
    summary.value = data.summary || summary.value;

    binaryLoading.value = true;
    binaryError.value = "";
    const res = await fetchBinaryChildren(null);
    binaryRoot.value = res?.node || null;
    treeContextLabel.value = "Tu árbol";
  } catch {
    error.value = "No se pudieron cargar los referidos.";
  } finally {
    loading.value = false;
    binaryLoading.value = false;
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

const rootCircleText = computed(() => {
  if (!binaryRoot.value) return "TU";
  if (treeContextLabel.value === "Tu árbol") return "TU";
  const parts = String(binaryRoot.value?.name || "").trim().split(/\s+/).filter(Boolean);
  const a = parts[0]?.[0] || "U";
  const b = parts[1]?.[0] || "";
  return `${a}${b}`.toUpperCase();
});

const stats = computed(() => ({
  total: summary.value.total ?? referidos.value.length,
  izquierda: summary.value.izquierda ?? referidos.value.filter((r) => r.pierna === "left").length,
  derecha: summary.value.derecha ?? referidos.value.filter((r) => r.pierna === "right").length,
  activos: summary.value.activos ?? referidos.value.filter((r) => r.estadoRaw === "active").length,
  pendientes: summary.value.pendientes ?? referidos.value.filter((r) => r.estadoRaw === "pending").length,
}));

async function buscarEnArbol() {
  const q = String(treeQuery.value || "").trim();
  if (!q) return;
  treeSearching.value = true;
  treeSearchError.value = "";
  try {
    const res = await searchBinaryTree(q);
    if (!res?.node) {
      treeSearchError.value = "No se encontró un usuario con ese dato en tu red.";
      return;
    }
    binaryRoot.value = res.node;
    const label = res?.match?.name ? `${res.match.name}` : "Resultado";
    treeContextLabel.value = `Árbol de: ${label}`;
    // Mantener el input (permite refinar) pero seleccionarlo es UX opcional.
  } catch (e) {
    treeSearchError.value = e?.response?.data?.message || "No se pudo buscar en tu red binaria.";
  } finally {
    treeSearching.value = false;
  }
}

async function resetArbol() {
  treeQuery.value = "";
  treeSearchError.value = "";
  treeSearching.value = true;
  try {
    const res = await fetchBinaryChildren(null);
    binaryRoot.value = res?.node || null;
    treeContextLabel.value = "Tu árbol";
  } catch {
    treeSearchError.value = "No se pudo restaurar tu árbol.";
  } finally {
    treeSearching.value = false;
  }
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

    <!-- Árbol binario (principal) -->
    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header border-0 pb-0 pt-3 px-3 px-md-4 bg-transparent">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-2">
              <div class="min-w-0">
                <h6 class="mb-0 font-weight-bolder text-dark">Árbol binario (MLM)</h6>
                <p class="text-xs text-secondary mb-0 mt-1 text-truncate">
                  {{ treeContextLabel }} · expande con “+” cuando lo necesites.
                </p>
              </div>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="input-group input-group-sm referidos-tree-search" role="search">
                  <span class="input-group-text bg-white border-0 text-secondary">
                    <i class="fas fa-search" aria-hidden="true"></i>
                  </span>
                  <input
                    v-model="treeQuery"
                    type="search"
                    class="form-control border-0"
                    placeholder="Buscar usuario o código…"
                    aria-label="Buscar usuario o código"
                    @keydown.enter.prevent="buscarEnArbol"
                  />
                  <button class="btn btn-sm btn-outline-success border-0 fw-bold" :disabled="treeSearching" @click="buscarEnArbol">
                    {{ treeSearching ? "Buscando…" : "Buscar" }}
                  </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="treeSearching" @click="resetArbol">
                  Ver mi árbol
                </button>
              </div>
            </div>
            <p class="text-xs text-secondary mb-0 mt-1">
              Consejo: puedes buscar por <strong>nombre</strong> o <strong>código</strong>. Si existe en tu red, verás solo su árbol.
            </p>
          </div>
          <div class="card-body px-3 px-md-4 pb-4">
            <p v-if="treeSearchError" class="text-danger text-sm mb-2">{{ treeSearchError }}</p>

            <div class="bt-scroll">
              <div class="bt-wrap">
              <div class="bt-root">
                <div class="bt-root__circle bg-gradient-dark text-white shadow">{{ rootCircleText }}</div>
              </div>

              <div v-if="binaryLoading" class="text-muted text-sm mt-2">Cargando árbol binario…</div>
              <div v-else-if="binaryError" class="text-danger text-sm mt-2">{{ binaryError }}</div>

              <div v-else class="bt-grid">
                <div class="bt-col">
                  <div class="bt-col__title">Left Team</div>
                  <div class="bt-col__body">
                    <BinaryTreeBranch v-if="binaryRoot?.left" :node="binaryRoot.left" />
                    <div v-else class="bt-emptySlot">
                      <div class="bt-emptySlot__circle"></div>
                      <div class="text-xxs text-muted mt-1">Sin usuario</div>
                    </div>
                  </div>
                </div>

                <div class="bt-col">
                  <div class="bt-col__title">Right Team</div>
                  <div class="bt-col__body">
                    <BinaryTreeBranch v-if="binaryRoot?.right" :node="binaryRoot.right" />
                    <div v-else class="bt-emptySlot">
                      <div class="bt-emptySlot__circle"></div>
                      <div class="text-xxs text-muted mt-1">Sin usuario</div>
                    </div>
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

/* Árbol binario (Left/Right) */
.referidos-tree-search {
  min-width: min(520px, 100%);
}
.bt-scroll {
  max-height: min(70vh, 680px);
  overflow: auto;
  padding: 0.5rem 0.25rem 0.25rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(255, 255, 255, 0.65);
}
.bt-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  position: relative;
}

.bt-wrap::after {
  content: "";
  position: absolute;
  top: 66px;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  border-left: 2px dashed rgba(17, 24, 39, 0.22);
  pointer-events: none;
}

.bt-root__circle {
  width: 54px;
  height: 54px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  font-weight: 900;
}

.bt-grid {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  align-items: start;
}

.bt-col__title {
  text-align: center;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #111827;
  margin-bottom: 8px;
  font-size: 1.05rem;
}
.bt-col__body {
  display: flex;
  justify-content: center;
}

.bt-emptySlot {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.2rem 0;
}
.bt-emptySlot__circle {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.08);
  border: 2px solid rgba(17, 24, 39, 0.06);
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
  .bt-grid {
    grid-template-columns: 1fr;
  }
  .bt-scroll {
    max-height: min(75vh, 640px);
  }
}
</style>
