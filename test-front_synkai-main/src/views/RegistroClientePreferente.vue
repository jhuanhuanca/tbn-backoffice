<script setup>
import { ref, onBeforeMount, onBeforeUnmount } from "vue";
import { useRouter, useRoute } from "vue-router";
import Navbar from "@/examples/PageLayout/Navbar.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import { registerPreferredCustomer } from "@/services/auth";

const router = useRouter();
const route = useRoute();

const name = ref("");
const documentId = ref("");
const sponsorCode = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const loading = ref(false);
const error = ref("");

onBeforeMount(() => {
  document.body.classList.remove("bg-gray-100");
  const q = route.query.sponsor || route.query.ref || route.query.codigo;
  if (q) sponsorCode.value = String(q);
});

onBeforeUnmount(() => {
  document.body.classList.add("bg-gray-100");
});

async function submit() {
  error.value = "";
  loading.value = true;
  try {
    await registerPreferredCustomer({
      name: name.value.trim(),
      document_id: documentId.value.trim(),
      sponsor_referral_code: sponsorCode.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    router.push({ name: "VerificarCorreo", query: { email: email.value.trim() } });
  } catch (e) {
    const msg = e.response?.data?.message;
    const errs = e.response?.data?.errors;
    if (errs && typeof errs === "object") {
      error.value = Object.values(errs).flat().join(" ");
    } else {
      error.value = msg || "No se pudo registrar. Revisa los datos.";
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div>
    <div class="container top-0 position-sticky z-index-sticky">
      <div class="row">
        <div class="col-12">
          <navbar
            isBlur="blur border-radius-lg my-3 py-2 start-0 end-0 mx-4 shadow"
            :darkMode="true"
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
              <div class="mx-auto col-xl-5 col-lg-6 col-md-8">
                <div class="card card-plain">
                  <div class="pb-0 card-header text-start">
                    <h4 class="font-weight-bolder">Registro de cliente preferente</h4>
                    <p class="mb-0 text-sm">
                      Compras al precio de cliente. Tu patrocinador recibe el bono de venta directa (diferencia con
                      precio socio). Solo verificación de correo para ingresar.
                    </p>
                  </div>
                  <div class="card-body">
                    <div v-if="error" class="alert alert-danger text-white text-sm py-2">{{ error }}</div>
                    <form role="form" @submit.prevent="submit">
                      <div class="mb-3">
                        <argon-input
                          id="rcp-name"
                          v-model="name"
                          type="text"
                          placeholder="Nombre completo"
                          name="name"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          id="rcp-doc"
                          v-model="documentId"
                          type="text"
                          placeholder="NIT / CI"
                          name="document_id"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          id="rcp-sponsor"
                          v-model="sponsorCode"
                          type="text"
                          placeholder="Código de patrocinador (obligatorio)"
                          name="sponsor"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          id="rcp-email"
                          v-model="email"
                          type="email"
                          placeholder="Correo"
                          name="email"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          id="rcp-pass"
                          v-model="password"
                          type="password"
                          placeholder="Contraseña"
                          name="password"
                          size="lg"
                        />
                      </div>
                      <div class="mb-3">
                        <argon-input
                          id="rcp-pass2"
                          v-model="passwordConfirmation"
                          type="password"
                          placeholder="Confirmar contraseña"
                          name="password_confirmation"
                          size="lg"
                        />
                      </div>
                      <div class="text-center">
                        <argon-button
                          class="mt-2"
                          variant="gradient"
                          color="success"
                          fullWidth
                          size="lg"
                          :disabled="loading"
                        >
                          {{ loading ? "Enviando…" : "Registrarme" }}
                        </argon-button>
                      </div>
                    </form>
                    <p class="text-sm mt-3 mb-0 text-center">
                      ¿Eres socio?
                      <router-link to="/signup" class="text-success font-weight-bold">Registro MLM</router-link>
                      ·
                      <router-link to="/signin" class="text-dark ms-1">Iniciar sesión</router-link>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
