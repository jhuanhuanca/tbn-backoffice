<script setup>
import { ref, computed, onMounted } from "vue";
import { RouterLink } from "vue-router";
import { useStore } from "vuex";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import GradientLineChart from "@/examples/Charts/GradientLineChart.vue";
import Carousel from "./components/Carousel.vue";
import CategoriesList from "./components/CategoriesList.vue";
import { fetchDashboard, fetchProfile } from "@/services/me";
import { useMlmLiveRefresh } from "@/composables/useMlmLiveRefresh";

const store = useStore();
const loading = ref(true);
const loadError = ref(null);
const dashboard = ref(null);

function formatBs(value) {
  if (value === null || value === undefined || value === "") {
    return "—";
  }
  const n = Number(value);
  if (Number.isNaN(n)) {
    return String(value);
  }
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(n);
}

function formatPv(v) {
  if (v === null || v === undefined) {
    return "—";
  }
  const n = Number(v);
  if (Number.isNaN(n)) {
    return "—";
  }
  return `${n.toLocaleString("es-BO")} PV`;
}

function formatUsd(value) {
  if (value === null || value === undefined || value === "") {
    return "—";
  }
  const n = Number(value);
  if (Number.isNaN(n)) {
    return String(value);
  }
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
  }).format(n);
}

const plataBanner = computed(() => dashboard.value?.bonus_progress?.plata_onboarding ?? null);

const progressRank = computed(() => dashboard.value?.bonus_progress?.progress_bars?.rank_ascent ?? null);

const progressAutoOkm = computed(() => dashboard.value?.bonus_progress?.progress_bars?.auto_okm ?? null);

const progressPlataWindow = computed(
  () => dashboard.value?.bonus_progress?.progress_bars?.plata_window ?? null,
);

const user = computed(() => store.state.auth.user);

const firstName = computed(() => {
  const n = user.value?.name || dashboard.value?.user?.name || "";
  const part = String(n).trim().split(/\s+/)[0];
  return part || "Socio";
});

const quickLinks = [
  { to: "/cardreferidos", label: "Referidos", icon: "ni ni-single-02", grad: "primary" },
  { to: "/cardarbol", label: "Árbol binario", icon: "ni ni-chart-pie-35", grad: "success" },
  { to: "/comisiones", label: "Comisiones", icon: "ni ni-money-coins", grad: "warning" },
  { to: "/billetera", label: "Billetera", icon: "ni ni-wallet-43", grad: "info" },
  { to: "/linkreferidos", label: "Mi enlace", icon: "ni ni-send", grad: "dark" },
  { to: "/productos", label: "Productos", icon: "ni ni-basket", grad: "danger" },
];

/** Proporción Izq/Der para barra comparativa (misma paleta). */
const binaryBar = computed(() => {
  const bw = dashboard.value?.binary_week;
  const L = bw ? Number(bw.left_pv) || 0 : 0;
  const R = bw ? Number(bw.right_pv) || 0 : 0;
  const t = L + R;
  if (t <= 0) {
    return { leftPct: 50, rightPct: 50, L: 0, R: 0 };
  }
  const leftPct = Math.round((L / t) * 100);
  return { leftPct, rightPct: 100 - leftPct, L, R };
});

const kpis = computed(() => {
  const d = dashboard.value;
  const w = d?.wallet?.available;
  const bw = d?.binary_week;
  const rc = d?.referrals_direct_count ?? 0;
  const rankName = d?.rank?.name || user.value?.rank?.name || "—";
  const act =
    d?.user?.is_mlm_qualified || user.value?.is_mlm_qualified ? "Calificado" : "Pendiente";

  const pvRaw = d?.user?.monthly_qualifying_pv ?? user.value?.monthly_qualifying_pv;

  return {
    availableBalance: loading.value ? "…" : formatBs(w),
    dailyEarnings: "—",
    totalEarnings: d && d.commissions_total != null ? formatBs(d.commissions_total) : "—",
    leftVolume: bw ? formatPv(bw.left_pv) : "—",
    rightVolume: bw ? formatPv(bw.right_pv) : "—",
    currentRank: rankName,
    directAffiliates: String(rc),
    activityStatus: act,
    monthlyPvDisplay: loading.value ? "…" : formatPv(pvRaw),
  };
});

const binaryLegs = computed(() => {
  const bw = dashboard.value?.binary_week;
  if (!bw) {
    return [];
  }
  const periodLabel =
    bw.volume_period === "monthly" ? "Mes " + (bw.week_key || "") : "Semana " + (bw.week_key || "");
  return [
    {
      side: "Izquierda",
      volume: formatPv(bw.left_pv),
      carryOver: formatPv(bw.left_carry_in),
      matched: "—",
      status: periodLabel,
    },
    {
      side: "Derecha",
      volume: formatPv(bw.right_pv),
      carryOver: formatPv(bw.right_carry_in),
      matched: "—",
      status: periodLabel,
    },
  ];
});

