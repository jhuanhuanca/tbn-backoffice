<script setup>
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";

const store = useStore();
const isRTL = computed(() => store.state.isRTL);

const getRoute = () => {
  const route = useRoute();
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

</script>

<template>
  <div class="collapse navbar-collapse w-auto h-auto h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      
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
   <!-- Sistema de Liderazgo -->
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" @click="toggle('Administracion')">
          <i class="ni ni-compass-04 text-primary me-2"></i>
          <span class="nav-link-text ms-1">Administracion</span>
          <i :class="['ni', isOpen.Administracion ? 'ni-bold-down' : 'ni-bold-right', 'ms-auto']"></i>
        </a>
        <div class="collapse" :class="{ show: isOpen.Administracion }">
          <ul class="nav ms-1">
            <li class="nav-item">
              <router-link to="/profile" class="nav-link">
                <i class="ni ni-single-02 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Perfil</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod2" class="nav-link">
                <i class="ni ni-chat-round text-info"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Contenido de Ventas</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod3" class="nav-link">
                <i class="ni ni-check-bold text-warning"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Giones y presentaciones</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod4" class="nav-link">
                <i class="ni ni-world-2 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Contenido de Accion</span>
              </router-link>
            </li>
             <li class="nav-item">
              <router-link to="/submod4" class="nav-link">
                <i class="ni ni-world-2 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Contenido de Productos</span>
              </router-link>
            </li>
             <li class="nav-item">
              <router-link to="/submod4" class="nav-link">
                <i class="ni ni-world-2 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Contenido de Biblioteca</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/submod4" class="nav-link">
                <i class="ni ni-settings-gear-65 text-success"></i>
                <span class="sidenav-normal ms-2 text-truncate" style="color: #16A34A">Configuracion</span>
              </router-link>
            </li>
          </ul>
        </div>
      </li>
      <!-- Resto de items fijos (billing, profile, signin, signup) -->
      <!-- ... dejan igual ... -->

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