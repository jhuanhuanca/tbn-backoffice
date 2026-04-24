<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { useStore } from "vuex";
import { useRoute, useRouter } from "vue-router";
import Breadcrumbs from "../Breadcrumbs.vue";
import { fetchNotifications, clearNotificationsAll, dismissNotificationsByIds } from "@/services/me";

const showMenu = ref(false);
const store = useStore();
const route = useRoute();
const router = useRouter();
const isRTL = computed(() => store.state.isRTL);

const searchQuery = ref("");
const notifItems = ref([]);
const notifLoading = ref(false);
let notifTimer = null;

const currentRouteName = computed(() => route.name);

const currentDirectory = computed(() => {
  const dir = route.path.split("/")[1] || "";
  if (!dir) return "";
  return dir.charAt(0).toUpperCase() + dir.slice(1);
});

const minimizeSidebar = () => store.commit("sidebarMinimize");

const closeMenu = () => {
  setTimeout(() => {
    showMenu.value = false;
  }, 120);
};

/** Rutas buscables (mismo alcance que el socio autenticado). */
const searchIndex = [
  { path: "/dashboard-default", label: "Dashboard", keywords: "panel inicio resumen" },
  { path: "/productos", label: "Productos", keywords: "tienda catálogo comprar" },
  { path: "/comisiones", label: "Comisiones", keywords: "bonos binario bir residual" },
  { path: "/cardreferidos", label: "Referidos", keywords: "red patrocinio" },
  { path: "/cardarbol", label: "Árbol", keywords: "binario matriz" },
  { path: "/billetera", label: "Billetera", keywords: "saldo retiros" },
  { path: "/compras-realizadas", label: "Compras", keywords: "pedidos historial" },
  { path: "/cuenta", label: "Cuenta", keywords: "perfil datos" },
  { path: "/profile", label: "Perfil", keywords: "editar cuenta" },
  { path: "/linkreferidos", label: "Enlaces", keywords: "invitar" },
  { path: "/estadisticas-equipo", label: "Estadísticas", keywords: "equipo volumen" },
  { path: "/retiros-historial", label: "Retiros", keywords: "historial" },
  { path: "/activacion-binaria", label: "Activación binaria", keywords: "pierna" },
  { path: "/suscripcion-pago", label: "Suscripción", keywords: "pago activación" },
];

function normalize(s) {
  return String(s || "").toLowerCase();
}

function runSearch() {
  const q = normalize(searchQuery.value).trim();
  if (!q) return;
  const hit = searchIndex.find((x) => {
    const hay = `${normalize(x.label)} ${normalize(x.keywords)}`;
    return hay.includes(q) || q.split(/\s+/).every((w) => w.length > 0 && hay.includes(w));
  });
  if (hit) {
    router.push(hit.path);
    searchQuery.value = "";
    return;
  }
  router.push({ path: "/productos", query: { q: searchQuery.value.trim() } });
  searchQuery.value = "";
}

function onSearchKeydown(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    runSearch();
  }
}

async function loadNotifications() {
  if (!store.state.auth.token) {
    notifItems.value = [];
    return;
  }
  notifLoading.value = true;
  try {
    const data = await fetchNotifications();
    notifItems.value = Array.isArray(data.items) ? data.items : [];
  } catch {
    notifItems.value = [];
  } finally {
    notifLoading.value = false;
  }
}

function openNotifLink(n) {
  showMenu.value = false;
  if (n?.url) router.push(n.url);
}

async function clearAllNotifs() {
  if (notifLoading.value) return;
  notifLoading.value = true;
  try {
    await clearNotificationsAll();
    notifItems.value = [];
    showMenu.value = false;
  } catch {
    // si falla, mantenemos la lista; el usuario puede intentar de nuevo
  } finally {
    notifLoading.value = false;
  }
}

async function dismissNotif(n) {
  const id = n?.id;
  if (!id || notifLoading.value) return;
  // Optimista: quita del UI inmediatamente.
  notifItems.value = notifItems.value.filter((x) => x?.id !== id);
  try {
    await dismissNotificationsByIds([id]);
  } catch {
    // Si falla, recarga para mantener consistencia.
    await loadNotifications();
  }
}

onMounted(() => {
  loadNotifications();
  notifTimer = setInterval(loadNotifications, 120000);
});

onBeforeUnmount(() => {
  if (notifTimer) clearInterval(notifTimer);
});
</script>

