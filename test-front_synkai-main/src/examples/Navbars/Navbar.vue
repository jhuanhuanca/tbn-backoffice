<script setup>
import { computed, ref } from "vue";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import Breadcrumbs from "../Breadcrumbs.vue";

const showMenu = ref(false);
const store = useStore();
const isRTL = computed(() => store.state.isRTL);

const route = useRoute();

const currentRouteName = computed(() => route.name);

const currentDirectory = computed(() => {
  let dir = route.path.split("/")[1];
  return dir.charAt(0).toUpperCase() + dir.slice(1);
});

const minimizeSidebar = () => store.commit("sidebarMinimize");

const closeMenu = () => {
  setTimeout(() => {
    showMenu.value = false;
  }, 100);
};
</script>

<template>
  <nav
    class="navbar navbar-main navbar-expand-lg px-3 shadow-sm border-radius-xl navbar-dashboard-shell"
    :class="isRTL ? 'top-0 position-sticky z-index-sticky' : ''"
  >
    <div class="container-fluid py-3 d-flex align-items-center">

      <div class="d-flex align-items-center justify-content-between w-100">

        <!-- BOTÓN SIDEBAR -->
        <a
          href="#"
          class="d-xl-none p-2 nav-link text-white rounded"
          @click.prevent="minimizeSidebar"
        >
          <div class="sidenav-toggler-inner">
            <i class="sidenav-toggler-line bg-white"></i>
            <i class="sidenav-toggler-line bg-white"></i>
            <i class="sidenav-toggler-line bg-white"></i>
          </div>
        </a>

        <!-- BREADCRUMB -->
        <div class="flex-grow-1 px-2">
          <breadcrumbs
            :current-page="currentRouteName"
            :current-directory="currentDirectory"
          />
        </div>

        <!-- ICONOS DERECHA -->
        <ul class="navbar-nav flex-row align-items-center gap-2 mb-0">

          <!-- BUSCADOR (SOLO DESKTOP) -->
          <li class="d-none d-md-block">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-0">
                <i class="fas fa-search"></i>
              </span>
              <input
                type="search"
                class="form-control border-0"
                placeholder="Buscar..."
              />
            </div>
          </li>

          <!-- NOTIFICACIONES -->
          <li class="nav-item dropdown">
            <a
              href="#"
              class="p-2 nav-link text-white"
              :class="showMenu ? 'show' : ''"
              @click.prevent="showMenu = !showMenu"
              @blur="closeMenu"
            >
              <i class="fa fa-bell"></i>
            </a>

            <ul
              class="dropdown-menu dropdown-menu-end shadow"
              :class="showMenu ? 'show' : ''"
            >
              <li class="px-3 py-2 text-sm text-muted">
                Sin notificaciones nuevas.
              </li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar-dashboard-shell {
  min-height: 100px; /* 🔥 altura más grande */
  display: flex;
  align-items: center; /* centra verticalmente */
}
.nav-link {
  font-size: 1.1rem;
}

.fa, .fas {
  font-size: 1.2rem;
}

/* Ajustes responsive finos */
@media (max-width: 768px) {
  .navbar-nav {
    gap: 4px;
  }

  .input-group {
    display: none; /* ocultar buscador en móvil */
  }
}
</style>
