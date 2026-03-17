<script setup>
import { computed } from "vue";
import { useStore } from "vuex";
import SidenavList from "./SidenavList.vue";
import logo from '@/assets/img/synkailogo2.png';
import logoWhite from '@/assets/img/synkailogo2.png';

const store = useStore();
const isRTL = computed(() => store.state.isRTL);
const layout = computed(() => store.state.layout);
const sidebarType = computed(() => store.state.sidebarType);
const darkMode = computed(() => store.state.darkMode);

function closeSidenav() {
  if (store.state.isPinned) store.commit("sidebarMinimize");
}
</script>
<template>
  <aside
    class="my-3 overflow-y-auto overflow-x-hidden border-0 sidenav navbar navbar-vertical navbar-expand-xs border-radius-xl"
    :class="`${isRTL ? 'me-3 rotate-caret fixed-end' : 'fixed-start ms-3'} ${layout === 'landing' ? 'bg-transparent shadow-none' : ''} ${sidebarType}`"
    id="sidenav-main"
  >
    <div class="sidenav-header">
      <i
        class="top-0 p-3 cursor-pointer fas fa-times text-secondary opacity-5 position-absolute end-0 d-none d-xl-none"
        aria-hidden="true"
        id="iconSidenav"
        role="button"
        @click="closeSidenav"
      />

      <router-link class="m-0 navbar-brand" to="/Dashboard-default">
        <img
            :src="darkMode ? logoWhite : logo"
            class="navbar-brand-img h-100"
            alt="main_logo"
          />
      </router-link>
    </div>

    <hr class="mt-0 horizontal dark" />

    <sidenav-list />
  </aside>
</template>
