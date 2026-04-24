<script setup>
import { ref, onBeforeUnmount, onBeforeMount, watch, onMounted } from "vue";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import Navbar from "@/examples/PageLayout/Navbar.vue";
import AppFooter from "@/examples/PageLayout/Footer.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import api from "@/services/api";
import { fetchSponsorByCode } from "@/services/sponsor";
import { fetchPackages } from "@/services/me";
import { LATAM_COUNTRIES } from "@/constants/latamCountries";
import termsPdfUrl from "@/assets/doc/pdfejemplo.pdf";
const body = document.getElementsByTagName("body")[0];
const store = useStore();
const router = useRouter();
const route = useRoute();

const name = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
/** CI / NIT — se envía al API como `document_id` (campo obligatorio ci_nit en negocio). */
const ciNit = ref("");
const phone = ref("");
const birthDate = ref("");
const sponsorReferralCode = ref("");
const preferredBinaryLeg = ref("auto");
const sponsorValidated = ref(null);
const sponsorCheckLoading = ref(false);
const sponsorError = ref("");
const terms = ref(false);
const termsModalOpen = ref(false);
const error = ref("");
const loading = ref(false);
const packagesList = ref([]);
const selectedPackageId = ref("");
const countryCode = ref("BO");

function applyPackageFromQuery() {
  const pkg = route.query.package;
  if (pkg) {
    selectedPackageId.value = String(pkg);
    return;
  }
  const slug = route.query.slug;
  if (slug && packagesList.value.length) {
    const found = packagesList.value.find((p) => p.slug === String(slug));
    if (found) {
      selectedPackageId.value = String(found.id);
    }
  }
}

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

function applySponsorFromRoute() {
  const q = route.query.sponsor || route.query.ref || route.query.codigo;
  if (q) {
    sponsorReferralCode.value = String(q).trim();
  }
}

onMounted(async () => {
  applySponsorFromRoute();
  if (sponsorReferralCode.value) {
    validateSponsor();
  }
  try {
    const res = await fetchPackages();
    packagesList.value = res.data || [];
  } catch {
    packagesList.value = [];
  }
  applyPackageFromQuery();
});

watch(
  () => [route.query.sponsor, route.query.ref, route.query.codigo],
  () => {
    applySponsorFromRoute();
    if (sponsorReferralCode.value) {
      validateSponsor();
    }
  }
);

watch(
  () => route.query.package,
  () => {
    applyPackageFromQuery();
  }
);

watch(
  () => route.query.slug,
  () => {
    applyPackageFromQuery();
  }
);

async function validateSponsor() {
  const code = sponsorReferralCode.value.trim();
  if (!code) {
    sponsorValidated.value = null;
    sponsorError.value = "";
    return;
  }
  sponsorCheckLoading.value = true;
  sponsorError.value = "";
  try {
    const data = await fetchSponsorByCode(code);
    sponsorValidated.value = data.sponsor;
  } catch {
    sponsorValidated.value = null;
    sponsorError.value = "No se encontró un patrocinador con ese código.";
  } finally {
    sponsorCheckLoading.value = false;
  }
}

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

