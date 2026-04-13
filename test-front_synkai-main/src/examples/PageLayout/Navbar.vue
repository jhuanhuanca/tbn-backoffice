<script setup>
defineProps({
  btnBackground: {
    type: String,
    default: "",
  },
  isBlur: {
    type: String,
    default: "",
  },
  darkMode: {
    type: Boolean,
    default: false,
  },
  isBtn: { type: String, default: "bg-gradient-light" },
});
</script>
<template>
  <nav
    class="navbar navbar-expand-lg top-0 z-index-3 position-absolute mt-3 mt-sm-4 w-100 px-0 auth-top-navbar"
    :class="isBlur ? isBlur : 'shadow-none my-2 navbar-transparent'"
  >
    <div class="container-fluid px-3 px-sm-4">
      <router-link
        class="navbar-brand font-weight-bolder text-truncate me-2"
        style="max-width: min(60vw, 280px)"
        :class="darkMode ? 'text-dark' : 'text-white'"
        to="/welcom"
      >
        TBN LIVING
      </router-link>
      <button
        class="navbar-toggler shadow-none border-0 ms-auto"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#authNavCollapse"
        aria-controls="authNavCollapse"
        aria-expanded="false"
        aria-label="Abrir menú"
      >
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1" :class="darkMode ? 'bg-dark' : ''"></span>
          <span class="navbar-toggler-bar bar2" :class="darkMode ? 'bg-dark' : ''"></span>
          <span class="navbar-toggler-bar bar3" :class="darkMode ? 'bg-dark' : ''"></span>
        </span>
      </button>
      <div id="authNavCollapse" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item">
            <router-link
              class="nav-link py-2 py-lg-1"
              :class="darkMode ? 'text-dark' : 'text-white'"
              to="/signup"
            >
              <i class="ni ni-single-02 opacity-8 me-1" aria-hidden="true"></i>
              Registro socio
            </router-link>
          </li>
          <li class="nav-item">
            <router-link
              class="nav-link py-2 py-lg-1"
              :class="darkMode ? 'text-dark' : 'text-white'"
              to="/registro-cliente-preferente"
            >
              <i class="ni ni-shop opacity-8 me-1" aria-hidden="true"></i>
              Cliente preferente
            </router-link>
          </li>
          <li class="nav-item">
            <router-link
              class="nav-link py-2 py-lg-1"
              :class="darkMode ? 'text-dark' : 'text-white'"
              to="/signin"
            >
              <i class="ni ni-key-25 opacity-8 me-1" aria-hidden="true"></i>
              Iniciar sesión
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.auth-top-navbar :deep(.navbar-toggler-bar) {
  background: #fff;
}
.auth-top-navbar :deep(.navbar-toggler-icon .bg-dark) {
  background: #344767 !important;
}
</style>
