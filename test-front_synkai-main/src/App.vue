<!--
=========================================================
* Vue Argon Dashboard 2 - v4.0.0
=========================================================

* Product Page: https://creative-tim.com/product/vue-argon-dashboard
* Copyright 2024 Creative Tim (https://www.creative-tim.com)

Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<script setup>
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import Sidenav from "@/examples/Sidenav/index.vue";
import Configurator from "@/examples/Configurator.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/Footer.vue";

const XL = 1200;
const store = useStore();
const router = useRouter();
const isMobile = ref(typeof window !== "undefined" ? window.innerWidth < XL : true);

function updateViewport() {
  if (typeof window === "undefined") return;
  const mobile = window.innerWidth < XL;
  isMobile.value = mobile;
  store.commit("setPinned", !mobile);
}

// Solo cerrar el sidebar al navegar en móvil; en escritorio mantener estado
router.afterEach(() => {
  if (typeof window !== "undefined" && window.innerWidth < XL) {
    store.commit("setPinned", false);
  }
});

const isNavFixed = computed(() => store.state.isNavFixed);
const isAbsolute = computed(() => store.state.isAbsolute);
const showSidenav = computed(() => store.state.showSidenav);
const layout = computed(() => store.state.layout);
const darkMode = computed(() => store.state.darkMode);
const showNavbar = computed(() => store.state.showNavbar);
const showFooter = computed(() => store.state.showFooter);
const showConfig = computed(() => store.state.showConfig);
const hideConfigButton = computed(() => store.state.hideConfigButton);
const toggleConfigurator = () => store.commit("toggleConfigurator");

const isPinned = computed(() => store.state.isPinned);
const showSidenavOverlay = computed(() => isPinned.value && isMobile.value && showSidenav.value && layout.value === "default");

const sidenavWrapperClass = computed(() => ({
  "g-sidenav-show": true,
  "g-sidenav-hidden": !isPinned.value,
  "g-sidenav-pinned": isPinned.value,
}));

const navClasses = computed(() => ({
  "position-sticky bg-gradient-navbar left-auto top-2 z-index-sticky": isNavFixed.value,
  "position-absolute px-4 mx-0 w-100 z-index-2 bg-gradient-navbar": isAbsolute.value,
  "px-0 mx-4 bg-gradient-navbar": !isAbsolute.value,
}));

function closeSidenavOverlay() {
  if (isMobile.value) store.commit("setPinned", false);
}

onMounted(() => {
  updateViewport();
  window.addEventListener("resize", updateViewport);
  nextTick(() => {
    import("@/assets/js/nav-pills.js").catch(() => {});
    import("@/assets/js/tooltip.js").catch(() => {});
  });
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateViewport);
});
</script>
<template>
  <div
    class="g-sidenav-wrapper"
    :class="sidenavWrapperClass"
  >
    <div
      v-show="layout === 'landing'"
      class="landing-bg h-100 bg-gradient-primary position-fixed w-100"
    />
    <div
      v-show="showSidenav && layout === 'default'"
      class="min-height-300 position-absolute w-100"
      :class="darkMode ? 'bg-transparent' : 'bg-success'"
    />
    <Sidenav v-if="showSidenav" />

    <main class="main-content position-relative max-height-vh-100 h-100">
      <Navbar :class="[navClasses]" v-if="showNavbar" />
      <div class="main-content-inner">
        <router-view />
      </div>
      <AppFooter v-show="showFooter" />
      <Configurator
        :toggle="toggleConfigurator"
        :class="[showConfig ? 'show' : '', hideConfigButton ? 'd-none' : '']"
      />
    </main>

    <!-- Overlay móvil: después de main para que .sidenav y main sean hermanos (margen escritorio) -->
    <div
      v-show="showSidenavOverlay"
      class="sidenav-backdrop"
      aria-hidden="true"
      @click="closeSidenavOverlay"
    />
  </div>
</template>