const chartData = computed(() => {
  const bw = dashboard.value?.binary_week;
  const L = bw ? Number(bw.left_pv) || 0 : 0;
  const R = bw ? Number(bw.right_pv) || 0 : 0;
  return {
    labels: ["Actual"],
    datasets: [
      { label: "Volumen Izq.", data: [L] },
      { label: "Volumen Der.", data: [R] },
    ],
  };
});

const sponsorCardDescription = computed(() => {
  const c = dashboard.value?.sponsor?.referral_code;
  return (
    "<span class='text-sm font-weight-bolder text-secondary'>Código</span> " + (c || "—")
  );
});

const categoriesList = computed(() => [
  {
    icon: { component: "ni ni-credit-card", background: "dark" },
    label: "Saldo y retiros",
    description: "Disponible <strong>" + kpis.value.availableBalance + "</strong>",
  },
  {
    icon: { component: "ni ni-chart-bar-32", background: "dark" },
    label: "Código de socio",
    description:
      "Tu código: <strong>" +
      (dashboard.value?.user?.referral_code || user.value?.referral_code || "—") +
      "</strong>",
  },
  {
    icon: { component: "ni ni-box-2", background: "dark" },
    label: "Volumen binario (periodo actual)",
    description:
      "Izq <strong>" +
      kpis.value.leftVolume +
      "</strong> · Der <strong>" +
      kpis.value.rightVolume +
      "</strong>",
  },
  {
    icon: { component: "ni ni-badge", background: "dark" },
    label: "Rango y actividad",
    description:
      "Rango <strong>" +
      kpis.value.currentRank +
      "</strong> · <strong>" +
      kpis.value.activityStatus +
      "</strong>",
  },
]);

async function refreshDashboardSilent() {
  try {
    const [data, prof] = await Promise.all([fetchDashboard(), fetchProfile()]);
    dashboard.value = data;
    await store.dispatch("auth/setAuth", {
      user: prof,
      token: localStorage.getItem("token"),
    });
  } catch {
    /* mantiene último snapshot */
  }
}

useMlmLiveRefresh(refreshDashboardSilent, 15000);

