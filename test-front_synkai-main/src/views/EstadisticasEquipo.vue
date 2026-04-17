<script setup>
import { computed, onMounted, ref } from "vue";
import { Chart, registerables } from "chart.js";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import { fetchDashboard, fetchReferrals } from "@/services/me";

Chart.register(...registerables);

const loading = ref(true);
const error = ref("");
const dash = ref(null);
const referrals = ref([]);
const chartCanvas = ref(null);
let chart = null;

function formatPv(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return `${n.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`;
}

function formatBs(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", { style: "currency", currency: "BOB", minimumFractionDigits: 2 }).format(n);
}

const binary = computed(() => dash.value?.binary_week ?? null);
const leftPv = computed(() => Number(binary.value?.left_pv || 0));
const rightPv = computed(() => Number(binary.value?.right_pv || 0));
const weakLeg = computed(() => (leftPv.value <= rightPv.value ? "left" : "right"));

// Estimación de “oportunidad perdida” por desbalance (aprox): PV no emparejado × 21% × 7 Bs/PV.
const estimatedLoss = computed(() => {
  const L = leftPv.value;
  const R = rightPv.value;
  const diff = Math.abs(L - R);
  const bobPerPv = 7;
  const rate = 0.21;
  const loss = diff * bobPerPv * rate;
  return { diffPv: diff, lossBob: loss };
});

const rankingMensual = computed(() => {
  const rows = referrals.value
    .map((r) => ({
      id: r.id,
      name: r.name,
      member_code: r.member_code,
      rank_name: r.rank_name || "—",
      pv: Number(r.monthly_qualifying_pv || 0),
      leg: r.pierna || null,
      joined_at: r.joined_at,
    }))
    .sort((a, b) => b.pv - a.pv);
  return rows;
});

const helpHint = computed(() => {
  const side = weakLeg.value === "left" ? "izquierda" : "derecha";
  const diffPv = estimatedLoss.value.diffPv;
  if (!diffPv) {
    return `Tus piernas están balanceadas. Mantén volumen constante en ambos lados.`;
  }
  return `Debes ayudar la pierna ${side}. Estás desbalanceado por ${formatPv(diffPv)} (aprox.).`;
});

const actionPlan = computed(() => {
  const side = weakLeg.value === "left" ? "izquierda" : "derecha";
  const diff = estimatedLoss.value.diffPv;
  if (!diff) {
    return [
      "Mantén compras/ventas distribuidas en ambas piernas.",
      "Activa nuevos socios alternando izquierda/derecha para sostener balance.",
      "Revisa semanalmente el panel de binario para evitar desbalances grandes.",
    ];
  }
  return [
    `Enfoca el volumen de esta semana en la pierna ${side} (ventas personales y del equipo).`,
    `Coloca tus próximos directos preferiblemente en ${side} hasta reducir el desbalance.`,
    "Haz seguimiento a tus top 3 de la pierna débil para que repitan compra (segunda compra = residual).",
  ];
});

