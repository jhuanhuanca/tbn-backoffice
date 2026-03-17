<script setup>
import { ref, onBeforeUnmount, onBeforeMount } from "vue";
import { useStore } from "vuex";
import Navbar from "@/examples/PageLayout/Navbar.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonSwitch from "@/components/ArgonSwitch.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import axios from 'axios';
import { useRouter } from 'vue-router';

const body = document.getElementsByTagName("body")[0];
const store = useStore();
const router = useRouter();

// Estado de los inputs
const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const error = ref('');

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

// Función de login
const login = async () => {
  error.value = ''; // limpiar errores
  try {
    const response = await axios.post('http://api.imparablesjhn.shop/api/login', {
      email: email.value,
      password: password.value,
    });

    // Validar que el backend devolvió un token
    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));

      // Redirigir al dashboard usando Vue Router
      router.push('/dashboard-default');
    } else {
      error.value = 'Credenciales incorrectas';
    }
  } catch (err) {
    console.error(err);
    error.value = 'Credenciales incorrectas';
  }
};
</script>

<template>
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
                  <img src="@/assets/img/synkailogo2.png" alt="Synkai Logo" class="mb-2 mt-5" style="max-width: 200px; height: auto;" />
                  <h4 class="font-weight-bolder">Sign In</h4>
                  <p class="mb-0">Enter your email and password to sign in</p>
                </div>
                <div class="card-body">
                  <form @submit.prevent="login">
                    <div class="mb-3">
                      <argon-input
                        v-model="email"
                        id="email"
                        type="email"
                        placeholder="Email"
                        name="email"
                        size="lg"
                      />
                    </div>
                    <div class="mb-3">
                      <argon-input
                        v-model="password"
                        id="password"
                        type="password"
                        placeholder="Password"
                        name="password"
                        size="lg"
                      />
                    </div>
                    <argon-switch
                      v-model="rememberMe"
                      id="rememberMe"
                      name="remember-me"
                    >Remember me</argon-switch>

                    <div class="text-center">
                      <argon-button
                        class="mt-4"
                        variant="gradient"
                        color="success"
                        fullWidth
                        size="lg"
                        type="submit"
                      >Sign in</argon-button>
                    </div>

                    <p v-if="error" class="text-danger mt-2">{{ error }}</p>
                  </form>
                </div>
                <div class="px-1 pt-0 text-center card-footer px-lg-2">
                  <p class="mx-auto mb-4 text-sm">
                    Don't have an account?
                    <a href="signup" class="text-success text-gradient font-weight-bold">Sign up</a>
                  </p>
                </div>
              </div>
            </div>

            <div class="top-0 my-auto text-center col-6 d-lg-flex d-none h-100 pe-0 position-absolute end-0 justify-content-center flex-column">
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
</template>
