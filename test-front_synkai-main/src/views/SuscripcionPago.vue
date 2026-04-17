<script setup>
import { ref, computed, onBeforeMount, onBeforeUnmount, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import ArgonButton from "@/components/ArgonButton.vue";
import MlmPaymentMethodPanel from "@/components/MlmPaymentMethodPanel.vue";
import { fetchPackages, createOrder, fetchProfile, fetchOrders } from "@/services/me";
import { REGISTRATION_PAYMENT_METHODS } from "@/constants/registrationPayments";

const store = useStore();
const router = useRouter();
const body = document.getElementsByTagName("body")[0];

const packagesList = ref([]);
const selectedId = ref("");
const loading = ref(true);
const checkoutLoading = ref(false);
const err = ref("");
const infoMsg = ref("");
const paymentMethod = ref("tarjeta");
const paymentOptions = REGISTRATION_PAYMENT_METHODS;
const hasPendingActivation = ref(false);
const pendingOrderId = ref(null);
let pollTimer = null;

const selectedPkg = computed(() =>
  packagesList.value.find((p) => String(p.id) === String(selectedId.value))
);

const paymentSettlement = computed(() =>
  ["tarjeta", "online"].includes(paymentMethod.value) ? "immediate" : "manual"
);

onBeforeMount(() => {
  store.state.hideConfigButton = true;
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  store.state.layout = "landing";
  body.classList.remove("bg-gray-100");
});

onBeforeUnmount(() => {
  store.state.hideConfigButton = false;
  store.state.showNavbar = true;
  store.state.showSidenav = true;
  store.state.showFooter = true;
  store.state.layout = "default";
  body.classList.add("bg-gray-100");
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
});

onMounted(async () => {
  loading.value = true;
  err.value = "";
  try {
    const res = await fetchPackages();
    packagesList.value = res.data || [];
    if (packagesList.value.length) {
      selectedId.value = String(packagesList.value[0].id);
    }

    // Si ya existe un pedido de activación pendiente, bloquear confirmaciones repetidas.
    const ord = await fetchOrders({ estado: "pendiente_pago", tipo: "paquete", per_page: 5 }).catch(() => null);
    const rows = ord?.data || [];
    if (rows.length) {
      hasPendingActivation.value = true;
      pendingOrderId.value = rows[0]?.id ?? null;
      infoMsg.value =
        "Ya tienes una activación pendiente de validación por Administración. No vuelvas a confirmar el pago reiteradamente.";
    }
  } catch {
    err.value = "No se pudieron cargar los paquetes. Intenta más tarde.";
  } finally {
    loading.value = false;
  }

  // Poll de perfil: cuando el admin confirma, activation_paid_at se setea y el usuario queda liberado.
  pollTimer = setInterval(async () => {
    if (!localStorage.getItem("token")) return;
    try {
      const u = await fetchProfile();
      await store.dispatch("auth/setAuth", {
        user: u,
        token: localStorage.getItem("token"),
      });
      if (!u?.needs_activation_subscription) {
        if (u?.needs_binary_placement) {
          router.push("/activacion-binaria");
        } else {
          router.push("/dashboard-default");
        }
      }
    } catch {
      /* ignore */
    }
  }, 12000);
});

async function confirmarActivacion() {
  err.value = "";
  infoMsg.value = "";
  if (hasPendingActivation.value) {
    infoMsg.value =
      "Tienes una activación pendiente de validación por Administración. Espera la confirmación para continuar.";
    return;
  }
  if (!selectedId.value) {
    err.value = "Selecciona un paquete.";
    return;
  }
  checkoutLoading.value = true;
  try {
    const order = await createOrder({
      tipo: "paquete",
      items: [{ package_id: parseInt(selectedId.value, 10), cantidad: 1 }],
      payment_settlement: paymentSettlement.value,
      payment_method: paymentMethod.value,
    });
    const u = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: u,
      token: localStorage.getItem("token"),
    });
    if (order?.estado === "pendiente_pago") {
      hasPendingActivation.value = true;
      pendingOrderId.value = order?.id ?? null;
      infoMsg.value =
        "Pedido registrado como pendiente de pago. Debes ir a Administración para validar/confirmar tu transacción. Evita confirmar reiteradamente.";
      return;
    }
    if (u.needs_binary_placement) {
      router.push("/activacion-binaria");
    } else {
      router.push("/dashboard-default");
    }
  } catch (e) {
    if (e.response?.status === 409) {
      hasPendingActivation.value = true;
      infoMsg.value =
        e.response?.data?.message ||
        "Ya existe una activación pendiente de confirmación por Administración. Evita confirmar reiteradamente.";
    } else {
      err.value = e.response?.data?.message || "No se pudo registrar el pedido.";
    }
  } finally {
    checkoutLoading.value = false;
  }
}
</script>

<template>
  <div class="suscripcion-pago">
    <section class="hero-pay position-relative overflow-hidden">
      <div class="hero-bg"></div>
      <div class="container position-relative py-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
          <div class="text-white">
            <h1 class="h3 font-weight-bolder mb-1">Activación / suscripción</h1>
            <p class="text-sm opacity-90 mb-0">
              Debes completar la compra de un paquete para acceder al panel. Elige método de pago de referencia y
              confirma el pedido (simulación de pago en back office).
            </p>
          </div>
        </div>

        <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
          <div class="card-body p-4 p-lg-5">
            <div v-if="loading" class="text-muted">Cargando paquetes…</div>
            <template v-else>
              <p v-if="err" class="text-danger text-sm">{{ err }}</p>
              <p v-if="infoMsg" class="text-success text-sm">{{ infoMsg }}</p>
              <div class="mb-3">
                <label class="form-label text-sm">Paquete de activación</label>
                <select v-model="selectedId" class="form-select">
                  <option v-for="p in packagesList" :key="p.id" :value="String(p.id)">
                    {{ p.name }} — {{ p.pv_points }} PV — Bs. {{ p.price }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label text-sm">Método de pago (referencia)</label>
                <select v-model="paymentMethod" class="form-select">
                  <option v-for="opt in paymentOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
              <MlmPaymentMethodPanel
                v-model="paymentMethod"
                :show-method-select="false"
                title="Instrucciones de pago y entrega"
                class="mb-3"
              />
              <div v-if="selectedPkg" class="alert alert-light border text-sm mb-3">
                <strong>{{ selectedPkg.name }}</strong><br />
                PV: {{ selectedPkg.pv_points }} · Precio: Bs. {{ selectedPkg.price }}
              </div>
              <argon-button
                color="success"
                variant="gradient"
                class="w-100"
                :disabled="checkoutLoading || !packagesList.length || hasPendingActivation"
                @click="confirmarActivacion"
              >
                <template v-if="hasPendingActivation">Activación pendiente (Admin)</template>
                <template v-else>{{ checkoutLoading ? "Procesando…" : "Confirmar pedido y activar" }}</template>
              </argon-button>
              <p v-if="hasPendingActivation" class="text-xs text-secondary mt-2 mb-0">
                Pedido pendiente: <strong>{{ pendingOrderId || "—" }}</strong>. Cuando administración confirme, se habilitará tu panel automáticamente.
              </p>
            </template>
          </div>
        </div>
      </div>
    </section>

    <footer class="py-4 footer-pay">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start text-sm text-muted">Activación obligatoria para el panel</div>
          <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
            <router-link to="/signin" class="text-muted text-sm">Iniciar sesión</router-link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.hero-pay {
  min-height: 100vh;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #54b144 0%, #222d25 100%);
}

.footer-pay {
  background: #ffffff;
}
</style>
