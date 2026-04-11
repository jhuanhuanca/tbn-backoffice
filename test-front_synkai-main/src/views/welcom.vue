<script setup>
import {
  ref,
  onMounted,
  onUnmounted,
  onBeforeMount,
  onBeforeUnmount,
  computed,
} from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import ArgonButton from "@/components/ArgonButton.vue";
import logo from "@/assets/img/synkailogo2.png";
import { fetchPackages } from "@/services/me";
import { REGISTRATION_PAYMENT_METHODS } from "@/constants/registrationPayments";

const store = useStore();
const router = useRouter();
const body = document.getElementsByTagName("body")[0];

const isScrolled = ref(false);
const currentSection = ref("inicio");
const sectionIds = ["inicio", "como-funciona", "plan-compensacion", "productos", "faq"];

const packages = ref([]);
const paymentMethods = REGISTRATION_PAYMENT_METHODS;

async function loadPackages() {
  try {
    const res = await fetchPackages();
    packages.value = res.data || [];
  } catch {
    packages.value = [];
  }
}

const navbarClasses = computed(() => {
  return [
    "navbar navbar-expand-lg border-radius-0 top-0 z-index-3 py-2 bg-white shadow-sm navbar-light navbar-fixed",
  ];
});

function handleScroll() {
  isScrolled.value = window.scrollY > 50;
  const offset = 140;
  const scrollPoint = window.scrollY + offset;

  for (const id of sectionIds) {
    const section = document.getElementById(id);
    if (!section) continue;

    const top = section.offsetTop;
    const bottom = top + section.offsetHeight;
    if (scrollPoint >= top && scrollPoint < bottom) {
      currentSection.value = id;
      break;
    }
  }
}

const navLinkClass = (id) => [
  "nav-link nav-link-icon me-2",
  { active: currentSection.value === id },
];

const goSignin = () => {
  router.push("/signin");
};

const goSignup = () => {
  router.push("/signup");
};

const scrollToHowItWorks = () => {
  const el = document.getElementById("como-funciona");
  if (el) {
    el.scrollIntoView({ behavior: "smooth" });
  }
};

onBeforeMount(() => {
  // Oculta el layout del dashboard para que la landing sea totalmente independiente
  store.state.hideConfigButton = true;
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  store.state.layout = "landing";
  body.classList.remove("bg-gray-100");
});

onMounted(() => {
  loadPackages();
  window.addEventListener("scroll", handleScroll, { passive: true });
  handleScroll();
});

