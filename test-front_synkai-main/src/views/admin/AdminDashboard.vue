<script setup>
import { computed, onMounted, onBeforeUnmount, ref, nextTick } from "vue";
import { useStore } from "vuex";
import { Chart, registerables } from "chart.js";
import { fetchAdminDashboard, fetchAdminWithdrawals } from "@/services/admin";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";

Chart.register(...registerables);

const store = useStore();
const loading = ref(true);
const error = ref("");
const stats = ref({
  users_total: 0,
  withdrawals_pending: 0,
  orders_today: 0,
  orders_revenue_total: "0",
  commissions_paid_total: "0",
  users_new_this_month: 0,
  binary_volume_current_period: "0",
  binary_period_key: "",
  charts: {
    sales_last_6_months: [],
    commissions_by_type: {},
    rank_distribution: [],
  },
  top_members: [],
});
const mostrarGuia = ref(false);
const retirosPreview = ref([]);
const retirosPreviewError = ref("");

const salesCanvas = ref(null);
const commissionsCanvas = ref(null);
const ranksCanvas = ref(null);
let chartSales = null;
let chartCommissions = null;
let chartRanks = null;

const mlmRole = computed(() => store.getters["auth/mlmRole"]);
const rolEtiqueta = computed(() => {
  const r = mlmRole.value;
  if (r === "superadmin") return "Superadministrador";
  if (r === "admin") return "Administrador";
  if (r === "support") return "Soporte empresa";
  return r || "—";
});

const modulos = [
  {
    to: "/admin/productos",
    title: "Productos",
    text: "Alta, edición y desactivación del catálogo (PV, precio, categoría).",
    icon: "ni ni-box-2",
    color: "primary",
  },
  {
    to: "/admin/paquetes",
    title: "Paquetes",
    text: "Básico, avanzado, profesional y fundador (PV y precios BOB).",
    icon: "ni ni-gift",
    color: "success",
  },
  {
    to: "/admin/pedidos",
    title: "Pedidos (pagos)",
    text: "Confirmar pagos en efectivo, QR o transferencia pendientes.",
    icon: "ni ni-cart",
    color: "secondary",
  },
  {
    to: "/admin/retiros",
    title: "Retiros",
    text: "Aprobar o rechazar solicitudes de retiro.",
    icon: "ni ni-money-coins",
    color: "warning",
  },
  {
    to: "/admin/reconciliacion",
    title: "Reconciliación",
    text: "Cierres de periodo y resumen de comisiones.",
    icon: "ni ni-chart-bar-32",
    color: "info",
  },
];

function formatBs(value) {
  const n = Number(value);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(n);
}

function formatPv(value) {
  const n = Number(value);
  if (Number.isNaN(n)) return "—";
  return `${n.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`;
}

function destroyCharts() {
  chartSales?.destroy();
  chartCommissions?.destroy();
  chartRanks?.destroy();
  chartSales = null;
  chartCommissions = null;
  chartRanks = null;
}

