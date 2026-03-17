<script setup>
import { computed, onBeforeMount, onBeforeUnmount } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import PagoSuscripcionOptions from "@/views/components/PagoSuscripcionOptions.vue";

const store = useStore();
const route = useRoute();
const body = document.getElementsByTagName("body")[0];

const paquete = computed(() => {
  const nombre = String(route.query?.paquete || "Paquete seleccionado");
  const precio = String(route.query?.precio || "Bs. 0");
  const pv = String(route.query?.pv || "");
  return { nombre, precio, pv };
});

onBeforeMount(() => {
  // Página independiente (sin dashboard)
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
</script>

<template>
  <div class="suscripcion-pago">
    <section class="hero-pay position-relative overflow-hidden">
      <div class="hero-bg"></div>
      <div class="container position-relative py-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
          <div class="text-white">
            <h1 class="h3 font-weight-bolder mb-1">Pago de suscripción</h1>
            <p class="text-sm opacity-90 mb-0">
              Tarjeta, QR o efectivo. Elige el método y confirma tu activación.
            </p>
          </div>
          <router-link to="/welcom#productos" class="btn btn-sm btn-outline-light">
            <i class="ni ni-bold-left me-1"></i>
            Volver a paquetes
          </router-link>
        </div>

        <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
          <div class="card-body p-4 p-lg-5">
            <PagoSuscripcionOptions :paquete="paquete" />
          </div>
        </div>
      </div>
    </section>

    <footer class="py-4 footer-pay">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start text-sm text-muted">
            Suscripción · Métodos de pago
          </div>
          <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
            <router-link to="/signin" class="text-muted text-sm me-3">Iniciar sesión</router-link>
            <router-link to="/" class="text-muted text-sm">Inicio</router-link>
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

