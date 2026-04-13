<script setup>
import { ref, onBeforeUnmount, onBeforeMount } from "vue";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import Navbar from "@/examples/PageLayout/Navbar.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonSwitch from "@/components/ArgonSwitch.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import api from "@/services/api";
import { LATAM_COUNTRIES } from "@/constants/latamCountries";

const body = document.getElementsByTagName("body")[0];
const store = useStore();
const router = useRouter();
const route = useRoute();

const email = ref("");
const password = ref("");
const rememberMe = ref(false);
const countryCode = ref("BO");
const error = ref("");

onBeforeMount(() => {
  store.state.hideConfigButton = true;
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  body.classList.remove("bg-gray-100");
});

onBeforeUnmount(() => {
  store.state.hideConfigButton = false;
  store.state.showNavbar = true;
  store.state.showSidenav = true;
  store.state.showFooter = true;
  body.classList.add("bg-gray-100");
});

const login = async () => {
  error.value = "";
  try {
    const response = await api.post("/login", {
      email: email.value.trim(),
      password: password.value,
      country_code: countryCode.value || undefined,
    });

    if (response.data.token) {
      await store.dispatch("auth/setAuth", {
        user: response.data.user,
        token: response.data.token,
      });
      const redir = route.query.redirect;
      if (typeof redir === "string" && redir.startsWith("/")) {
        router.push(redir);
      } else if (response.data.user?.can_access_admin_panel) {
        router.push("/admin/dashboard");
      } else if (response.data.user?.is_preferred_customer) {
        router.push("/cliente-preferente");
      } else {
        router.push("/dashboard-default");
      }
    } else {
      error.value = "Credenciales incorrectas.";
    }
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.code === "email_unverified") {
      error.value =
        "Debes confirmar tu correo antes de entrar. Revisa tu bandeja o solicita un nuevo enlace en «Verificar correo».";
      return;
    }
    error.value = "Credenciales incorrectas o servidor no disponible.";
  }
};
</script>

<template>
  <div class="auth-shell min-vh-100 d-flex flex-column">
    <div class="container top-0 position-sticky z-index-sticky px-2 px-sm-3">
      <navbar
        isBlur="blur border-radius-lg my-3 py-2 start-0 end-0 mx-3 mx-sm-4 shadow"
        :darkMode="true"
        isBtn="bg-gradient-success"
      />
    </div>

    <main class="auth-shell__main flex-grow-1 d-flex align-items-center py-4 py-lg-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-sm-11 col-md-9 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-lg auth-card">
              <div class="card-header text-center bg-white border-0 pt-4 pb-0 px-4">
                <img
                  src="@/assets/img/synkailogo2.png"
                  alt="Logo"
                  class="mb-3 auth-card__logo"
                  width="180"
                  height="auto"
                />
                <h4 class="font-weight-bolder text-dark mb-1">Iniciar sesión</h4>
                <p class="text-xs text-muted mb-0 px-lg-2">
                   Usa el correo registrado y tu contraseña.
                </p>
              </div>
              <div class="card-body px-4 pb-4 pt-3">
                <form @submit.prevent="login" class="auth-form">
                  <div class="mb-3">
                    <label class="form-label text-sm text-muted mb-1">País</label>
                    <select v-model="countryCode" class="form-select">
                      <option v-for="c in LATAM_COUNTRIES" :key="c.code" :value="c.code">
                        {{ c.flag }} {{ c.name }}
                      </option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm text-muted mb-1">Correo electrónico</label>
                    <argon-input v-model="email" id="email" type="email" placeholder="correo@ejemplo.com" size="lg" />
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm text-muted mb-1">Contraseña</label>
                    <argon-input v-model="password" id="password" type="password" placeholder="••••••••" size="lg" />
                  </div>
                  <argon-switch
                    :checked="rememberMe"
                    id="rememberMe"
                    name="remember-me"
                    @change="rememberMe = $event.target.checked"
                  >
                    Recordarme en este equipo
                  </argon-switch>

                  <div v-if="error" class="alert alert-danger text-white text-sm py-2 mt-3 mb-0" role="alert">
                    {{ error }}
                  </div>

                  <div class="d-grid gap-2 mt-4">
                    <argon-button variant="gradient" color="success" fullWidth size="lg" type="submit">
                      Entrar al panel
                    </argon-button>
                  </div>

                  <p class="text-xs text-secondary mt-3 mb-0 text-center">
                    <router-link to="/verificar-correo" class="text-primary font-weight-bold"
                      >¿No verificaste el correo?</router-link
                    >
                  </p>
                </form>
              </div>
              <div class="card-footer text-center bg-transparent border-0 px-4 pb-4 pt-0">
                <p class="text-sm text-muted mb-0">
                  ¿No tienes cuenta?
                  <router-link to="/signup" class="text-success font-weight-bold text-gradient"> Registro socio </router-link>
                  ·
                  <router-link to="/registro-cliente-preferente" class="text-dark font-weight-bold">
                    Cliente preferente
                  </router-link>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.auth-shell {
  background: linear-gradient(180deg, #f8f9fa 0%, #eef2f0 45%, #e8f5e9 100%);
}
.auth-card {
  border-radius: 1rem;
}
.auth-card__logo {
  max-width: 200px;
  height: auto;
}
.auth-form :deep(.form-control),
.auth-form :deep(.input-group) {
  border-radius: 0.5rem;
}
</style>
