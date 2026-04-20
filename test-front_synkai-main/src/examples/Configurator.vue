<script setup>
import { computed } from "vue";
import { useStore } from "vuex";
import { activateDarkMode, deactivateDarkMode } from "@/assets/js/dark-mode";

const store = useStore();
// state
const isRTL = computed(() => store.state.isRTL);
const sidebarType = computed(() => store.state.sidebarType);
const toggleConfigurator = () => store.commit("toggleConfigurator");

// mutations
const setSidebarType = (type) => store.commit("sidebarType", type);



const darkMode = () => {
  if (store.state.darkMode) {
    store.state.darkMode = false;
    setSidebarType("bg-white");
    deactivateDarkMode();
    return;
  } else {
    store.state.darkMode = true;
    setSidebarType("bg-default");
    activateDarkMode();
  }
};
</script>
<template>
  <div class="fixed-plugin">
    <a
      class="px-3 py-2 fixed-plugin-button text-dark position-fixed"
      @click="toggleConfigurator"
    >
      <i class="py-2 fa fa-cog"></i>
    </a>
    <div class="shadow-lg card">
      <div class="pt-3 pb-0 bg-transparent card-header">
        <div class="" :class="isRTL ? 'float-end' : 'float-start'">
          <h5 class="mt-3 mb-0">Configuracion de panel</h5>
          <p>Configura el panel de acuerdo a tus necesidades.</p>
        </div>
        <div
          class="mt-4"
          @click="toggleConfigurator"
          :class="isRTL ? 'float-start' : 'float-end'"
        >
          <button class="p-0 btn btn-link text-dark fixed-plugin-close-button">
            <i class="fa fa-close"></i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="my-1 horizontal dark" />
      <div class="pt-0 card-body pt-sm-3">
        <!-- Sidebar Backgrounds -->
       
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex gap-2">
          <button
            id="btn-white"
            class="btn w-100 px-3 mb-2"
            :class="
              sidebarType === 'bg-white'
                ? 'bg-gradient-success'
                : 'btn-outline-success'
            "
            @click="setSidebarType('bg-white')"
          >
            White
          </button>
          <button
            id="btn-dark"
            class="btn w-100 px-3 mb-2"
            :class="
              sidebarType === 'bg-default'
                ? 'bg-gradient-success'
                : 'btn-outline-success'
            "
            @click="setSidebarType('bg-default')"
          >
            Dark
          </button>
        </div>
        <p class="mt-2 text-sm d-xl-none d-block">
          You can change the sidenav type just on desktop view.
        </p>
        <hr class="horizontal dark my-3" />
        <div class="mt-2 d-flex align-items-center justify-content-between">
          <span class="text-sm">Modo oscuro</span>
          <button
            type="button"
            class="btn btn-sm mb-0"
            :class="store.state.darkMode ? 'bg-gradient-success' : 'btn-outline-success'"
            @click="darkMode"
          >
            {{ store.state.darkMode ? "Activado" : "Desactivado" }}
          </button>
        </div>
       
        <a
          class="btn bg-gradient-dark w-100"
          href="https://soportetbn.vercel.app/"
          >soporte</a
        >
        <a
          class="btn btn-outline-dark w-100"
          href="https://www.creative-tim.com/learning-lab/vue/overview/argon-dashboard/"
          >Ver documentacion</a
        >
        <div class="text-center w-100">
          <a
            class="github-button"
            href="https://github.com/creativetimofficial/vue-argon-dashboard"
            data-icon="octicon-star"
            data-size="large"
            data-show-count="true"
            aria-label="Star creativetimofficial/vue-argon-dashboard on GitHub"
            >Star</a
          >
        </div>
      </div>
    </div>
  </div>
</template>