async function signup() {
  error.value = "";
  if (!terms.value) {
    error.value = "Debe aceptar los términos y condiciones.";
    return;
  }
  const ciErr = validateCiNit(ciNit.value);
  if (ciErr) {
    error.value = ciErr;
    return;
  }
  const emErr = validateEmailFormat(email.value);
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
  if (!["left", "right", "auto"].includes(preferredBinaryLeg.value)) {
    error.value = "Debes seleccionar una opción de colocación binaria (izquierda, derecha o automático).";
    return;
  }
  loading.value = true;
  try {
    if (sponsorReferralCode.value.trim()) {
      await validateSponsor();
      if (!sponsorValidated.value) {
        error.value = sponsorError.value || "Código de patrocinador inválido.";
        loading.value = false;
        return;
      }
    }
    const payload = {
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: passwordConfirmation.value,
      document_id: ciNit.value.trim(),
      phone: phone.value.trim(),
      birth_date: birthDate.value,
    };
    if (sponsorReferralCode.value.trim()) {
      payload.sponsor_referral_code = sponsorReferralCode.value.trim();
    }
    payload.country_code = countryCode.value;
    payload.preferred_binary_leg = preferredBinaryLeg.value;
    if (selectedPackageId.value) {
      payload.registration_package_id = parseInt(String(selectedPackageId.value), 10);
    }
    const response = await api.post("/register", payload);
    if (response.data.requires_email_verification) {
      router.push({
        path: "/verificar-correo",
        query: { email: response.data.email || email.value.trim() },
      });
      return;
    }
    if (response.data.token) {
      await store.dispatch("auth/setAuth", {
        user: response.data.user,
        token: response.data.token,
      });
      router.push("/dashboard-default");
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const first = Object.values(err.response.data.errors)[0];
      error.value = Array.isArray(first) ? first[0] : String(first);
    } else {
      error.value =
        err.response?.data?.message || "No se pudo completar el registro.";
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

  <main class="main-content mt-0 pb-5">
    <div
      class="page-header align-items-start min-vh-50 pt-10 pb-11 m-3 border-radius-lg"
      style="position: relative; overflow: hidden"
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
          <div class="col-lg-6 text-center mx-auto">
            <h1 class="text-white mb-2 mt-5">Crear cuenta</h1>
            <p class="text-lead text-white mb-0">
              Registro de socio. Si llegaste por enlace de referido, tu patrocinador
              quedará vinculado automáticamente.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="container px-3">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-6 col-lg-7 col-md-10 mx-auto">
          <div class="card border-0 shadow-lg signup-card z-index-0">
            <div class="card-header text-center bg-white border-0 pt-4 pb-0">
              <img
                src="@/assets/img/synkailogo2.png"
                alt="Logo"
                class="mb-2 mt-2 signup-card__logo"
                width="176"
                height="auto"
              />
              <h5 class="text-dark font-weight-bolder mb-1">Inscripción socio</h5>
              
            </div>
            <div class="card-body px-4 pb-2">
              <h6 class="text-sm text-dark font-weight-bolder mb-3">Paquete y país</h6>
              <div class="mb-4">
                <label class="form-label text-sm mb-1">País</label>
                <select v-model="countryCode" class="form-select">
                  <option v-for="c in LATAM_COUNTRIES" :key="c.code" :value="c.code">
                    {{ c.flag }} {{ c.name }}
                  </option>
                </select>
              </div>

              <div
                v-if="sponsorReferralCode"
                class="alert alert-info text-white text-sm mb-3"
                role="alert"
              >
                <template v-if="sponsorCheckLoading">Verificando patrocinador…</template>
                <template v-else-if="sponsorValidated">
                  Patrocinador:
                  <strong>{{ sponsorValidated.name }}</strong>
                  (código {{ sponsorValidated.referral_code }})
                </template>
                <template v-else-if="sponsorError">{{ sponsorError }}</template>
                <template v-else>Ingresa un código válido o continúa sin patrocinador.</template>
              </div>

              <form @submit.prevent="signup" class="mt-4">
                <h6 class="text-sm text-dark font-weight-bolder mb-3">Datos personales</h6>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Código de patrocinador (opcional)</label>
                  <argon-input
                    v-model="sponsorReferralCode"
                    id="sponsor"
                    type="text"
                    placeholder="Ej. 10, 11, 100…"
                  />
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary mt-2"
                    :disabled="sponsorCheckLoading || !sponsorReferralCode.trim()"
                    @click="validateSponsor"
                  >
                    Validar código
                  </button>
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Nombre completo <span class="text-danger">*</span></label>
                  <argon-input v-model="name" id="name" type="text" placeholder="Nombre y apellidos" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Correo electrónico <span class="text-danger">*</span></label>
                  <argon-input v-model="email" id="email" type="email" placeholder="correo@empresa.com" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">CI / NIT (ci_nit) <span class="text-danger">*</span></label>
                  <argon-input v-model="ciNit" id="ciNit" type="text" placeholder="Ej. 1234567 LP o NIT" />
                  <p class="text-xxs text-muted mb-0 mt-1">Obligatorio. Se guarda como documento de identidad en el sistema.</p>
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Teléfono</label>
                  <argon-input v-model="phone" id="phone" type="text" placeholder="Celular o fijo" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Fecha de nacimiento</label>
                  <argon-input v-model="birthDate" id="bd" type="date" placeholder="" />
                </div>
           
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Contraseña (mín. 8) <span class="text-danger">*</span></label>
                  <div class="input-group">
                  <argon-input 
                    v-model="password" 
                    id="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    placeholder="Contraseña" 
                  />
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm mb-1">Confirmar contraseña <span class="text-danger">*</span></label>
                  <div class="input-group">
                  <argon-input
                    v-model="passwordConfirmation"
                    id="password2"
                    :type="showPasswordConfirm ? 'text' : 'password'"
                    placeholder="Repite la contraseña"
                  />
                 
                  </div>
                </div>

                <div class="form-check mb-2">
                  <input
                    id="termsMlm"
                    v-model="terms"
                    class="form-check-input"
                    type="checkbox"
                  />
                  <label class="form-check-label text-sm" for="termsMlm">
                    Acepto los términos y condiciones del programa.
                  </label>
                  <button
                    type="button"
                    class="btn btn-link btn-sm p-0 ms-2 align-baseline"
                    @click="termsModalOpen = true"
                  >
                    Ver términos
                  </button>
                </div>

                <p v-if="error" class="text-danger mt-2 text-sm">{{ error }}</p>

                <div class="d-grid pt-2">
                  <argon-button
                    fullWidth
                    color="dark"
                    variant="gradient"
                    class="mb-2"
                    type="submit"
                    :disabled="loading"
                  >
                    {{ loading ? "Registrando…" : "Registrarme" }}
                  </argon-button>
                </div>

                <p class="text-sm mt-3 mb-0 text-center">
                  ¿Ya tienes cuenta?
                  <router-link to="/signin" class="text-dark font-weight-bolder">Iniciar sesión</router-link>
                </p>
                <p class="text-sm mt-2 mb-0 text-center text-muted">
                  ¿Solo quieres comprar como cliente?
                  <router-link to="/registro-cliente-preferente" class="text-success font-weight-bold"
                    >Registro cliente preferente</router-link
                  >
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <app-footer />

  <div v-if="termsModalOpen" class="modal-backdrop fade show"></div>
  <div
    v-if="termsModalOpen"
    class="modal fade show d-block"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    @click.self="termsModalOpen = false"
  >
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Términos y condiciones</h6>
          <button type="button" class="btn-close" aria-label="Cerrar" @click="termsModalOpen = false" />
        </div>
        <div class="modal-body">
          <p class="text-sm text-dark mb-0" style="white-space: pre-line">
            Los presentes términos y condiciones regulan el uso de este servicio, por lo que al acceder y utilizarlo,
            el usuario acepta cumplir con las disposiciones aquí establecidas. El usuario se compromete a hacer un uso
            adecuado de la plataforma, evitando actividades ilícitas o que puedan afectar su funcionamiento. El
            proveedor se reserva el derecho de modificar estos términos en cualquier momento, así como de suspender el
            acceso en caso de incumplimiento. Asimismo, no se garantiza la disponibilidad continua del servicio ni la
            ausencia de errores, y el uso del mismo es bajo la responsabilidad del usuario.
          </p>
        </div>
        <div class="modal-footer">
          <a class="btn btn-outline-success btn-sm" :href="termsPdfUrl" download="terminos_y_condiciones.pdf">
            Descargar PDF
          </a>
          <button type="button" class="btn btn-success btn-sm" @click="termsModalOpen = false">Aceptar</button>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<style scoped>
.signup-card {
  border-radius: 1rem;
}
.signup-card__logo {
  max-width: 180px;
  height: auto;
}
.text-xxs {
  font-size: 0.65rem;
}
</style>
