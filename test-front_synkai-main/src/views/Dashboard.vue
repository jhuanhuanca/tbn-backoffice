<script setup>
import { ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import GradientLineChart from "@/examples/Charts/GradientLineChart.vue";
import Carousel from "./components/Carousel.vue";
import CategoriesList from "./components/CategoriesList.vue";
import { fetchDashboard } from "@/services/me";

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

const user = computed(() => store.state.auth.user);

const kpis = computed(() => {
  const d = dashboard.value;
  const w = d?.wallet?.available;
  const bw = d?.binary_week;
  const rc = d?.referrals_direct_count ?? 0;
  const rankName = d?.rank?.name || user.value?.rank?.name || "—";
  const act =
    d?.user?.is_mlm_qualified || user.value?.is_mlm_qualified ? "Calificado" : "Pendiente";

  return {
    availableBalance: loading.value ? "…" : formatBs(w),
    dailyEarnings: "—",
    totalEarnings: d && d.commissions_total != null ? formatBs(d.commissions_total) : "—",
    leftVolume: bw ? formatPv(bw.left_pv) : "—",
    rightVolume: bw ? formatPv(bw.right_pv) : "—",
    currentRank: rankName,
    directAffiliates: String(rc),
    activityStatus: act,
  };
});

const binaryLegs = computed(() => {
  const bw = dashboard.value?.binary_week;
  if (!bw) {
    return [];
  }
  return [
    {
      side: "Izquierda",
      volume: formatPv(bw.left_pv),
      carryOver: formatPv(bw.left_carry_in),
      matched: "—",
      status: "Semana " + (bw.week_key || ""),
    },
    {
      side: "Derecha",
      volume: formatPv(bw.right_pv),
      carryOver: formatPv(bw.right_carry_in),
      matched: "—",
      status: "Semana " + (bw.week_key || ""),
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
    label: "Volumen binario (semana ISO)",
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
  } catch {
    loadError.value = "No se pudo cargar el panel. Verifica el servidor y tu sesión.";
    dashboard.value = null;
  } finally {
    loading.value = false;
  }
});
</script>
<template>
  <div class="py-4 container-fluid">
    <div v-if="loadError" class="alert alert-warning text-white mb-3" role="alert">
      {{ loadError }}
    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-12">
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
          <div class="col-lg-3 col-md-6 col-12">
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
          <div class="col-lg-3 col-md-6 col-12">
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
          <div class="col-lg-3 col-md-6 col-12">
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
        <div class="row mt-4">
          <div class="col-lg-3 col-md-6 col-12">
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
          <div class="col-lg-3 col-md-6 col-12">
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
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Estado MLM"
              :value="kpis.activityStatus"
              description="<span class='text-sm font-weight-bolder'>PV mensual</span>"
              :icon="{
                component: 'ni ni-check-bold',
                background: 'bg-gradient-danger',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
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
        <div class="row">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <div class="card z-index-2">
              <gradient-line-chart
                id="chart-line"
                title="Volumen binario (semana actual)"
                description="<span class='font-weight-bold'>Izquierda vs derecha</span> (PV)"
                :chart="chartData"
              />
            </div>
          </div>
          <div class="col-lg-5 col-12 d-none d-lg-block">
            <carousel />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <div class="card">
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
            <CategoriesList :categories="categoriesList" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