onMounted(async () => {
  if (!localStorage.getItem("token")) {
    loadError.value = "Inicia sesión para ver tu panel.";
    loading.value = false;
    return;
  }
  loading.value = true;
  loadError.value = null;
  try {
    const data = await fetchDashboard();
    dashboard.value = data;
    const prof = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: prof,
      token: localStorage.getItem("token"),
    });
  } catch {
    loadError.value = "No se pudo cargar el panel. Verifica el servidor y tu sesión.";
    dashboard.value = null;
  } finally {
    loading.value = false;
  }
});
</script>
<template>
  <div class="py-4 container-fluid dashboard-home">
    <div v-if="loadError" class="alert alert-warning text-white mb-3" role="alert">
      {{ loadError }}
    </div>

    <div v-if="!loadError" class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-lg overflow-hidden dashboard-home__hero">
          <div class="card-body p-4 p-lg-5 position-relative">
            <div class="row align-items-center position-relative" style="z-index: 1">
              <div class="col-lg-8">
                <p class="text-white text-xs font-weight-bolder text-uppercase mb-2 opacity-8 letter-space-1">
                  Panel de socio
                </p>
                <h3 class="text-white font-weight-bolder mb-2 dashboard-home__title">
                  Hola, {{ loading ? "…" : firstName }}
                </h3>
                <p class="text-white text-sm mb-0 opacity-9 pe-lg-5">
                  Tu negocio en un vistazo: saldo, volumen binario y red. Los datos se refrescan automáticamente mientras
                  navegas.
                </p>
              </div>
              <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                <span class="badge bg-white text-success px-3 py-2 font-weight-bold shadow-sm dashboard-home__live">
                  <span class="dashboard-home__pulse me-2" aria-hidden="true" />
                  En vivo
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loadError" class="row g-2 g-md-3 mb-4 dashboard-home__quick">
      <div v-for="q in quickLinks" :key="q.to" class="col-6 col-md-4 col-xl-2">
        <RouterLink :to="q.to" class="text-decoration-none d-block h-100">
          <div
            class="card border-0 shadow-sm h-100 dashboard-home__quick-card text-center py-3 px-2"
            :class="`border-start border-3 border-${q.grad === 'dark' ? 'secondary' : q.grad}`"
          >
            <div
              class="icon icon-shape rounded-circle mx-auto mb-2 shadow text-white d-flex align-items-center justify-content-center"
              :class="`bg-gradient-${q.grad}`"
              style="width: 44px; height: 44px"
            >
              <i :class="q.icon" aria-hidden="true" />
            </div>
            <span class="text-dark text-xs font-weight-bolder d-block">{{ q.label }}</span>
          </div>
        </RouterLink>
      </div>
    </div>

    <div v-if="!loadError && !loading && binaryLegs.length" class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <h6 class="mb-0 font-weight-bolder text-dark">Balance de piernas (periodo actual)</h6>
              <span class="text-xs text-secondary">{{ binaryLegs[0]?.status }}</span>
            </div>
            <div class="d-flex rounded-pill overflow-hidden shadow-inset dashboard-home__balance-bar" role="img">
              <div
                class="dashboard-home__balance-seg dashboard-home__balance-seg--left text-center text-white text-xs font-weight-bold py-2"
                :style="{ width: binaryBar.leftPct + '%' }"
              >
                Izq. {{ binaryBar.leftPct }}%
              </div>
              <div
                class="dashboard-home__balance-seg dashboard-home__balance-seg--right text-center text-white text-xs font-weight-bold py-2"
                :style="{ width: binaryBar.rightPct + '%' }"
              >
                Der. {{ binaryBar.rightPct }}%
              </div>
            </div>
            <div class="d-flex justify-content-between mt-2 text-xs text-secondary">
              <span>{{ formatPv(String(binaryBar.L)) }}</span>
              <span>{{ formatPv(String(binaryBar.R)) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="plataBanner?.show"
      class="alert alert-info text-white mb-3"
      role="alert"
    >
      <strong class="text-white">{{ plataBanner.title }}</strong>
      <p class="mb-0 mt-2 text-sm text-white opacity-9">
        {{ plataBanner.message }}
      </p>
    </div>
    <div v-if="progressRank && progressAutoOkm" class="card border-0 shadow-sm mb-4 dashboard-home__lift">
      <div class="card-body">
        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">
          Progreso de carrera y bonos
        </h6>
        <p class="text-sm mb-1">{{ progressRank.label }}</p>
        <p class="text-xs text-muted mb-1">{{ progressRank.subtitle }}</p>
        <div class="progress mb-4" style="height: 12px">
          <div
            class="progress-bar bg-gradient-success"
            role="progressbar"
            :style="{ width: Math.min(100, progressRank.percent || 0) + '%' }"
            :aria-valuenow="progressRank.percent"
            aria-valuemin="0"
            aria-valuemax="100"
          />
        </div>
        <p class="text-sm mb-1">{{ progressAutoOkm.label }}</p>
        <p class="text-xs text-muted mb-1">
          Acumulado ≈ {{ formatUsd(progressAutoOkm.earned_usd) }} de
          {{ formatUsd(progressAutoOkm.target_usd) }}
          (comisiones en BOB: {{ formatBs(progressAutoOkm.earned_bob) }} · tipo
          {{ progressAutoOkm.bob_per_usd }} BOB/USD)
        </p>
        <div class="progress mb-0" style="height: 12px">
          <div
            class="progress-bar bg-gradient-warning"
            role="progressbar"
            :style="{ width: Math.min(100, progressAutoOkm.percent || 0) + '%' }"
            :aria-valuenow="progressAutoOkm.percent"
            aria-valuemin="0"
            aria-valuemax="100"
          />
        </div>
        <template v-if="plataBanner?.show && progressPlataWindow">
          <p class="text-sm mb-1 mt-4">{{ progressPlataWindow.label }}</p>
          <p class="text-xs text-muted mb-1">{{ progressPlataWindow.subtitle }}</p>
          <div class="progress" style="height: 10px">
            <div
              class="progress-bar bg-gradient-info"
              role="progressbar"
              :style="{ width: Math.min(100, progressPlataWindow.percent || 0) + '%' }"
            />
          </div>
        </template>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="row dashboard-home__kpi-row">
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Saldo disponible"
              :value="kpis.availableBalance"
              description="<span class='text-sm font-weight-bolder text-success'>Ledger</span> en BOB"
              :icon="{
                component: 'ni ni-money-coins',
                background: 'bg-gradient-primary',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Comisiones (histórico)"
              :value="kpis.totalEarnings"
              description="<span class='text-sm font-weight-bolder text-secondary'>Ver detalle</span> en Comisiones"
              :icon="{
                component: 'ni ni-chart-bar-32',
                background: 'bg-gradient-danger',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Referidos directos"
              :value="kpis.directAffiliates"
              description="<span class='text-sm font-weight-bolder text-success'>Red</span> unilevel"
              :icon="{
                component: 'ni ni-trophy',
                background: 'bg-gradient-success',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Rango"
              :value="kpis.currentRank"
              description="<span class='text-sm font-weight-bolder text-info'>Plan</span> activo"
              :icon="{
                component: 'ni ni-badge',
                background: 'bg-gradient-warning',
                shape: 'rounded-circle',
              }"
            />
          </div>
        </div>
        <div class="row mt-4 dashboard-home__kpi-row">
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Volumen izquierdo"
              :value="kpis.leftVolume"
              description="<span class='text-sm font-weight-bolder text-primary'>Semana</span> actual"
              :icon="{
                component: 'ni ni-bold-left',
                background: 'bg-gradient-primary',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Volumen derecho"
              :value="kpis.rightVolume"
              description="<span class='text-sm font-weight-bolder text-primary'>Semana</span> actual"
              :icon="{
                component: 'ni ni-bold-right',
                background: 'bg-gradient-info',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="PV mensual (calificación)"
              :value="kpis.monthlyPvDisplay"
              :description="'<span class=\'text-sm font-weight-bolder text-secondary\'>Estado:</span> ' + kpis.activityStatus + ' · se actualiza al comprar'"
              :icon="{
                component: 'ni ni-check-bold',
                background: 'bg-gradient-danger',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12 dashboard-home__kpi">
            <mini-statistics-card
              title="Patrocinador"
              :value="dashboard?.sponsor?.name || '—'"
              :description="sponsorCardDescription"
              :icon="{
                component: 'ni ni-single-02',
                background: 'bg-gradient-success',
                shape: 'rounded-circle',
              }"
            />
          </div>
        </div>
        <div class="row align-items-stretch">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <div class="card z-index-2 border-0 shadow-sm h-100 dashboard-home__lift">
              <gradient-line-chart
                id="chart-line"
                title="Volumen binario (semana actual)"
                description="<span class='font-weight-bold'>Izquierda vs derecha</span> (PV)"
                :chart="chartData"
              />
            </div>
          </div>
          <div class="col-lg-5 col-12 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100 overflow-hidden dashboard-home__lift">
              <carousel />
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <div class="card border-0 shadow-sm dashboard-home__lift">
              <div class="p-3 pb-0 card-header">
                <h6 class="mb-2">Piernas binarias (volumen semanal)</h6>
                <p class="text-xs text-muted mb-0">Arrastre = carry de la semana ISO anterior.</p>
              </div>
              <div class="table-responsive">
                <table class="table align-items-center">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                        Pierna
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Volumen
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Arrastre
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Periodo
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(leg, index) in binaryLegs" :key="index">
                      <td class="w-30">
                        <h6 class="mb-0 text-sm">{{ leg.side }}</h6>
                      </td>
                      <td class="align-middle text-center text-sm">{{ leg.volume }}</td>
                      <td class="align-middle text-center text-sm">{{ leg.carryOver }}</td>
                      <td class="align-middle text-center text-sm">{{ leg.status }}</td>
                    </tr>
                    <tr v-if="binaryLegs.length === 0">
                      <td colspan="4" class="text-center text-muted py-4 text-sm">
                        Sin datos de volumen (sin pedidos o sin colocación binaria).
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-5 col-12">
            <div class="dashboard-home__lift h-100">
              <CategoriesList :categories="categoriesList" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-home__hero .card-body {
  background: linear-gradient(125deg, #1a1f36 0%, #222d25 38%, #2d5a35 72%, #54b144 100%);
}
.dashboard-home__title {
  letter-spacing: -0.02em;
}
.dashboard-home__pulse {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #54b144;
  box-shadow: 0 0 0 0 rgba(84, 177, 68, 0.7);
  animation: dashboardPulse 2s ease-out infinite;
  vertical-align: middle;
}
@keyframes dashboardPulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(84, 177, 68, 0.5);
  }
  70% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(84, 177, 68, 0);
  }
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(84, 177, 68, 0);
  }
}
.letter-space-1 {
  letter-spacing: 0.06em;
}
.dashboard-home__quick-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 0.65rem;
}
.dashboard-home__quick-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.12) !important;
}
.dashboard-home__balance-bar {
  min-height: 40px;
  background: #e9ecef;
}
.dashboard-home__balance-seg--left {
  background: linear-gradient(90deg, #5e72e4, #11cdef);
  min-width: 2.5rem;
}
.dashboard-home__balance-seg--right {
  background: linear-gradient(90deg, #2dce89, #54b144);
  min-width: 2.5rem;
}
.dashboard-home__lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.dashboard-home__lift:hover {
  transform: translateY(-2px);
}
.dashboard-home__kpi {
  animation: dashboardFadeUp 0.55s ease both;
}
.dashboard-home__kpi-row .dashboard-home__kpi:nth-child(1) {
  animation-delay: 0.02s;
}
.dashboard-home__kpi-row .dashboard-home__kpi:nth-child(2) {
  animation-delay: 0.08s;
}
.dashboard-home__kpi-row .dashboard-home__kpi:nth-child(3) {
  animation-delay: 0.14s;
}
.dashboard-home__kpi-row .dashboard-home__kpi:nth-child(4) {
  animation-delay: 0.2s;
}
@keyframes dashboardFadeUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
