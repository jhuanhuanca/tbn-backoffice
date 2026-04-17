<script setup>
import { computed, onMounted, ref } from "vue";
import { Chart, registerables } from "chart.js";
import { fetchWalletTransactions } from "@/services/me";
import api from "@/services/api";

Chart.register(...registerables);

const loading = ref(true);
const error = ref("");
const withdrawals = ref([]);
const tx = ref([]);

const chartCanvas = ref(null);
let chart = null;

function sameMonth(a, b) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth();
}

const monthKpis = computed(() => {
  const now = new Date();
  let earned = 0;
  let withdrew = 0;

  // Ganancias: movimientos positivos en billetera del mes (aprox)
  tx.value.forEach((row) => {
    const dt = row.created_at ? new Date(row.created_at) : null;
    if (!dt || Number.isNaN(dt.getTime()) || !sameMonth(dt, now)) return;
    const amt = Number(row.amount || 0);
    if (amt > 0) earned += amt;
  });

  // Retiros: solicitudes del mes (pendiente o completado)
  withdrawals.value.forEach((w) => {
    const dt = w.created_at ? new Date(w.created_at) : null;
    if (!dt || Number.isNaN(dt.getTime()) || !sameMonth(dt, now)) return;
    const estado = String(w.estado || "").toLowerCase();
    if (estado === "rechazado") return;
    withdrew += Number(w.monto || 0);
  });

  return {
    monthLabel: now.toLocaleDateString("es-BO", { month: "long", year: "numeric" }),
    earned,
    withdrew,
    balance: earned - withdrew,
  };
});

function formatBs(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", { style: "currency", currency: "BOB", minimumFractionDigits: 2 }).format(n);
}

function monthKey(d) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  return `${y}-${m}`;
}

const monthlyBalance = computed(() => {
  const map = new Map();
  tx.value.forEach((row) => {
    const dt = row.created_at ? new Date(row.created_at) : null;
    if (!dt || Number.isNaN(dt.getTime())) return;
    const k = monthKey(dt);
    const prev = map.get(k) || 0;
    map.set(k, prev + Number(row.amount || 0));
  });
  const keys = Array.from(map.keys()).sort();
  return keys.map((k) => ({ month: k, amount: map.get(k) }));
});

function buildChart() {
  chart?.destroy();
  chart = null;
  if (!chartCanvas.value) return;
  const data = monthlyBalance.value;
  if (!data.length) return;
  chart = new Chart(chartCanvas.value, {
    type: "bar",
    data: {
      labels: data.map((r) => r.month),
      datasets: [
        {
          label: "Balance mensual (BOB) — movimientos de billetera",
          data: data.map((r) => Number(r.amount)),
          backgroundColor: "rgba(84, 177, 68, 0.65)",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: false } },
    },
  });
}

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const [wRes, txRes] = await Promise.all([
      api.get("/withdrawals", { params: { per_page: 50 } }),
      fetchWalletTransactions(),
    ]);
    withdrawals.value = wRes.data?.data || [];
    tx.value = txRes.data || [];
  } catch {
    error.value = "No se pudo cargar el historial de retiros.";
    withdrawals.value = [];
    tx.value = [];
  } finally {
    loading.value = false;
    setTimeout(buildChart, 0);
  }
}

onMounted(load);
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h4 class="text-dark font-weight-bolder mb-1">Retiros</h4>
            <p class="text-sm text-secondary mb-0">
              Historial de solicitudes y gráfico de balance mensual (según movimientos de billetera).
            </p>
            <p v-if="error" class="text-danger text-sm mt-2 mb-0">{{ error }}</p>
            <p v-else-if="loading" class="text-muted text-sm mt-2 mb-0">Cargando…</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading" class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Balance mensual</h6>
            <p class="text-xs text-secondary mb-0">
              Este mes ({{ monthKpis.monthLabel }}): ganaste <strong>{{ formatBs(monthKpis.earned) }}</strong>, retiraste
              <strong>{{ formatBs(monthKpis.withdrew) }}</strong>, balance <strong>{{ formatBs(monthKpis.balance) }}</strong>.
            </p>
          </div>
          <div class="card-body" style="min-height: 280px">
            <canvas ref="chartCanvas" />
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Historial de retiros</h6>
            <p class="text-xs text-secondary mb-0">Últimas 50 solicitudes.</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs ps-3">#</th>
                  <th class="text-uppercase text-secondary text-xxs">Monto</th>
                  <th class="text-uppercase text-secondary text-xxs text-center">Estado</th>
                  <th class="text-uppercase text-secondary text-xxs pe-3 text-end">Fecha</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="w in withdrawals" :key="w.id">
                  <td class="ps-3 text-sm">{{ w.id }}</td>
                  <td class="text-sm font-weight-bold">{{ formatBs(w.monto) }}</td>
                  <td class="text-center">
                    <span class="badge badge-sm" :class="w.estado === 'pendiente' ? 'bg-gradient-warning' : w.estado === 'completado' ? 'bg-gradient-success' : 'bg-secondary'">
                      {{ w.estado }}
                    </span>
                  </td>
                  <td class="pe-3 text-end text-sm text-muted">
                    {{ w.created_at ? new Date(w.created_at).toLocaleDateString("es-BO") : "—" }}
                  </td>
                </tr>
                <tr v-if="!withdrawals.length">
                  <td colspan="4" class="text-center text-muted py-4 text-sm">Aún no hay retiros.</td>
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

