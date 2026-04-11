<script setup>
import { computed, onMounted, ref } from "vue";
import { useStore } from "vuex";
import { fetchAdminDashboard } from "@/services/admin";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";

const store = useStore();
const loading = ref(true);
const error = ref("");
const stats = ref({ users_total: 0, withdrawals_pending: 0, orders_today: 0 });
const mostrarGuia = ref(false);

const mlmRole = computed(() => store.getters["auth/mlmRole"]);
const rolEtiqueta = computed(() => {
  const r = mlmRole.value;
  if (r === "superadmin") return "Superadministrador";
  if (r === "admin") return "Administrador";
  if (r === "support") return "Soporte empresa";
  return r || "—";
});

/** Módulos solo de administración (admin / superadmin / support con acceso API). */
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
    text: "Paquetes de inscripción y venta (slug, PV, monto comisionable).",
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

onMounted(async () => {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await fetchAdminDashboard();
    stats.value = data;
  } catch (e) {
    error.value = "No se pudo cargar el panel (¿sesión de administrador?).";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h4 class="mb-1">Dashboard administración</h4>
            <p class="text-sm text-muted mb-0">
              Módulos de empresa (admin / superadmin). No incluye el menú de socio común.
            </p>
          </div>
          <span class="badge bg-gradient-dark text-white px-3 py-2">Rol: {{ rolEtiqueta }}</span>
        </div>
      </div>
    </div>

    <p v-if="error" class="text-danger text-sm">{{ error }}</p>
    <div v-if="loading" class="text-sm text-muted">Cargando métricas…</div>

    <div v-else class="row">
      <div class="col-lg-4 col-md-6 col-12 mb-4">
        <mini-statistics-card
          title="Usuarios"
          :value="String(stats.users_total ?? 0)"
          description="<span class='text-sm font-weight-bolder text-primary'>Registrados</span>"
          :icon="{
            component: 'ni ni-single-02',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-4 col-md-6 col-12 mb-4">
        <mini-statistics-card
          title="Retiros pendientes"
          :value="String(stats.withdrawals_pending ?? 0)"
          description="<span class='text-sm font-weight-bolder text-warning'>Por revisar</span>"
          :icon="{
            component: 'ni ni-money-coins',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-4 col-md-6 col-12 mb-4">
        <mini-statistics-card
          title="Pedidos hoy"
          :value="String(stats.orders_today ?? 0)"
          description="<span class='text-sm font-weight-bolder text-success'>Hoy</span>"
          :icon="{
            component: 'ni ni-cart',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12 mb-3">
        <h6 class="text-dark font-weight-bolder mb-0">Accesos rápidos</h6>
        <p class="text-xs text-secondary mb-0">Solo rutas bajo <code>/admin/*</code>.</p>
      </div>
      <div v-for="m in modulos" :key="m.to + m.title" class="col-lg-3 col-md-6 mb-4">
        <router-link :to="m.to" class="text-decoration-none">
          <div class="card border-0 shadow-sm h-100 card-hover">
            <div class="card-body p-4">
              <div class="d-flex align-items-start">
                <div
                  class="icon icon-shape rounded-circle shadow text-white d-flex align-items-center justify-content-center"
                  :class="`bg-gradient-${m.color}`"
                  style="width: 48px; height: 48px"
                >
                  <i :class="m.icon + ' text-white'" aria-hidden="true"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-1 text-dark">{{ m.title }}</h6>
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
            class="card-header bg-gradient-light py-3 d-flex justify-content-between align-items-center cursor-pointer"
            role="button"
            tabindex="0"
            @click="mostrarGuia = !mostrarGuia"
            @keydown.enter.prevent="mostrarGuia = !mostrarGuia"
          >
            <span class="font-weight-bolder text-dark mb-0">Guía: inscripción, referidos y activación</span>
            <i class="ni" :class="mostrarGuia ? 'ni-bold-up' : 'ni-bold-down'"></i>
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
              <code>member_code</code> y <code>referral_code</code> y el estado de cuenta suele quedar
              <code>pending</code> hasta que haya actividad que cumpla reglas MLM.
            </p>
            <h6 class="text-dark">2. ¿Cómo “se activan” los referidos?</h6>
            <p class="text-sm text-secondary mb-3">
              El vínculo de referido es inmediato: quien se registra con tu código queda como tu hijo directo en
              <code>sponsor_id</code> y aparece en la lista de referidos. Lo que se “activa” con reglas MLM es la
              <strong>calificación</strong>: al completarse pedidos, el sistema suma el
              <strong>PV del mes</strong> del usuario. Si en el mes alcanza el umbral configurado (por defecto
              <strong>200 PV</strong> en <code>config/mlm.php</code> → <code>monthly_activation_pv</code>), se marca
              <code>is_mlm_qualified</code> y puede ajustarse <code>account_status</code> a activo. Eso lo hace
              <code>UserQualificationService</code> tras procesar pedidos completados (cola/jobs).
            </p>
            <h6 class="text-dark">3. Pedidos y comisiones</h6>
            <p class="text-sm text-secondary mb-3">
              Cuando un socio crea un <strong>pedido</strong> (<code>POST /api/orders</code>) con productos o
              paquetes, el pedido pasa a <strong>completado</strong> y se dispara <code>OrderCompleted</code>. Un job
              encola BIR (bono inicio rápido sobre paquetes, cadena de patrocinio), residual por pedido, volumen
              binario y actualización de PV mensual. Los referidos generan comisiones a sus patrocinadores según esas
              reglas, no “al registrarse” solamente. En servidor debes tener el <strong>worker de cola</strong> en
              marcha (<code>php artisan queue:work</code>) para que esos cálculos se ejecuten.
            </p>
            <h6 class="text-dark">4. Tu rol en el panel</h6>
            <p class="text-sm text-secondary mb-0">
              Desde <strong>Productos</strong> y <strong>Paquetes</strong> defines qué se ofrece en tienda y en
              inscripción. Los usuarios con rol <code>admin</code>, <code>superadmin</code> o
              <code>support</code> (según <code>MLM_ADMIN_ROLES</code>) pueden usar estas APIs; el front comprueba
              <code>can_access_admin_panel</code>.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <router-link to="/dashboard-default" class="btn btn-sm btn-outline-success">
          <i class="ni ni-bold-left me-1"></i> Ir al panel de socio
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-hover {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.card-hover:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.35rem 0.85rem rgba(0, 0, 0, 0.1) !important;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