function buildCharts() {
  destroyCharts();
  const ch = stats.value.charts || {};
  const sales = ch.sales_last_6_months || [];
  const byType = ch.commissions_by_type || {};
  const ranks = ch.rank_distribution || [];

  if (salesCanvas.value && sales.length) {
    chartSales = new Chart(salesCanvas.value, {
      type: "line",
      data: {
        labels: sales.map((r) => r.month),
        datasets: [
          {
            label: "Ventas completadas (Bs.)",
            data: sales.map((r) => Number(r.total)),
            borderColor: "#5e72e4",
            backgroundColor: "rgba(94, 114, 228, 0.15)",
            fill: true,
            tension: 0.35,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true } },
        scales: {
          y: { beginAtZero: true },
        },
      },
    });
  }

  const typeLabels = Object.keys(byType);
  const typeValues = typeLabels.map((k) => Number(byType[k]));
  if (commissionsCanvas.value && typeLabels.length) {
    chartCommissions = new Chart(commissionsCanvas.value, {
      type: "bar",
      data: {
        labels: typeLabels.map((t) => t.toUpperCase()),
        datasets: [
          {
            label: "Monto (Bs.)",
            data: typeValues,
            backgroundColor: ["#fb6340", "#2dce89", "#11cdef", "#f5365c", "#8965e0", "#8898aa"].slice(
              0,
              typeLabels.length
            ),
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

  if (ranksCanvas.value && ranks.length) {
    chartRanks = new Chart(ranksCanvas.value, {
      type: "doughnut",
      data: {
        labels: ranks.map((r) => r.name),
        datasets: [
          {
            data: ranks.map((r) => r.total),
            backgroundColor: [
              "#5e72e4",
              "#2dce89",
              "#fb6340",
              "#11cdef",
              "#f5365c",
              "#8965e0",
              "#172b4d",
              "#8898aa",
            ],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: "bottom" } },
      },
    });
  }
}

onMounted(async () => {
  loading.value = true;
  error.value = "";
  retirosPreviewError.value = "";
  try {
    const [dashRes, retRes] = await Promise.all([
      fetchAdminDashboard(),
      fetchAdminWithdrawals({ estado: "pendiente", per_page: 8 }).catch(() => null),
    ]);
    stats.value = { ...stats.value, ...(dashRes?.data ?? {}) };
    if (retRes?.data) {
      const body = retRes.data;
      retirosPreview.value = Array.isArray(body.data) ? body.data : [];
    } else {
      retirosPreview.value = [];
      retirosPreviewError.value = "No se pudo cargar la vista previa de retiros.";
    }
  } catch (e) {
    error.value = "No se pudo cargar el panel (¿sesión de administrador?).";
  } finally {
    loading.value = false;
    await nextTick();
    buildCharts();
  }
});

onBeforeUnmount(() => {
  destroyCharts();
});
</script>

<template>
  <div class="py-4 container-fluid admin-dash">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
      <div class="card-body p-4 admin-dash__hero">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h3 class="text-white font-weight-bolder mb-2">Panel empresa</h3>
            <p class="text-white text-sm mb-0 opacity-9">
              KPIs MLM, ventas y comisiones. Los socios usan el panel de red.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <span class="badge bg-white text-dark px-3 py-2 font-weight-bold">
              {{ rolEtiqueta }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <p v-if="error" class="alert alert-danger text-white text-sm">{{ error }}</p>
    <div v-if="loading" class="text-sm text-muted py-4">Cargando métricas…</div>

    <template v-else>
      <div class="row g-3 mb-2">
        <div class="col-6 col-xl-3">
          <mini-statistics-card
            title="Ingresos pedidos"
            :value="formatBs(stats.orders_revenue_total)"
            description="<span class='text-sm font-weight-bolder text-success'>Completados</span>"
            :icon="{
              component: 'ni ni-money-coins',
              background: 'bg-gradient-success',
              shape: 'rounded-circle',
            }"
          />
        </div>
        <div class="col-6 col-xl-3">
          <mini-statistics-card
            title="Comisiones (hist.)"
            :value="formatBs(stats.commissions_paid_total)"
            description="<span class='text-sm font-weight-bolder text-warning'>Eventos acumulados</span>"
            :icon="{
              component: 'ni ni-chart-bar-32',
              background: 'bg-gradient-warning',
              shape: 'rounded-circle',
            }"
          />
        </div>
        <div class="col-6 col-xl-3">
          <mini-statistics-card
            title="Nuevos este mes"
            :value="String(stats.users_new_this_month ?? 0)"
            description="<span class='text-sm font-weight-bolder text-info'>Registros</span>"
            :icon="{
              component: 'ni ni-single-02',
              background: 'bg-gradient-info',
              shape: 'rounded-circle',
            }"
          />
        </div>
        <div class="col-6 col-xl-3">
          <mini-statistics-card
            title="Volumen binario"
            :value="formatPv(stats.binary_volume_current_period)"
            :description="`<span class='text-sm font-weight-bolder text-primary'>Periodo ${stats.binary_period_key || '—'}</span>`"
            :icon="{
              component: 'ni ni-chart-pie-35',
              background: 'bg-gradient-primary',
              shape: 'rounded-circle',
            }"
          />
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6 col-12">
          <mini-statistics-card
            title="Usuarios totales"
            :value="String(stats.users_total ?? 0)"
            description="<span class='text-sm font-weight-bolder text-primary'>Base</span>"
            :icon="{
              component: 'ni ni-world',
              background: 'bg-gradient-primary',
              shape: 'rounded-circle',
            }"
          />
        </div>
        <div class="col-lg-4 col-md-6 col-12">
          <router-link to="/admin/retiros" class="text-decoration-none d-block h-100">
            <mini-statistics-card
              title="Retiros pendientes"
              :value="String(stats.withdrawals_pending ?? 0)"
              description="<span class='text-sm font-weight-bolder text-warning'>Ir a gestión</span>"
              :icon="{
                component: 'ni ni-money-coins',
                background: 'bg-gradient-warning',
                shape: 'rounded-circle',
              }"
            />
          </router-link>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
          <mini-statistics-card
            title="Pedidos hoy"
            :value="String(stats.orders_today ?? 0)"
            description="<span class='text-sm font-weight-bolder text-success'>Creados hoy</span>"
            :icon="{
              component: 'ni ni-cart',
              background: 'bg-gradient-success',
              shape: 'rounded-circle',
            }"
          />
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header pb-0 pt-3">
              <h6 class="mb-0 font-weight-bolder text-dark">Crecimiento — ventas 6 meses</h6>
              <p class="text-xs text-secondary mb-0">Pedidos completados por mes</p>
            </div>
            <div class="card-body chart-wrap">
              <canvas ref="salesCanvas" />
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header pb-0 pt-3">
              <h6 class="mb-0 font-weight-bolder text-dark">Comisiones por tipo</h6>
              <p class="text-xs text-secondary mb-0">Suma histórica por tipo de evento</p>
            </div>
            <div class="card-body chart-wrap">
              <canvas ref="commissionsCanvas" />
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header pb-0 pt-3">
              <h6 class="mb-0 font-weight-bolder text-dark">Distribución de rangos</h6>
              <p class="text-xs text-secondary mb-0">Usuarios por rango asignado</p>
            </div>
            <div class="card-body chart-wrap chart-wrap--doughnut">
              <canvas ref="ranksCanvas" />
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header pb-0 pt-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h6 class="mb-0 font-weight-bolder text-dark">Ranking — PV mensual</h6>
                <p class="text-xs text-secondary mb-0">Top 10 por volumen de calificación del mes</p>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs ps-3">#</th>
                    <th class="text-uppercase text-secondary text-xxs">Socio</th>
                    <th class="text-uppercase text-secondary text-xxs">Código</th>
                    <th class="text-uppercase text-secondary text-xxs">Rango</th>
                    <th class="text-uppercase text-secondary text-xxs pe-3 text-end">PV mes</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(m, idx) in stats.top_members || []" :key="m.id">
                    <td class="ps-3 text-sm">{{ idx + 1 }}</td>
                    <td class="text-sm font-weight-bold">{{ m.name }}</td>
                    <td class="text-sm text-muted">{{ m.member_code }}</td>
                    <td class="text-sm">
                      <span class="badge badge-sm bg-gradient-warning">{{ m.rank_name }}</span>
                    </td>
                    <td class="pe-3 text-end text-sm font-weight-bold">{{ formatPv(m.monthly_qualifying_pv) }}</td>
                  </tr>
                  <tr v-if="!(stats.top_members || []).length">
                    <td colspan="5" class="text-center text-muted py-4 text-sm">Sin datos de socios.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-if="!loading" class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div
            class="card-header bg-gradient-warning py-3 d-flex flex-wrap justify-content-between align-items-center gap-2"
          >
            <div>
              <h6 class="text-white font-weight-bolder mb-0">Retiros pendientes (vista rápida)</h6>
              <p class="text-white text-xs mb-0 opacity-9">Últimas solicitudes en cola</p>
            </div>
            <router-link to="/admin/retiros" class="btn btn-sm btn-white text-warning font-weight-bold mb-0">
              Ver todos
            </router-link>
          </div>
          <div class="card-body p-0">
            <p v-if="retirosPreviewError" class="text-warning text-sm px-3 pt-3 mb-0">
              {{ retirosPreviewError }}
            </p>
            <div v-else class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs ps-3">ID</th>
                    <th class="text-uppercase text-secondary text-xxs">Socio</th>
                    <th class="text-uppercase text-secondary text-xxs">Monto</th>
                    <th class="text-uppercase text-secondary text-xxs pe-3 text-end">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="w in retirosPreview" :key="w.id">
                    <td class="ps-3 text-sm">{{ w.id }}</td>
                    <td class="text-sm">
                      {{ w.user?.name }} <span class="text-muted">({{ w.user?.member_code }})</span>
                    </td>
                    <td class="text-sm font-weight-bold">{{ formatBs(w.monto) }}</td>
                    <td class="pe-3 text-end">
                      <router-link :to="'/admin/retiros'" class="btn btn-xs btn-outline-primary btn-sm">
                        Gestionar
                      </router-link>
                    </td>
                  </tr>
                  <tr v-if="!retirosPreview.length">
                    <td colspan="4" class="text-center text-muted py-4 text-sm">No hay retiros pendientes.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12 mb-3">
        <h6 class="text-dark font-weight-bolder mb-0">Módulos</h6>
        <p class="text-xs text-secondary mb-0">Rutas bajo <code>/admin/*</code></p>
      </div>
      <div v-for="m in modulos" :key="m.to + m.title" class="col-lg-4 col-md-6 mb-4">
        <router-link :to="m.to" class="text-decoration-none">
          <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body p-4">
              <div class="d-flex align-items-start">
                <div
                  class="icon icon-shape rounded-circle shadow text-white d-flex align-items-center justify-content-center"
                  :class="`bg-gradient-${m.color}`"
                  style="width: 52px; height: 52px"
                >
                  <i :class="m.icon + ' text-white'" aria-hidden="true" />
                </div>
                <div class="ms-3">
                  <h6 class="mb-1 text-dark font-weight-bolder">{{ m.title }}</h6>
                  <p class="text-xs text-secondary mb-0">{{ m.text }}</p>
                </div>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div
            class="card-header bg-light py-3 d-flex justify-content-between align-items-center cursor-pointer"
            role="button"
            tabindex="0"
            @click="mostrarGuia = !mostrarGuia"
            @keydown.enter.prevent="mostrarGuia = !mostrarGuia"
          >
            <span class="font-weight-bolder text-dark mb-0">Guía: inscripción, referidos y activación</span>
            <i class="ni" :class="mostrarGuia ? 'ni-bold-up' : 'ni-bold-down'" />
          </div>
          <div v-show="mostrarGuia" class="card-body">
            <h6 class="text-dark">1. Inscripción (registro)</h6>
            <p class="text-sm text-secondary mb-3">
              El socio se registra con <code>POST /api/register</code>. Puede indicar
              <strong>código de patrocinador</strong> (<code>sponsor_referral_code</code>): el backend busca al
              patrocinador por su código/member code y guarda <code>sponsor_id</code>. Opcionalmente elige un
              <strong>paquete de inscripción</strong> (<code>registration_package_id</code>) entre paquetes
              <strong>activos</strong> y una <strong>forma de pago preferida</strong> (referencia comercial; el cobro
              real lo gestionas fuera o integras después). Al crear la cuenta se asignan
              <code>member_code</code> y <code>referral_code</code>.
            </p>
            <h6 class="text-dark">2. Paquetes y BIR</h6>
            <p class="text-sm text-secondary mb-3">
              Los cuatro paquetes (básico 100 PV, avanzado 300 PV, profesional 600 PV, fundador 1200 PV) definen el PV de
              línea del pedido. El <strong>bono inicio rápido</strong> se calcula por cada línea de paquete en el pedido,
              según el paquete elegido.
            </p>
            <h6 class="text-dark">3. Pedidos y comisiones</h6>
            <p class="text-sm text-secondary mb-3">
              Cuando un socio crea un <strong>pedido</strong> y pasa a <strong>completado</strong>, se encolan BIR,
              residual, volumen binario y PV mensual. Requiere <strong>worker de cola</strong> en el servidor.
            </p>
            <h6 class="text-dark">4. Retiros</h6>
            <p class="text-sm text-secondary mb-0">
              El socio solicita retiro; el saldo queda <strong>retenido</strong>. Desde este panel apruebas o rechazas;
              al aprobar se procesa el pago (job). Si el listado no carga, verifica rol admin y que el token sea válido.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <router-link to="/dashboard-default" class="btn btn-sm btn-outline-success">
          <i class="ni ni-bold-left me-1" /> Ir al panel de socio
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-dash__hero {
  background: linear-gradient(135deg, #344767 0%, #1a1f36 100%);
}
.chart-wrap {
  min-height: 260px;
  position: relative;
}
.chart-wrap--doughnut {
  min-height: 280px;
}
.card-hover {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.card-hover:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
}
.cursor-pointer {
  cursor: pointer;
}
.text-xxs {
  font-size: 0.65rem;
}
</style>
