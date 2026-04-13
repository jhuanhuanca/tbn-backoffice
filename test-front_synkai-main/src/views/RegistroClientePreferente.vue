<script setup>
import { ref, onBeforeMount, onBeforeUnmount } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useStore } from "vuex";
import Navbar from "@/examples/PageLayout/Navbar.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import { registerPreferredCustomer } from "@/services/auth";

const router = useRouter();
const route = useRoute();
const store = useStore();

const name = ref("");
const documentId = ref("");
const sponsorCode = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const loading = ref(false);
const error = ref("");

const body = document.getElementsByTagName("body")[0];

function validateCiNit(v) {
  const s = String(v || "").trim();
  if (!s) return "El CI / NIT es obligatorio.";
  if (s.length < 5 || s.length > 32) return "CI / NIT: entre 5 y 32 caracteres.";
  if (!/^[A-Za-z0-9.-]+$/.test(s)) return "CI / NIT: solo letras, números, punto y guion.";
  return "";
}

function validateEmailFormat(v) {
  const s = String(v || "").trim();
  if (!s) return "El correo es obligatorio.";
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s)) return "Introduce un correo electrónico válido.";
  return "";
}

onBeforeMount(() => {
  store.state.hideConfigButton = true;
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  body.classList.remove("bg-gray-100");
  const q = route.query.sponsor || route.query.ref || route.query.codigo;
  if (q) sponsorCode.value = String(q);
});

onBeforeUnmount(() => {
  store.state.hideConfigButton = false;
  store.state.showNavbar = true;
  store.state.showSidenav = true;
  store.state.showFooter = true;
  body.classList.add("bg-gray-100");
});

async function submit() {
  error.value = "";
  const docErr = validateCiNit(documentId.value);
  const emErr = validateEmailFormat(email.value);
  if (!name.value.trim()) {
    error.value = "El nombre es obligatorio.";
    return;
  }
  if (!sponsorCode.value.trim()) {
    error.value = "El código de patrocinador es obligatorio.";
    return;
  }
  if (docErr) {
    error.value = docErr;
    return;
  }
  if (emErr) {
    error.value = emErr;
    return;
  }
  if (password.value.length < 8) {
    error.value = "La contraseña debe tener al menos 8 caracteres.";
    return;
  }
  if (password.value !== passwordConfirmation.value) {
    error.value = "Las contraseñas no coinciden.";
    return;
  }
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
      error.value = Object.values(errs)
        .flat()
        .join(" ");
    } else {
      error.value = msg || "No se pudo registrar. Revisa los datos.";
    }
  } finally {
    loading.value = false;
  }
}
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
          <div class="col-12 col-sm-11 col-md-9 col-lg-6 col-xl-5">
            <div class="card border-0 shadow-lg auth-card">
              <div class="card-header text-center bg-white border-0 pt-4 pb-0 px-4">
                <h4 class="font-weight-bolder text-dark mb-1">Cliente preferente</h4>
                <p class="text-sm text-secondary mb-0">
                  Compras al precio de cliente. Tu patrocinador recibe el bono de venta directa. Tras registrarte,
                  verifica tu correo para ingresar.
                </p>
              </div>
              <div class="card-body px-4 pt-3 pb-4">
                <div v-if="error" class="alert alert-danger text-white text-sm py-2 mb-3">{{ error }}</div>
                <form role="form" class="auth-form" @submit.prevent="submit">
                  <div class="mb-3">
                    <label class="form-label text-sm mb-1">Nombre completo <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-name"
                      v-model="name"
                      type="text"
                      placeholder="Nombre y apellidos"
                      name="name"
                      size="lg"
                    />
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm mb-1">CI / NIT <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-doc"
                      v-model="documentId"
                      type="text"
                      placeholder="Ej. 1234567 LP o NIT"
                      name="document_id"
                      size="lg"
                    />
                    <p class="text-xxs text-secondary mb-0 mt-1">5–32 caracteres: letras, números, punto o guion.</p>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm mb-1">Código de patrocinador <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-sponsor"
                      v-model="sponsorCode"
                      type="text"
                      placeholder="Código de tu patrocinador"
                      name="sponsor"
                      size="lg"
                    />
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm mb-1">Correo electrónico <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-email"
                      v-model="email"
                      type="email"
                      placeholder="correo@ejemplo.com"
                      name="email"
                      size="lg"
                    />
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-sm mb-1">Contraseña <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-pass"
                      v-model="password"
                      type="password"
                      placeholder="Mínimo 8 caracteres"
                      name="password"
                      size="lg"
                    />
                  </div>
                  <div class="mb-4">
                    <label class="form-label text-sm mb-1">Confirmar contraseña <span class="text-danger">*</span></label>
                    <argon-input
                      id="rcp-pass2"
                      v-model="passwordConfirmation"
                      type="password"
                      placeholder="Repite la contraseña"
                      name="password_confirmation"
                      size="lg"
                    />
                  </div>
                  <argon-button
                    class="w-100"
                    variant="gradient"
                    color="success"
                    fullWidth
                    size="lg"
                    :disabled="loading"
                  >
                    {{ loading ? "Enviando…" : "Registrarme" }}
                  </argon-button>
                </form>
                <p class="text-sm mt-4 mb-0 text-center text-secondary">
                  ¿Eres socio MLM?
                  <router-link to="/signup" class="text-success font-weight-bold">Registro socio</router-link>
                  <span class="mx-1">·</span>
                  <router-link to="/signin" class="text-dark font-weight-bold">Iniciar sesión</router-link>
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
.auth-shell__main {
  min-height: 0;
}
.auth-card {
  border-radius: 0.75rem;
}
.auth-form :deep(.input-group) {
  margin-bottom: 0;
}
.text-xxs {
  font-size: 0.65rem;
}
</style>