function buildChart() {
  chart?.destroy();
  chart = null;
  if (!chartCanvas.value) return;
  chart = new Chart(chartCanvas.value, {
    type: "bar",
    data: {
      labels: ["Izquierda", "Derecha"],
      datasets: [
        {
          label: "PV periodo actual",
          data: [leftPv.value, rightPv.value],
          backgroundColor: ["rgba(94,114,228,0.65)", "rgba(84,177,68,0.65)"],
          borderRadius: 10,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } },
    },
  });
}

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const [d, r] = await Promise.all([fetchDashboard(), fetchReferrals()]);
    dash.value = d;
    referrals.value = r.items || [];
  } catch {
    error.value = "No se pudo cargar estadísticas del equipo.";
  } finally {
    loading.value = false;
    setTimeout(buildChart, 0);
  }
});
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h4 class="text-dark font-weight-bolder mb-1">Estadísticas del equipo</h4>
            <p class="text-sm text-secondary mb-0">
              Ranking mensual (1.ª línea) + recomendación de pierna a impulsar según tu volumen binario actual.
            </p>
            <p v-if="error" class="text-danger text-sm mt-2 mb-0">{{ error }}</p>
            <p v-else-if="loading" class="text-muted text-sm mt-2 mb-0">Cargando…</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading" class="row g-3 mb-4">
      <div class="col-md-4">
        <mini-statistics-card
          title="Pierna a ayudar"
          :value="weakLeg === 'left' ? 'Izquierda' : 'Derecha'"
          description="<span class='text-sm font-weight-bolder text-success'>Recomendación</span> binaria"
          :icon="{ component: 'ni ni-bold-right', background: 'bg-gradient-success', shape: 'rounded-circle' }"
        />
      </div>
      <div class="col-md-4">
        <mini-statistics-card
          title="Desbalance PV"
          :value="formatPv(estimatedLoss.diffPv)"
          description="<span class='text-sm font-weight-bolder text-info'>No emparejado</span> (aprox.)"
          :icon="{ component: 'ni ni-chart-bar-32', background: 'bg-gradient-info', shape: 'rounded-circle' }"
        />
      </div>
      <div class="col-md-4">
        <mini-statistics-card
          title="Oportunidad"
          :value="formatBs(estimatedLoss.lossBob)"
          description="<span class='text-sm font-weight-bolder text-warning'>Estimación</span> por balance"
          :icon="{ component: 'ni ni-money-coins', background: 'bg-gradient-warning', shape: 'rounded-circle' }"
        />
      </div>
    </div>

    <div v-if="!loading" class="row mb-4">
      <div class="col-12">
        <div class="alert alert-secondary border-0 text-dark">
          <strong>Autoayuda:</strong> {{ helpHint }}
          <div class="text-xs text-muted mt-1">
            Nota: estimación basada en \(PV\\_no\\_emparejado × 21% × 7 Bs/PV\). Ajusta si tu plan cambia.
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading" class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Volumen por pierna (periodo actual)</h6>
            <p class="text-xs text-secondary mb-0">Comparativa izquierda vs derecha.</p>
          </div>
          <div class="card-body" style="min-height: 280px">
            <canvas ref="chartCanvas" />
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Recomendaciones accionables</h6>
            <p class="text-xs text-secondary mb-0">Qué hacer hoy para nivelar tu desbalance.</p>
          </div>
          <div class="card-body">
            <ol class="text-sm text-dark mb-0 ps-3">
              <li v-for="(tip, idx) in actionPlan" :key="idx" class="mb-2">{{ tip }}</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading" class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header pb-0">
            <h6 class="mb-0 text-dark font-weight-bolder">Ranking mensual — 1.ª línea</h6>
            <p class="text-xs text-secondary mb-0">Ordenado por PV mensual (mayor a menor).</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs ps-3">#</th>
                  <th class="text-uppercase text-secondary text-xxs">Socio</th>
                  <th class="text-uppercase text-secondary text-xxs text-center">Pierna</th>
                  <th class="text-uppercase text-secondary text-xxs text-center">Rango</th>
                  <th class="text-uppercase text-secondary text-xxs pe-3 text-end">PV mes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, idx) in rankingMensual" :key="r.id">
                  <td class="ps-3 text-sm">{{ idx + 1 }}</td>
                  <td class="text-sm">
                    <div class="fw-semibold text-dark">{{ r.name }}</div>
                    <div class="text-xs text-muted">{{ r.member_code }}</div>
                  </td>
                  <td class="text-center text-sm">
                    <span class="badge badge-sm" :class="r.leg === 'left' ? 'bg-gradient-primary' : r.leg === 'right' ? 'bg-gradient-success' : 'bg-gradient-secondary'">
                      {{ r.leg === "left" ? "Izq." : r.leg === "right" ? "Der." : "—" }}
                    </span>
                  </td>
                  <td class="text-center text-sm">
                    <span class="badge badge-sm bg-gradient-warning">{{ r.rank_name }}</span>
                  </td>
                  <td class="pe-3 text-end text-sm font-weight-bold">{{ formatPv(r.pv) }}</td>
                </tr>
                <tr v-if="!rankingMensual.length">
                  <td colspan="5" class="text-center text-muted py-4 text-sm">Aún no hay socios en tu 1.ª línea.</td>
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
.text-xxs {
  font-size: 0.65rem;
}
</style>

