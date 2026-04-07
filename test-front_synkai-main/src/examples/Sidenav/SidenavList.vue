<script setup>
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import api from "@/services/api";

const route = useRoute();
const router = useRouter();
const isAdminRoute = computed(() => route.path.startsWith("/admin"));
import { labelCountry } from "@/constants/latamCountries";

const store = useStore();
const isRTL = computed(() => store.state.isRTL);
const canAccessAdmin = computed(() => store.getters["auth/canAccessAdmin"]);
const authUser = computed(() => store.state.auth.user);
const userInitials = computed(() => {
  const n = authUser.value?.name || "";
  const parts = n.trim().split(/\s+/).filter(Boolean);
  const a = parts[0]?.[0] || "U";
  const b = parts[1]?.[0] || "";
  return `${a}${b}`.toUpperCase();
});
const countryLabel = computed(() => labelCountry(authUser.value?.country_code));

const getRoute = () => {
  const routeArr = route.path.split("/");
  return routeArr[1];
};

// Inicializamos todos los toggles
const isOpen = ref({
  programaComercial: false,
  arranqueExplosivo: false,
  universidadEmpresarios: false,
  negocioOnline: false,
  sistemaLiderazgo: false
});

function toggle(menu) {
  isOpen.value[menu] = !isOpen.value[menu];
}

const logoutLoading = ref(false);

async function cerrarSesion() {
  logoutLoading.value = true;
  try {
    await api.post("/logout");
  } catch {
    /* */
  }
  await store.dispatch("auth/logout");
  logoutLoading.value = false;
  router.push("/signin");
}

</script>

