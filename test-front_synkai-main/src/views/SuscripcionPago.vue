<script setup>
import { ref, computed, onBeforeMount, onBeforeUnmount, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import ArgonButton from "@/components/ArgonButton.vue";
import { fetchPackages, createOrder, fetchProfile } from "@/services/me";
import { REGISTRATION_PAYMENT_METHODS } from "@/constants/registrationPayments";

const store = useStore();
const router = useRouter();
const body = document.getElementsByTagName("body")[0];

const packagesList = ref([]);
const selectedId = ref("");
const loading = ref(true);
const checkoutLoading = ref(false);
const err = ref("");
const paymentMethod = ref("transferencia");
const paymentOptions = REGISTRATION_PAYMENT_METHODS;

const selectedPkg = computed(() =>
  packagesList.value.find((p) => String(p.id) === String(selectedId.value))
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
  } catch {
    err.value = "No se pudieron cargar los paquetes. Intenta más tarde.";
  } finally {
    loading.value = false;
  }
});

async function confirmarActivacion() {
  err.value = "";
  if (!selectedId.value) {
    err.value = "Selecciona un paquete.";
    return;
  }
  checkoutLoading.value = true;
  try {
    await createOrder({
      tipo: "paquete",
      items: [{ package_id: parseInt(selectedId.value, 10), cantidad: 1 }],
    });
    const u = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: u,
      token: localStorage.getItem("token"),
    });
    if (u.needs_binary_placement) {
      router.push("/activacion-binaria");
    } else {
      router.push("/dashboard-default");
    }
  } catch (e) {
    err.value = e.response?.data?.message || "No se pudo registrar el pedido.";
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
              <div v-if="selectedPkg" class="alert alert-light border text-sm mb-3">
                <strong>{{ selectedPkg.name }}</strong><br />
                PV: {{ selectedPkg.pv_points }} · Precio: Bs. {{ selectedPkg.price }}
              </div>
              <argon-button
                color="success"
                variant="gradient"
                class="w-100"
                :disabled="checkoutLoading || !packagesList.length"
                @click="confirmarActivacion"
              >
                {{ checkoutLoading ? "Procesando…" : "Confirmar pedido y activar" }}
              </argon-button>
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
