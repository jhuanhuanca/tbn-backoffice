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
      email: email.value,
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
  <div>
    <div class="container top-0 position-sticky z-index-sticky">
      <div class="row">
        <div class="col-12">
          <navbar
            isBlur="blur border-radius-lg my-3 py-2 start-0 end-0 mx-4 shadow"
            v-bind:darkMode="true"
            isBtn="bg-gradient-success"
          />
        </div>
      </div>
    </div>

    <main class="mt-0 main-content">
      <section>
        <div class="page-header min-vh-100">
          <div class="container">
            <div class="row">
              <div class="mx-auto col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0">
                <div class="card card-plain">
                  <div class="pb-0 card-header text-start">
                    <img
                      src="@/assets/img/synkailogo2.png"
                      alt="Logo"
                      class="mb-2 mt-5"
                      style="max-width: 200px; height: auto"
                    />
                    <h4 class="font-weight-bolder">Iniciar sesión</h4>
                    <p class="mb-0 text-sm text-muted">
                      Socio: <strong>demo@empresa.com</strong> · Panel empresa:
                      <strong>admin@empresa.com</strong> · Contraseña seed: <strong>password</strong>
                    </p>
                  </div>
                  <div class="card-body">
                    <form @submit.prevent="login">
                      <div class="mb-3">
                        <label class="form-label text-sm text-muted">País (Latinoamérica)</label>
                        <select v-model="countryCode" class="form-select form-select-lg">
                          <option v-for="c in LATAM_COUNTRIES" :key="c.code" :value="c.code">
                            {{ c.flag }} {{ c.name }}
                          </option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <argon-input
                          v-model="email"
                          id="email"
                          type="email"
                          placeholder="Correo"
                          name="email"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          v-model="password"
                          id="password"
                          type="password"
                          placeholder="Contraseña"
                          name="password"
                          size="lg"
                        />
                      </div>
                      <argon-switch
                        :checked="rememberMe"
                        id="rememberMe"
                        name="remember-me"
                        @change="rememberMe = $event.target.checked"
                      >
                        Recordarme
                      </argon-switch>

                      <div class="text-center">
                        <argon-button
                          class="mt-4"
                          variant="gradient"
                          color="success"
                          fullWidth
                          size="lg"
                          type="submit"
                        >
                          Entrar
                        </argon-button>
                      </div>

                      <p v-if="error" class="text-danger mt-2 text-sm">{{ error }}</p>
                      <p class="text-xs text-secondary mt-2 mb-0 text-center">
                        <router-link to="/verificar-correo" class="text-primary">¿No verificaste el correo?</router-link>
                      </p>
                    </form>
                  </div>
                  <div class="px-1 pt-0 text-center card-footer px-lg-2">
                    <p class="mx-auto mb-4 text-sm">
                      ¿No tienes cuenta?
                      <router-link to="/signup" class="text-success text-gradient font-weight-bold">
                        Registrarse
                      </router-link>
                    </p>
                  </div>
                </div>
              </div>

              <div
                class="top-0 my-auto text-center col-6 d-lg-flex d-none h-100 pe-0 position-absolute end-0 justify-content-center flex-column"
              >
                <div
                  class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                  :style="{
                    backgroundImage: `url(${require('@/assets/img/synkailogo.png')})`,
                    backgroundSize: 'cover',
                  }"
                >
                  <span class="mask bg-gradient-success opacity-6"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