onBeforeUnmount(() => {
  // Restaura el layout del dashboard
  store.state.hideConfigButton = false;
  store.state.showNavbar = true;
  store.state.showSidenav = true;
  store.state.showFooter = true;
  store.state.layout = "default";
  body.classList.add("bg-gray-100");
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
  <div class="landing-page">
    <!-- Navbar -->
    <nav :class="navbarClasses">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#inicio">
          <img :src="logo" alt="TBN LIVING" class="navbar-logo me-2" />
          <span class="font-weight-bolder">TBN LIVING</span>
        </a>
        <button
          class="navbar-toggler shadow-none ms-2"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navigation"
          aria-controls="navigation"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon mt-2">
            <span class="navbar-toggler-bar bar1"></span>
            <span class="navbar-toggler-bar bar2"></span>
            <span class="navbar-toggler-bar bar3"></span>
          </span>
        </button>
        <div class="collapse navbar-collapse" id="navigation">
            <ul class="navbar-nav mx-auto">
            <li class="nav-item">
              <a :class="navLinkClass('inicio')" href="#inicio">Inicio</a>
            </li>
            <li class="nav-item">
              <a :class="navLinkClass('como-funciona')" href="#como-funciona">Cómo funciona</a>
            </li>
            <li class="nav-item">
              <a :class="navLinkClass('plan-compensacion')" href="#plan-compensacion"
                >Plan de compensación</a
              >
            </li>
            <li class="nav-item">
              <a :class="navLinkClass('productos')" href="#productos">Productos</a>
            </li>
            <li class="nav-item">
              <a :class="navLinkClass('faq')" href="#faq">FAQ</a>
            </li>
          </ul>
          <ul class="navbar-nav d-lg-flex align-items-lg-center">
            <li class="nav-item me-2">
              <button
                type="button"
                class="btn btn-sm bg-white text-dark mb-0 shadow-none"
                @click="goSignin"
              >
                Iniciar sesión
              </button>
            </li>
            <li class="nav-item">
              <argon-button color="primary" variant="gradient" size="sm" @click="goSignup">
                Registrarse
              </argon-button>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="pt-7 pb-6 position-relative bg-gradient-success">
      <div
        class="position-absolute w-100 h-100 top-0 start-0 bg-gradient-success opacity-9"
      ></div>
      <div class="container position-relative z-index-2">
        <div class="row align-items-center">
          <div class="col-lg-7 text-white">
            <h1 class="text-white mb-3 display-4 font-weight-bolder">
              Construye tu Negocio
              <span class="text-gradient text-warning">Viviendo Tiempos de Bienestar Natural</span>
               con TBN LIVING
            </h1>
            <p class="lead text-white opacity-9 mb-4">
              Somos una compañia especializada en la creacion de formulas unica,
              preparadas a base de aceites esenciales y vegetales premium,
              que proporciona una alternativa natural y eficaz para el tratamiento y la preveencion de transtornos en la salud.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <argon-button color="warning" variant="gradient" size="md" @click="goSignup">
                Crear cuenta
              </argon-button>
              <argon-button
                color="light"
                variant="outline"
                size="md"
                @click="scrollToHowItWorks"
              >
                Ver cómo funciona
              </argon-button>
            </div>
            <div class="mt-4 d-flex align-items-center">
              <div class="me-3">
                <span class="badge bg-gradient-success">Sin costo de mantenimiento oculto</span>
              </div>
              <p class="mb-0 text-sm text-white-50">
                Pagos semanales · Panel en tiempo real · Soporte global
              </p>
            </div>
          </div>
          <div class="col-lg-5 mt-5 mt-lg-0">
            <div class="card shadow-lg border-0">
              <div class="card-body p-4">
                <h6 class="text-success text-uppercase text-xs font-weight-bolder mb-3">
                  Panel binario híbrido
                </h6>
                <p class="text-sm text-secondary mb-3">
                  Visualiza tu red, volúmenes por pierna y ganancias en un solo lugar.
                </p>
                <div class="row">
                  <div class="col-6 mb-3">
                    <p class="text-xs text-secondary mb-1">Volumen pierna izquierda</p>
                    <h6 class="mb-0 font-weight-bolder">12,450 PV</h6>
                  </div>
                  <div class="col-6 mb-3">
                    <p class="text-xs text-secondary mb-1">Volumen pierna derecha</p>
                    <h6 class="mb-0 font-weight-bolder">10,980 PV</h6>
                  </div>
                  <div class="col-6 mb-3">
                    <p class="text-xs text-secondary mb-1">Bono binario (estimado)</p>
                    <h6 class="mb-0 font-weight-bolder text-success">$1,240.00</h6>
                  </div>
                  <div class="col-6 mb-3">
                    <p class="text-xs text-secondary mb-1">Equipo directo</p>
                    <h6 class="mb-0 font-weight-bolder">38 afiliados</h6>
                  </div>
                </div>
                <hr class="horizontal dark my-3" />
                <p class="text-xs text-secondary mb-0">
                  <i class="ni ni-check-bold text-success me-1"></i>
                  Cumple con estándares de transparencia y trazabilidad de comisiones.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Cómo funciona -->
    <section id="como-funciona" class="py-6 bg-gray-100">
      <div class="container">
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <h3 class="font-weight-bolder text-dark mb-2">Cómo funciona el negocio</h3>
            <p class="text-secondary text-sm">
              En solo cuatro pasos puedes activar tu negocio, construir tu red binaria y comenzar a
              generar ingresos residuales.
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <div
                  class="icon icon-shape bg-gradient-success shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-single-02 text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">1. Registrarse</h6>
                <p class="text-xs text-secondary mb-0">
                  Crea tu cuenta en minutos, verifica tus datos y accede al panel de afiliado.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <div
                  class="icon icon-shape bg-gradient-success shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-box-2 text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">2. Activar paquete</h6>
                <p class="text-xs text-secondary mb-0">
                  Elige el paquete que mejor se adapte a tus objetivos y activa tu posición binaria.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <div
                  class="icon icon-shape bg-gradient-info shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-send text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">3. Invitar afiliados</h6>
                <p class="text-xs text-secondary mb-0">
                  Comparte tu enlace, posiciona nuevos socios en tu pierna izquierda o derecha y
                  construye tu red.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body text-center">
                <div
                  class="icon icon-shape bg-gradient-warning shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-money-coins text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">4. Generar ingresos</h6>
                <p class="text-xs text-secondary mb-0">
                  Cobra bonos directos, binarios y de liderazgo cada vez que tu red consume o
                  recomienda productos.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Plan de compensación -->
    <section id="plan-compensacion" class="py-6">
      <div class="container">
        <div class="row mb-5 align-items-center">
          <div class="col-lg-6">
            <h3 class="font-weight-bolder text-dark mb-3">Plan de compensación binario híbrido</h3>
            <p class="text-sm text-secondary mb-3">
             ¡Hoy las reglas del juego han cambiado y esta es la unica forma de generar 
             ingresos de manera eficiente y sostenible en el tiempo.
            </p>
            <div class="row">
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-credit-card text-primary me-2"></i>Venta Directa
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Gana un porcentaje inmediato por cada venta directa que realice.
                </p>
              </div>
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-chart-bar-32 text-success me-2"></i>Bono de Inicio Rapido
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Ganas un aproximado del 42% en tus tres primeras lineas.
                </p>
              </div>
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-world-2 text-info me-2"></i>Bono por equipo
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Genera ingresos residuales sobre el consumo mensual de tu organización.
                </p>
              </div>
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-world-2 text-info me-2"></i>Bono Residual
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Genera ingresos residuales sobre el consumo mensual de tu organización en 12 niveles.
                </p>
              </div>
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-diamond text-warning me-2"></i>Bono de liderazgo
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Cobras por ayudar a tus líderes a alcanzar y mantener rangos dentro de la red.
                </p>
              </div>
              <div class="col-sm-6 mb-3">
                <h6 class="text-sm font-weight-bolder">
                  <i class="ni ni-diamond text-warning me-2"></i>Premios especiales
                </h6>
                <p class="text-xs text-secondary mb-0">
                  Vehiculos 0K, cruceros de lujo y muchos viajes.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-6 mt-5 mt-lg-0">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <h6 class="text-sm font-weight-bolder mb-3">
                  Diagrama binario (ejemplo visual)
                </h6>
                <div class="binary-diagram d-flex flex-column align-items-center">
                  <div class="binary-node root">
                    <span class="badge bg-gradient-success">Tú</span>
                  </div>
                  <div class="binary-connector"></div>
                  <div class="binary-level d-flex justify-content-between w-100 px-5">
                    <div class="text-center">
                      <div class="binary-node">
                        <span class="badge bg-gradient-success">Izquierda</span>
                      </div>
                      <p class="text-xxs text-secondary mt-1 mb-0">Pierna A</p>
                    </div>
                    <div class="text-center">
                      <div class="binary-node">
                        <span class="badge bg-gradient-info">Derecha</span>
                      </div>
                      <p class="text-xxs text-secondary mt-1 mb-0">Pierna B</p>
                    </div>
                  </div>
                  <div class="binary-connector small"></div>
                  <div class="binary-level d-flex justify-content-between w-100 px-3">
                    <div class="text-center">
                      <div class="binary-node small">
                        <span class="badge bg-light text-dark">Equipo A1</span>
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="binary-node small">
                        <span class="badge bg-light text-dark">Equipo A2</span>
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="binary-node small">
                        <span class="badge bg-light text-dark">Equipo B1</span>
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="binary-node small">
                        <span class="badge bg-light text-dark">Equipo B2</span>
                      </div>
                    </div>
                  </div>
                  <p class="text-xxs text-secondary mt-3 mb-0 text-center">
                    Los bonos binarios se calculan sobre el volumen matcheado entre las dos piernas.
                    El plan híbrido agrega bonos unilevel por profundidad.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Beneficios del negocio -->
    <section class="py-6 bg-gray-100">
      <div class="container">
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <h3 class="font-weight-bolder text-dark mb-2">Beneficios de nuestro negocio</h3>
            <p class="text-secondary text-sm">
              Una estructura binaria híbrida diseñada para la era digital, con automatización,
              transparencia y soporte constante.
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center">
              <div class="card-body">
                <div
                  class="icon icon-shape bg-gradient-success shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-money-coins text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">Ingresos residuales</h6>
                <p class="text-xs text-secondary mb-0">
                  Gana mes a mes sobre el consumo recurrente de tu organización global.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center">
              <div class="card-body">
                <div
                  class="icon icon-shape bg-gradient-success shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-laptop text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">Negocio digital</h6>
                <p class="text-xs text-secondary mb-0">
                  Gestiona todo desde tu panel: registros, compras, reportes y equipo.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center">
              <div class="card-body">
                <div
                  class="icon icon-shape bg-gradient-info shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-settings text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">Plataforma automatizada</h6>
                <p class="text-xs text-secondary mb-0">
                  Distribución de comisiones, reportes y alertas automatizadas en tiempo real.
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center">
              <div class="card-body">
                <div
                  class="icon icon-shape bg-gradient-dark shadow text-center rounded-circle mb-3"
                >
                  <i class="ni ni-world-2 text-white" aria-hidden="true"></i>
                </div>
                <h6 class="font-weight-bolder mb-2">Soporte global</h6>
                <p class="text-xs text-secondary mb-0">
                  Herramientas multilingües, soporte en línea y material de entrenamiento para tu
                  equipo internacional.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Productos / paquetes -->
    <section id="productos" class="py-6">
      <div class="container">
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <h3 class="font-weight-bolder text-dark mb-2">Paquetes de Activacion</h3>
            <p class="text-secondary text-sm">
              Ofrecemos diferentes paquetes para que cada afiliado pueda iniciar según su
              presupuesto y metas.
            </p>
          </div>
        </div>

        <h5 class="text-center text-dark font-weight-bolder mb-4">Paquetes destacados</h5>

        <h5 class="text-center text-dark font-weight-bolder mb-4 mt-4">Catálogo en línea </h5>
        <div class="row">
          <div v-if="packages.length === 0" class="col-12 text-center text-secondary text-sm py-4">
            Cargando paquetes desde el servidor…
          </div>
          <div
            v-for="(p, idx) in packages"
            :key="p.id"
            class="col-lg-4 col-md-6 mb-4"
          >
            <div class="card h-100 border-0 shadow-sm" :class="{ 'position-relative': idx === 1 }">
              <span
                v-if="idx === 1"
                class="badge bg-gradient-success position-absolute top-0 end-0 m-3"
              >
                Recomendado
              </span>
              <div class="card-body p-4">
                <h6 class="font-weight-bolder mb-1">{{ p.name }}</h6>
                <p class="text-xs text-secondary mb-3">
                  Paquete de activación · datos desde la base de datos.
                </p>
                <h5 class="mb-1 text-primary font-weight-bolder">Bs. {{ p.price }}</h5>
                <p class="text-xs text-secondary mb-3">{{ p.pv_points }} PV</p>
                <ul class="list-unstyled mb-4 text-xs text-secondary">
                  <li><i class="ni ni-check-bold text-success me-1"></i>Panel de afiliado</li>
                  <li><i class="ni ni-check-bold text-success me-1"></i>BIR 21% / 15% / 6% (3 líneas)</li>
                  <li><i class="ni ni-check-bold text-success me-1"></i>Binario y residual según reglas</li>
                </ul>
                <argon-button color="primary" variant="gradient" full-width size="sm">
                  <router-link
                    :to="{ path: '/signup', query: { package: String(p.id) } }"
                    class="nav-link text-white"
                  >
                    Inscribirme con este paquete
                  </router-link>
                </argon-button>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-5">
          <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm bg-gray-100">
              <div class="card-body p-4">
                <h5 class="font-weight-bolder text-dark mb-3 text-center">Sistema de pago en inscripción</h5>
                <p class="text-sm text-secondary text-center mb-4">
                  Puedes indicar tu preferencia al registrarte; el equipo validará la activación según tu país.
                </p>
                <div class="row g-3">
                  <div
                    v-for="pm in paymentMethods"
                    :key="pm.value"
                    class="col-md-6 col-lg-4"
                  >
                    <div class="d-flex align-items-center p-3 rounded-3 bg-white border shadow-sm h-100">
                      <i class="ni ni-money-coins text-success text-lg me-3"></i>
                      <span class="text-sm font-weight-bold text-dark">{{ pm.label }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Estadísticas y video -->
    <section class="py-6 bg-dark position-relative overflow-hidden">
      <div class="position-absolute top-0 start-0 w-100 h-100 opacity-3 bg-gradient-primary"></div>
      <div class="container position-relative z-index-2">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-5 mb-lg-0">
            <h3 class="text-white mb-3 font-weight-bolder">Confianza respaldada por resultados</h3>
            <p class="text-sm text-white opacity-8 mb-4">
              Nuestra infraestructura ha procesado miles de pagos y soporta equipos en múltiples
              países, con una plataforma escalable y segura.
            </p>
            <div class="row">
              <div class="col-6 mb-3">
                <h4 class="text-white mb-0 font-weight-bolder">+18,000</h4>
                <p class="text-xs text-white opacity-8 mb-0">Afiliados activos</p>
              </div>
              <div class="col-6 mb-3">
                <h4 class="text-white mb-0 font-weight-bolder">+10</h4>
                <p class="text-xs text-white opacity-8 mb-0">Países</p>
              </div>
              <div class="col-6 mb-3">
                <h4 class="text-white mb-0 font-weight-bolder">+2M</h4>
                <p class="text-xs text-white opacity-8 mb-0">Pagos realizados</p>
              </div>
              <div class="col-6 mb-3">
                <h4 class="text-white mb-0 font-weight-bolder">8+</h4>
                <p class="text-xs text-white opacity-8 mb-0">Años de experiencia</p>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card border-0 shadow-lg">
              <div
                class="position-relative"
                style="padding-top: 56.25%; background-image: linear-gradient(310deg, #5e72e4, #825ee4);"
              >
                <div
                  class="position-absolute top-50 start-50 translate-middle text-center text-white"
                >
                  <div
                    class="icon icon-shape bg-white text-center rounded-circle shadow mx-auto mb-3"
                  >
                    <i class="ni ni-button-play text-success" aria-hidden="true"></i>
                  </div>
                  <h6 class="mb-1 font-weight-bolder">Video de presentación</h6>
                  <p class="text-xs text-white opacity-8 mb-0">
                    Aquí irá tu video explicando el negocio y la plataforma.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-6 bg-gray-100">
      <div class="container">
        <div class="row mb-5 text-center">
          <div class="col-lg-8 mx-auto">
            <h3 class="font-weight-bolder text-dark mb-2">Preguntas frecuentes</h3>
            <p class="text-secondary text-sm">
              Resolvemos las dudas más comunes de nuevos afiliados. Puedes ampliar esta sección con
              tu contenido específico.
            </p>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
              <div class="accordion-item mb-3 border-radius-lg">
                <h6 class="accordion-header" id="faq1">
                  <button
                    class="accordion-button border-bottom font-weight-bolder text-sm"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq1Collapse"
                    aria-expanded="true"
                    aria-controls="faq1Collapse"
                  >
                    ¿Qué es un plan binario híbrido?
                  </button>
                </h6>
                <div
                  id="faq1Collapse"
                  class="accordion-collapse collapse show"
                  aria-labelledby="faq1"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body text-xs text-secondary">
                    Es una combinación entre un plan binario tradicional (dos piernas) y un plan
                    unilevel, que permite ganar tanto por el volumen matcheado entre piernas como
                    por la profundidad de tu organización.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3 border-radius-lg">
                <h6 class="accordion-header" id="faq2">
                  <button
                    class="accordion-button border-bottom font-weight-bolder text-sm collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq2Collapse"
                    aria-expanded="false"
                    aria-controls="faq2Collapse"
                  >
                    ¿Necesito experiencia previa en MLM?
                  </button>
                </h6>
                <div
                  id="faq2Collapse"
                  class="accordion-collapse collapse"
                  aria-labelledby="faq2"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body text-xs text-secondary">
                    No. Tendrás acceso a entrenamientos, material de duplicación y soporte para que
                    puedas comenzar desde cero y aprender paso a paso.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3 border-radius-lg">
                <h6 class="accordion-header" id="faq3">
                  <button
                    class="accordion-button border-bottom font-weight-bolder text-sm collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq3Collapse"
                    aria-expanded="false"
                    aria-controls="faq3Collapse"
                  >
                    ¿Cada cuánto se pagan las comisiones?
                  </button>
                </h6>
                <div
                  id="faq3Collapse"
                  class="accordion-collapse collapse"
                  aria-labelledby="faq3"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body text-xs text-secondary">
                    Dependiendo de tu plan, puedes recibir pagos semanales y mensuales. Toda la
                    información se muestra en tu panel de afiliado.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3 border-radius-lg">
                <h6 class="accordion-header" id="faq4">
                  <button
                    class="accordion-button border-bottom font-weight-bolder text-sm collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq4Collapse"
                    aria-expanded="false"
                    aria-controls="faq4Collapse"
                  >
                    ¿Puedo construir el negocio desde cualquier país?
                  </button>
                </h6>
                <div
                  id="faq4Collapse"
                  class="accordion-collapse collapse"
                  aria-labelledby="faq4"
                  data-bs-parent="#faqAccordion"
                >
                  <div class="accordion-body text-xs text-secondary">
                    Sí, la plataforma está preparada para operar en múltiples países. Revisa las
                    condiciones específicas y métodos de pago disponibles en tu región.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA final -->
    <section class="py-6">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="card bg-gradient-success border-0 shadow-lg">
              <div class="card-body p-4 p-lg-5 d-lg-flex align-items-center justify-content-between">
                <div class="mb-3 mb-lg-0">
                  <h3 class="text-white mb-2 font-weight-bolder">
                    Únete ahora y comienza tu negocio binario híbrido
                  </h3>
                  <p class="text-sm text-white opacity-9 mb-0">
                    Activa tu posición hoy, genera tus primeros bonos y empieza a construir un
                    ingreso residual sólido.
                  </p>
                </div>
                <div class="text-lg-end">
                  <argon-button color="white" variant="fill" size="lg" @click="goSignup">
                    <span class="text-success font-weight-bolder">
                      Únete ahora y comienza tu negocio
                    </span>
                  </argon-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer pt-5 pb-4 bg-gray-100 border-top">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 mb-4 mb-lg-0">
            <h6 class="font-weight-bolder mb-3">TBN-LIVING</h6>
            <p class="text-xs text-secondary mb-3">
              Plataforma profesional para empresas y líderes de marketing multinivel con plan
              binario híbrido.
            </p>
            <div class="d-flex gap-2">
              <a href="javascript:;" class="btn btn-icon-only btn-facebook btn-sm mb-0">
                <i class="fab fa-facebook" aria-hidden="true"></i>
              </a>
              <a href="javascript:;" class="btn btn-icon-only btn-instagram btn-sm mb-0">
                <i class="fab fa-instagram" aria-hidden="true"></i>
              </a>
              <a href="javascript:;" class="btn btn-icon-only btn-twitter btn-sm mb-0">
                <i class="fab fa-twitter" aria-hidden="true"></i>
              </a>
              <a href="javascript:;" class="btn btn-icon-only btn-linkedin btn-sm mb-0">
                <i class="fab fa-linkedin" aria-hidden="true"></i>
              </a>
            </div>
          </div>
          <div class="col-lg-4 mb-4 mb-lg-0">
            <h6 class="font-weight-bolder mb-3">Enlaces rápidos</h6>
            <ul class="list-unstyled text-xs text-secondary mb-0">
              <li class="mb-2"><a href="#inicio" class="text-secondary">Inicio</a></li>
              <li class="mb-2">
                <a href="#como-funciona" class="text-secondary">Cómo funciona</a>
              </li>
              <li class="mb-2">
                <a href="#plan-compensacion" class="text-secondary">Plan de compensación</a>
              </li>
              <li class="mb-2"><a href="#productos" class="text-secondary">Productos</a></li>
              <li class="mb-2"><a href="#faq" class="text-secondary">FAQ</a></li>
            </ul>
          </div>
          <div class="col-lg-4">
            <h6 class="font-weight-bolder mb-3">Contacto</h6>
            <p class="text-xs text-secondary mb-1">
              <i class="ni ni-email-83 text-success me-1"></i> soporte@tunegociomlm.com
            </p>
            <p class="text-xs text-secondary mb-1">
              <i class="ni ni-mobile-button text-success me-1"></i> +00 000 000 000
            </p>
            <p class="text-xs text-secondary mb-3">
              <i class="ni ni-pin-3 text-success me-1"></i> Operamos globalmente desde tu país
            </p>
          </div>
        </div>
        <hr class="horizontal dark my-3" />
        <div class="row">
          <div class="col-12 text-center">
            <p class="text-xs text-secondary mb-0">
              © {{ new Date().getFullYear() }} MLM Binario Híbrido. Todos los derechos reservados.
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.landing-page {
  background-color: #ffffff;
  overflow-x: hidden;
  max-width: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  padding-top: 68px;
}
.landing-page .container {
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.binary-diagram {
  min-height: 220px;
}

.binary-node {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.binary-node.root .badge {
  padding: 0.4rem 1.2rem;
  font-size: 0.75rem;
}

.binary-node.small .badge {
  padding: 0.25rem 0.6rem;
  font-size: 0.65rem;
}

.binary-connector {
  width: 2px;
  height: 22px;
  background: linear-gradient(180deg, #5e72e4 0%, #e9ecef 100%);
  margin: 0.25rem auto;
}

.binary-connector.small {
  height: 16px;
}

.text-xxs {
  font-size: 0.65rem;
}

.navbar-logo {
  height: 32px;
}

.navbar-fixed {
  position: fixed;
  left: 0;
  width: 100%;
  top: 0;
  z-index: 1040;
}

.navbar .nav-link.active {
  color: #2dce89 !important;
  font-weight: 700;
}
</style>