<template>
  <div class="collapse navbar-collapse w-auto h-auto h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <!-- Vista solo administración (sin menú de socio) -->
      <template v-if="isAdminRoute && canAccessAdmin">
        <li v-if="authUser" class="nav-item px-2 text-center border-bottom border-light pb-3 mb-2">
          <router-link to="/cuenta" class="text-decoration-none d-block">
            <div
              class="avatar avatar-lg rounded-circle bg-gradient-primary mx-auto text-white d-flex align-items-center justify-content-center shadow-sm"
              style="width: 56px; height: 56px; min-width: 56px; font-size: 1rem"
            >
              {{ userInitials }}
            </div>
            <div class="text-sm font-weight-bold text-dark mt-2 text-truncate">{{ authUser.name }}</div>
            <div class="text-xs text-muted">#{{ authUser.member_code ?? "—" }}</div>
            <div class="text-xs text-muted text-truncate">{{ countryLabel }}</div>
            <div v-if="authUser?.mlm_role" class="text-xxs text-uppercase text-muted mt-1">
              Rol: {{ authUser.mlm_role }}
            </div>
          </router-link>
        </li>
        <li class="nav-item">
          <sidenav-item
            to="/admin/dashboard"
            :class="getRoute() === 'admin' ? 'active' : ''"
            navText="Panel empresa"
          >
            <template v-slot:icon>
              <i class="ni ni-settings-gear-65 text-danger text-sm opacity-10"></i>
            </template>
          </sidenav-item>
        </li>
        <li class="nav-item">
          <sidenav-item to="/admin/retiros" navText="Retiros (gestión)">
            <template v-slot:icon>
              <i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
            </template>
          </sidenav-item>
        </li>
        <li class="nav-item">
          <sidenav-item to="/admin/reconciliacion" navText="Reconciliación">
            <template v-slot:icon>
              <i class="ni ni-chart-bar-32 text-info text-sm opacity-10"></i>
            </template>
          </sidenav-item>
        </li>
        <li class="nav-item">
          <sidenav-item to="/admin/productos" navText="Productos (catálogo)">
            <template v-slot:icon>
              <i class="ni ni-box-2 text-primary text-sm opacity-10"></i>
            </template>
          </sidenav-item>
        </li>
        <li class="nav-item">
          <sidenav-item to="/admin/paquetes" navText="Paquetes">
            <template v-slot:icon>
              <i class="ni ni-gift text-success text-sm opacity-10"></i>
            </template>
          </sidenav-item>
        </li>
        <li class="nav-item mt-2">
          <router-link to="/dashboard-default" class="nav-link text-success">
            <i class="ni ni-bold-left me-2"></i>
            <span class="nav-link-text">Ir al panel de socio</span>
          </router-link>
        </li>
        <li class="nav-item mt-3 pt-3 border-top border-light">
          <router-link to="/cuenta" class="nav-link text-sm">
            <i class="ni ni-settings-gear-65 text-secondary me-2"></i>
            <span class="nav-link-text">Configuración</span>
          </router-link>
        </li>
        <li class="nav-item">
          <a
            href="javascript:void(0)"
            class="nav-link text-sm text-danger"
            :class="{ 'opacity-50': logoutLoading }"
            @click="cerrarSesion"
          >
            <i class="ni ni-key-25 text-danger me-2"></i>
            <span class="nav-link-text">{{ logoutLoading ? "Cerrando…" : "Cerrar sesión" }}</span>
          </a>
        </li>
      </template>

      <template v-else>
      <li v-if="authUser" class="nav-item px-2 text-center border-bottom border-light pb-3 mb-2">
        <router-link to="/cuenta" class="text-decoration-none d-block">
          <div
            class="avatar avatar-lg rounded-circle bg-gradient-primary mx-auto text-white d-flex align-items-center justify-content-center shadow-sm"
            style="width: 56px; height: 56px; min-width: 56px; font-size: 1rem"
          >
            {{ userInitials }}
          </div>
          <div class="text-sm font-weight-bold text-dark mt-2 text-truncate">{{ authUser.name }}</div>
          <div class="text-xs text-muted">#{{ authUser.member_code ?? "—" }}</div>
          <div class="text-xs text-muted text-truncate">{{ countryLabel }}</div>
        </router-link>
      </li>

      <!-- Dashboard -->
      <li class="nav-item">
        <sidenav-item
          to="/dashboard-default"
          :class="getRoute() === 'dashboard-default' ? 'active' : ''"
          :navText="isRTL ? 'لوحة القيادة' : 'Dashboard'"
        >
          <template v-slot:icon>
            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>

      <li v-if="canAccessAdmin" class="nav-item">
        <sidenav-item
          to="/admin/dashboard"
          :class="getRoute() === 'admin' ? 'active' : ''"
          :navText="isRTL ? 'لوحة الإدارة' : 'Panel empresa'"
        >
          <template v-slot:icon>
            <i class="ni ni-settings-gear-65 text-danger text-sm opacity-10"></i>
          </template>
        </sidenav-item>
      </li>

      <!-- redss -->
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" @click="toggle('programaComercial')">
          <i class="ni ni-chart-bar-32 text-warning me-2"></i>
          <span class="nav-link-text ms-1">MI RED</span>
          <i :class="['ni', isOpen.programaComercial ? 'ni-bold-down' : 'ni-bold-right', 'ms-auto']"></i>
        </a>
        <div class="collapse" :class="{ show: isOpen.programaComercial }">
          <ul class="nav ms-1">
            <li class="nav-item">
              <router-link to="/cardarbol" class="nav-link">
                <i class="ni ni-single-02 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Arbol Binario</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/cardreferidos" class="nav-link">
                <i class="ni ni-collection text-info"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Referidos Directos</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="" class="nav-link">
                <i class="ni ni-check-bold text-warning"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Estadisticas del Equipo</span>
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- ganancias -->
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" @click="toggle('arranqueExplosivo')">
          <i class="ni ni-calendar-grid-58 text-warning me-2"></i>
          <span class="nav-link-text ms-1">GANANCIAS</span>
          <i :class="['ni', isOpen.arranqueExplosivo ? 'ni-bold-down' : 'ni-bold-right', 'ms-auto']"></i>
        </a>
        <div class="collapse" :class="{ show: isOpen.arranqueExplosivo }">
          <ul class="nav ms-1">
            <li class="nav-item">
              <router-link to="/comisiones" class="nav-link">
                <i class="ni ni-bullet-list-67 text-primary"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Comisiones</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/billetera" class="nav-link">
                <i class="ni ni-books text-info"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Billetera</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod2" class="nav-link">
                <i class="ni ni-target text-warning"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Retiros</span>
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- compras -->
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" @click="toggle('universidadEmpresarios')">
          <i class="ni ni-hat-3 text-primary me-2"></i>
          <span class="nav-link-text ms-1">COMPRAS</span>
          <i :class="['ni', isOpen.universidadEmpresarios ? 'ni-bold-down' : 'ni-bold-right', 'ms-auto']"></i>
        </a>
        <div class="collapse" :class="{ show: isOpen.universidadEmpresarios }">
          <ul class="nav ms-1">
            <li class="nav-item">
              <router-link to="/productos" class="nav-link">
                <i class="ni ni-hat-3 text-primary"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Productos</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/compras-realizadas" class="nav-link">
                <i class="ni ni-chat-round text-warning"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Compras realizadas</span>
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- Herramientas -->
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" @click="toggle('negocioOnline')">
          <i class="ni ni-world-2 text-info me-2"></i>
          <span class="nav-link-text ms-1">HERRAMIENTAS</span>
          <i :class="['ni', isOpen.negocioOnline ? 'ni-bold-down' : 'ni-bold-right', 'ms-auto']"></i>
        </a>
        <div class="collapse" :class="{ show: isOpen.negocioOnline }">
          <ul class="nav ms-1">
            <li class="nav-item">
              <router-link to="/cardcampanas" class="nav-link">
                <i class="ni ni-bell-55 text-primary"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Campañas de Marketing</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod2" class="nav-link">
                <i class="ni ni-settings-gear-65 text-info"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Noticias y Eventos</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/linkreferidos" class="nav-link">
                <i class="ni ni-chart-bar-32 text-warning"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Link de Referidos</span>
              </router-link>
            </li>
          </ul>
        </div>
      </li>

      <!-- Mi Cuenta -->
      <li class="nav-item">
        <router-link to="/cuenta" class="nav-link">
          <i class="ni ni-single-02 text-primary me-2"></i>
          <span class="nav-link-text ms-1">MI CUENTA</span>
        </router-link>
      </li>

      <li class="nav-item mt-3 pt-3 border-top border-light">
        <router-link to="/cuenta" class="nav-link text-sm">
          <i class="ni ni-settings-gear-65 text-secondary me-2"></i>
          <span class="nav-link-text ms-1">Configuración</span>
        </router-link>
      </li>
      <li class="nav-item">
        <a
          href="javascript:void(0)"
          class="nav-link text-sm text-danger"
          :class="{ 'opacity-50': logoutLoading }"
          @click="cerrarSesion"
        >
          <i class="ni ni-key-25 text-danger me-2"></i>
          <span class="nav-link-text ms-1">{{ logoutLoading ? "Cerrando…" : "Cerrar sesión" }}</span>
        </a>
      </li>
      </template>

    </ul>
  </div>
</template>
<style scoped>
/* Ajuste automático de títulos en sidebar */
.nav-link-text,
.sidenav-normal {
  display: block;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.nav-link {
  display: flex;
  align-items: center;
  width: 100%;
}

.nav-link svg.sidenav-mini-icon,
.nav-link i.sidenav-mini-icon {
  flex-shrink: 0;
}

.flex-grow-1 {
  flex-grow: 1;
}
</style>