<template>
  <nav
    class="navbar navbar-main navbar-expand-lg px-3 shadow-sm border-radius-xl navbar-dashboard-shell"
    :class="isRTL ? 'top-0 position-sticky z-index-sticky' : ''"
  >
    <div class="container-fluid py-2 py-md-3">
      <!-- Móvil: fila 1 (menú + migas + campana) y fila 2 (búsqueda ancho completo). Escritorio: una sola fila. -->
      <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center w-100 gap-2 gap-md-0">
        <div class="d-flex align-items-center justify-content-between w-100 flex-nowrap gap-2 min-w-0">
          <a href="#" class="d-xl-none p-2 nav-link text-white rounded flex-shrink-0" @click.prevent="minimizeSidebar">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line bg-white"></i>
              <i class="sidenav-toggler-line bg-white"></i>
              <i class="sidenav-toggler-line bg-white"></i>
            </div>
          </a>

          <div class="flex-grow-1 min-w-0 px-md-2 navbar-breadcrumb-slot">
            <breadcrumbs :current-page="currentRouteName" :current-directory="currentDirectory" />
          </div>

          <ul class="navbar-nav navbar-nav-actions flex-row align-items-center gap-1 gap-md-2 mb-0 flex-shrink-0">
            <li class="d-none d-md-block">
              <div class="input-group input-group-sm" role="search">
                <span class="input-group-text bg-white border-0">
                  <i class="fas fa-search"></i>
                </span>
                <input
                  v-model="searchQuery"
                  type="search"
                  class="form-control border-0"
                  placeholder="Buscar sección…"
                  aria-label="Buscar en el panel"
                  @keydown="onSearchKeydown"
                />
                <button type="button" class="btn btn-sm btn-outline-light border-0" title="Ir" @click="runSearch">
                  Ir
                </button>
              </div>
            </li>

            <li class="nav-item dropdown position-relative flex-shrink-0">
              <a
                href="#"
                class="p-2 nav-link text-white position-relative navbar-notif-trigger"
                :class="showMenu ? 'show' : ''"
                @click.prevent="showMenu = !showMenu"
                @blur="closeMenu"
              >
                <i class="fa fa-bell"></i>
                <span
                  v-if="notifItems.length"
                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-xxs"
                  style="font-size: 0.6rem"
                >
                  {{ notifItems.length > 99 ? "99+" : notifItems.length }}
                </span>
              </a>

              <ul
                class="dropdown-menu dropdown-menu-end shadow navbar-notif-menu"
                :class="showMenu ? 'show' : ''"
              >
                <li v-if="notifLoading" class="px-3 py-2 text-sm text-muted">Cargando…</li>
                <template v-else-if="notifItems.length">
                <li class="px-2 py-2 border-bottom border-light">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger w-100"
                    @mousedown.prevent="clearAllNotifs"
                  >
                    Eliminar todas las notificaciones
                  </button>
                </li>
                  <li
                    v-for="n in notifItems.slice(0, 25)"
                    :key="n.id"
                    class="dropdown-item-text px-0 py-0 border-bottom border-light"
                  >
                  <div class="d-flex align-items-start gap-2 px-3 py-2">
                    <button
                      type="button"
                      class="btn btn-link text-start text-decoration-none p-0 flex-grow-1 min-w-0"
                      @mousedown.prevent="openNotifLink(n)"
                    >
                      <div class="text-xs text-uppercase text-muted mb-0">{{ n.type }}</div>
                      <div class="text-sm font-weight-bolder text-dark text-truncate">{{ n.title }}</div>
                      <div class="text-xxs text-secondary">{{ n.body }}</div>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-link text-danger p-0 flex-shrink-0"
                      title="Eliminar"
                      @mousedown.prevent.stop="dismissNotif(n)"
                    >
                      <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                  </div>
                  </li>
                </template>
                <li v-else class="px-3 py-2 text-sm text-muted">Sin notificaciones recientes.</li>
              </ul>
            </li>
          </ul>
        </div>

        <div class="d-md-none w-100" role="search">
          <div class="input-group input-group-sm navbar-mobile-search">
            <span class="input-group-text bg-white border-0 text-secondary">
              <i class="fas fa-search" aria-hidden="true"></i>
            </span>
            <input
              v-model="searchQuery"
              type="search"
              class="form-control border-0"
              placeholder="Buscar sección…"
              aria-label="Buscar en el panel"
              @keydown.enter.prevent="runSearch"
            />
            <button type="button" class="btn btn-sm bg-white text-success border-0 fw-bold px-3" title="Ir" @click="runSearch">
              Ir
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar-dashboard-shell {
  min-height: 100px;
  display: flex;
  align-items: center;
}

@media (max-width: 767.98px) {
  .navbar-dashboard-shell {
    min-height: unset;
    align-items: stretch;
  }
}

/* Migas: no empujan la campana; texto largo con puntos suspensivos */
.navbar-breadcrumb-slot :deep(h6) {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100%;
}
.navbar-breadcrumb-slot :deep(.breadcrumb) {
  flex-wrap: nowrap;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.navbar-mobile-search {
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.08);
}
.nav-link {
  font-size: 1.1rem;
}

.fa,
.fas {
  font-size: 1.2rem;
}

.text-xxs {
  font-size: 0.65rem;
}

@media (max-width: 768px) {
  .navbar-nav-actions {
    gap: 2px;
  }
}

/* Notificaciones: ancho razonable en escritorio; en móvil ancho casi completo y fijo bajo el navbar */
.navbar-notif-menu {
  min-width: min(320px, calc(100vw - 1.5rem));
  max-height: min(380px, 70vh);
  overflow-y: auto;
  z-index: 1060;
}

@media (max-width: 767.98px) {
  .navbar-notif-menu.dropdown-menu {
    position: fixed !important;
    left: 0.75rem !important;
    right: 0.75rem !important;
    /* Navbar shell ~100px + padding; sitúa el panel bajo la barra verde */
    /* Navbar en móvil = 2 filas (migas + buscador debajo) */
    top: calc(8.5rem + env(safe-area-inset-top, 0px)) !important;
    bottom: auto !important;
    margin: 0 !important;
    transform: none !important;
    width: auto !important;
    min-width: 0 !important;
    max-width: none !important;
  }
}

.navbar-notif-menu .btn-link {
  word-break: break-word;
  white-space: normal;
}
</style>
