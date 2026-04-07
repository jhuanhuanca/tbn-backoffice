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
import { REGISTRATION_PAYMENT_METHODS } from "@/constants/registrationPayments";
import { STATIC_PACKAGES_CARDS } from "@/constants/landingStaticPackages";

const body = document.getElementsByTagName("body")[0];
const store = useStore();
const router = useRouter();
const route = useRoute();

const name = ref("");
const email = ref("");
const password = ref("");
const passwordConfirmation = ref("");
const documentId = ref("");
const phone = ref("");
const birthDate = ref("");
const sponsorReferralCode = ref("");
const sponsorValidated = ref(null);
const sponsorCheckLoading = ref(false);
const sponsorError = ref("");
const terms = ref(false);
const error = ref("");
const loading = ref(false);
const packagesList = ref([]);
const selectedPackageId = ref("");
const countryCode = ref("BO");
const paymentMethod = ref("transferencia");
const paymentOptions = REGISTRATION_PAYMENT_METHODS;
const staticPackages = STATIC_PACKAGES_CARDS;

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

function selectStaticPackage(card) {
  const found = packagesList.value.find((p) => p.slug === card.slug);
  if (found) {
    selectedPackageId.value = String(found.id);
  }
}

function isStaticPackageSelected(card) {
  const p = packagesList.value.find((x) => x.slug === card.slug);
  if (!p) return false;
  return selectedPackageId.value === String(p.id);
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
  () => route.query.sponsor,
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

async function signup() {
  error.value = "";
  if (!terms.value) {
    error.value = "Debe aceptar los términos y condiciones.";
    return;
  }
  if (password.value !== passwordConfirmation.value) {
    error.value = "Las contraseñas no coinciden.";
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
      document_id: documentId.value.trim(),
      phone: phone.value.trim(),
      birth_date: birthDate.value,
    };
    if (sponsorReferralCode.value.trim()) {
      payload.sponsor_referral_code = sponsorReferralCode.value.trim();
    }
    payload.country_code = countryCode.value;
    if (selectedPackageId.value) {
      payload.registration_package_id = parseInt(String(selectedPackageId.value), 10);
    }
    payload.preferred_payment_method = paymentMethod.value;
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
  <div>
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

    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-10 col-lg-11 mx-auto">
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <h6 class="text-dark font-weight-bolder mb-3 text-center">Paquetes destacados (misma oferta que la landing)</h6>
              <div class="row g-2">
                <div v-for="card in staticPackages" :key="card.slug" class="col-6 col-md-3">
                  <button
                    type="button"
                    class="btn w-100 btn-sm"
                    :class="isStaticPackageSelected(card) ? 'btn-success' : 'btn-outline-primary'"
                    @click="selectStaticPackage(card)"
                  >
                    <span class="d-block text-xs font-weight-bold">{{ card.name }}</span>
                    <span class="d-block text-xxs text-muted">{{ card.priceDisplay }}</span>
                  </button>
                </div>
              </div>
              <p class="text-xxs text-muted text-center mt-2 mb-0">
                Si el catálogo API aún no está cargado, elige el paquete en el desplegable inferior.
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-5 col-lg-6 col-md-8 mx-auto">
          <div class="card z-index-0">
            <div class="card-header text-center pt-4">
              <img
                src="@/assets/img/synkailogo2.png"
                alt="Logo"
                class="mb-2 mt-3"
                style="max-width: 180px; height: auto"
              />
              <h5 class="text-dark">Inscripción</h5>
            </div>
            <div class="card-body">
              <h6 class="text-sm text-muted mb-2">Paquete de inscripción y pago</h6>
              <div class="mb-3">
                <label class="form-label text-sm">Paquete (opcional; puedes comprarlo después)</label>
                <select v-model="selectedPackageId" class="form-select">
                  <option value="">— Elegir después —</option>
                  <option v-for="p in packagesList" :key="p.id" :value="String(p.id)">
                    {{ p.name }} — {{ p.pv_points }} PV — Bs. {{ p.price }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label text-sm">Forma de pago preferida</label>
                <select v-model="paymentMethod" class="form-select">
                  <option v-for="opt in paymentOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label text-sm">País</label>
                <select v-model="countryCode" class="form-select">
                  <option v-for="c in LATAM_COUNTRIES" :key="c.code" :value="c.code">
                    {{ c.flag }} {{ c.name }}
                  </option>
                </select>
              </div>

              <div class="mb-3 p-3 rounded bg-gray-100">
                <p class="text-xs font-weight-bold text-dark mb-2">Medios de pago (misma lista que la landing)</p>
                <div class="row g-1">
                  <div v-for="opt in paymentOptions" :key="opt.value" class="col-12">
                    <span class="text-xxs text-secondary"><i class="ni ni-check-bold text-success me-1"></i>{{ opt.label }}</span>
                  </div>
                </div>
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

              <form @submit.prevent="signup">
                <div class="mb-3">
                  <label class="form-label text-sm">Código de patrocinador (opcional)</label>
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
                  <label class="form-label text-sm">Nombre completo</label>
                  <argon-input v-model="name" id="name" type="text" placeholder="Nombre y apellidos" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">Correo electrónico</label>
                  <argon-input v-model="email" id="email" type="email" placeholder="correo@empresa.com" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">CI / NIT</label>
                  <argon-input v-model="documentId" id="doc" type="text" placeholder="Documento de identidad" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">Teléfono</label>
                  <argon-input v-model="phone" id="phone" type="text" placeholder="Celular o fijo" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">Fecha de nacimiento</label>
                  <argon-input v-model="birthDate" id="bd" type="date" placeholder="" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">Contraseña (mín. 8 caracteres)</label>
                  <argon-input v-model="password" id="password" type="password" placeholder="Contraseña" />
                </div>
                <div class="mb-3">
                  <label class="form-label text-sm">Confirmar contraseña</label>
                  <argon-input
                    v-model="passwordConfirmation"
                    id="password2"
                    type="password"
                    placeholder="Repite la contraseña"
                  />
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
                </div>

                <p v-if="error" class="text-danger mt-2 text-sm">{{ error }}</p>

                <div class="text-center">
                  <argon-button
                    fullWidth
                    color="dark"
                    variant="gradient"
                    class="my-4 mb-2"
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
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <app-footer />
  </div>
</template>
