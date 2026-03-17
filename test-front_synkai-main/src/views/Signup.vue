<script setup>
import { ref, onBeforeUnmount, onBeforeMount } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";

import Navbar from "@/examples/PageLayout/Navbar.vue";
import AppFooter from "@/examples/PageLayout/Footer.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonCheckbox from "@/components/ArgonCheckbox.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import axios from 'axios';

const body = document.getElementsByTagName("body")[0];
const store = useStore();
const router = useRouter();

// Estados de los inputs
const name = ref('');
const email = ref('');
const password = ref('');
const terms = ref(false);
const error = ref('');
const success = ref(''); // Mensaje de éxito

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

// Función de registro
const signup = async () => {
  error.value = '';
  success.value = '';

  if (!terms.value) {
    error.value = 'Debe aceptar los términos y condiciones';
    return;
  }

  try {
    const response = await axios.post('http://api.imparablesjhn.shop/api/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password.value, // Confirmación simple
    });

    if (response.data.user) {
      success.value = 'Usuario creado exitosamente. Redirigiendo al login...';
      
      // Esperar 1.5 segundos para que el usuario vea el mensaje
      setTimeout(() => {
        router.push('/signin');
      }, 1500);
    } else {
      error.value = 'Error al crear el usuario';
    }
  } catch (err) {
    if (err.response && err.response.data.errors) {
      const firstError = Object.values(err.response.data.errors)[0][0];
      error.value = firstError;
    } else {
      error.value = 'Error al crear el usuario';
    }
  }
};
</script>

<template>
  <div class="container top-0 position-sticky z-index-sticky">
    <div class="row">
      <div class="col-12">
        <navbar isBtn="bg-gradient-light" />
      </div>
    </div>
  </div>

  <main class="main-content mt-0">
    <div
      class="page-header align-items-start min-vh-50 pt-10 pb-11 m-3 border-radius-lg"
      style="position: relative; overflow: hidden;"
    >
      <video
      autoplay
      muted
      loop
      style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
      "
      >
      <source src="@/assets/videos/video1.mp4" type="video/mp4" />
      </video>
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 text-center mx-auto">
            <h1 class="text-white mb-2 mt-5">Welcome!</h1>
            <p class="text-lead text-white">
              “Welcome to TBN-LIVING. Activate your mind today, scale your business tomorrow.”
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card z-index-0">
            <div class="card-header text-center pt-4">
              <img src="@/assets/img/synkailogo2.png" alt="Synkai Logo" class="mb-2 mt-5" style="max-width: 200px; height: auto;" />
              <h5>Register</h5>
            </div>
            <div class="card-body">
              <form @submit.prevent="signup">
                <div class="mb-3">
                  <argon-input
                    v-model="name"
                    id="name"
                    type="text"
                    placeholder="Name"
                  />
                </div>
                <div class="mb-3">
                  <argon-input
                    v-model="email"
                    id="email"
                    type="email"
                    placeholder="Email"
                  />
                </div>
                <div class="mb-3">
                  <argon-input
                    v-model="password"
                    id="password"
                    type="password"
                    placeholder="Password"
                  />
                </div>

                <argon-checkbox
                  :checked="terms"
                  @change="terms = $event.target.checked"
                >
                  <label class="form-check-label" for="flexCheckDefault">
                    I agree the
                    <a href="javascript:;" class="text-dark font-weight-bolder">Terms and Conditions</a>
                  </label>
                </argon-checkbox>

                <!-- Mensajes de error y éxito -->
                <p v-if="error" class="text-danger mt-2">{{ error }}</p>
                <p v-if="success" class="text-success mt-2">{{ success }}</p>

                <div class="text-center">
                  <argon-button
                    fullWidth
                    color="dark"
                    variant="gradient"
                    class="my-4 mb-2"
                    type="submit"
                  >Sign up</argon-button>
                </div>

                <p class="text-sm mt-3 mb-0 text-center">
                  Already have an account?
                  <a href="/signin" class="text-dark font-weight-bolder">Sign in</a>
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <app-footer />
</template>