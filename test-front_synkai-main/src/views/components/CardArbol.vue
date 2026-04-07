<script setup>
import { ref, computed, onMounted } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import DefaultInfoCard from "@/examples/Cards/DefaultInfoCard.vue";
import BinaryTreeBranch from "./BinaryTreeBranch.vue";
import { fetchBinaryTree } from "@/services/me";

const loading = ref(true);
const error = ref("");
const payload = ref(null);

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    payload.value = await fetchBinaryTree();
  } catch {
    error.value = "No se pudo cargar el árbol binario. Inicia sesión e inténtalo de nuevo.";
  } finally {
    loading.value = false;
  }
});

const tree = computed(() => payload.value?.tree);
const stats = computed(() => payload.value?.stats || {});
const birPct = computed(() => payload.value?.bir_percentages || { 1: 0.21, 2: 0.15, 3: 0.06 });

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
              Colocación según <code>binary_placements</code> (tu posición y dos niveles).
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
              <div class="tree-children d-flex justify-content-around gap-3 flex-wrap">
                <div class="text-center flex-fill">
                  <div class="text-xs text-primary font-weight-bolder mb-2">Izquierda</div>
                  <BinaryTreeBranch v-if="tree.left" :branch="tree.left" />
                  <span v-else class="text-xs text-muted">Sin colocación</span>
                </div>
                <div class="text-center flex-fill">
                  <div class="text-xs text-success font-weight-bolder mb-2">Derecha</div>
                  <BinaryTreeBranch v-if="tree.right" :branch="tree.right" />
                  <span v-else class="text-xs text-muted">Sin colocación</span>
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
</style>
