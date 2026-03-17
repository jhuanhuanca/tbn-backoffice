<template>
  <div class="landing-personal">
    <!-- Barra superior: volver al backoffice -->
    <div class="landing-topbar py-2">
      <div class="container d-flex justify-content-end">
        <router-link to="/cuenta" class="btn btn-sm btn-outline-light">
          <i class="ni ni-bold-left me-1"></i>
          Volver a mi cuenta
        </router-link>
      </div>
    </div>

    <!-- Hero -->
    <section class="hero-landing position-relative overflow-hidden">
      <div class="hero-bg"></div>
      <div class="container position-relative py-5">
        <div class="row align-items-center py-5">
          <div class="col-lg-4 text-center mb-4 mb-lg-0">
            <div class="avatar-hero rounded-circle shadow-lg mx-auto d-inline-flex align-items-center justify-content-center bg-white text-primary">
              <img
                v-if="perfil.foto"
                :src="perfil.foto"
                alt="Foto"
                class="rounded-circle w-100 h-100"
                style="object-fit: cover;"
              />
              <span v-else class="display-4 fw-bold">{{ initials(perfil.nombre) }}</span>
            </div>
          </div>
          <div class="col-lg-8 text-center text-lg-start text-white">
            <h1 class="display-5 fw-bold mb-2">{{ perfil.nombre }}</h1>
            <p class="lead opacity-90 mb-3">{{ perfil.tagline || 'Emprendedor multinivel · Construyendo redes que transforman' }}</p>
            <p class="mb-4 opacity-85">{{ perfil.bio || 'Únete a mi equipo y descubre una forma diferente de generar ingresos.' }}</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
              <router-link to="/signup" class="btn btn-lg btn-white shadow">
                <i class="ni ni-bold-right me-2"></i>
                Únete ahora
              </router-link>
              <a href="#videos" class="btn btn-lg btn-outline-white border-2">
                <i class="ni ni-button-play me-2"></i>
                Ver videos
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Datos de contacto / Tarjeta -->
    <section class="py-5 bg-light-green">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
              <div class="card-body p-4 p-md-5">
                <h2 class="h4 fw-bold text-dark mb-4">
                  <i class="ni ni-single-02 text-primary me-2"></i>
                  Conóceme
                </h2>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-soft-primary">
                      <div class="icon-landing bg-gradient-primary text-white rounded-circle">
                        <i class="ni ni-email-83"></i>
                      </div>
                      <div>
                        <div class="text-xs text-uppercase text-muted fw-bold">Email</div>
                        <a :href="`mailto:${perfil.email}`" class="text-dark fw-semibold">{{ perfil.email }}</a>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-soft-primary">
                      <div class="icon-landing bg-gradient-primary text-white rounded-circle">
                        <i class="ni ni-mobile-button"></i>
                      </div>
                      <div>
                        <div class="text-xs text-uppercase text-muted fw-bold">Teléfono</div>
                        <a :href="`tel:${perfil.telefono}`" class="text-dark fw-semibold">{{ perfil.telefono || '—' }}</a>
                      </div>
                    </div>
                  </div>
                  <div class="col-12">
                    <p class="text-muted mb-0">{{ perfil.bio || 'Apasionado por el crecimiento personal y el trabajo en red. Te ayudo a alcanzar tus metas con un sistema probado.' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Videos de explicación -->
    <section id="videos" class="py-5">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="h3 fw-bold text-dark mb-2">Videos de explicación</h2>
          <p class="text-muted">Descubre cómo funciona el negocio y por qué deberías unirte</p>
        </div>
        <div class="row g-4">
          <div v-for="(video, i) in videos" :key="i" class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden video-card">
              <div class="video-thumb position-relative">
                <div class="thumb-placeholder bg-gradient-dark d-flex align-items-center justify-content-center">
                  <i class="ni ni-button-play display-4 text-white opacity-8"></i>
                </div>
                <span class="badge bg-gradient-primary position-absolute top-0 start-0 m-3">{{ video.duracion }}</span>
              </div>
              <div class="card-body">
                <h6 class="fw-bold text-dark mb-2">{{ video.titulo }}</h6>
                <p class="text-sm text-muted mb-0">{{ video.descripcion }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Beneficios / Por qué unirse -->
    <section class="py-5 bg-light-green">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="h3 fw-bold text-dark mb-2">¿Por qué unirte?</h2>
          <p class="text-muted">Ventajas de formar parte de mi red</p>
        </div>
        <div class="row g-4">
          <div v-for="(item, i) in beneficios" :key="i" class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 text-center p-4">
              <div class="icon-benefit bg-gradient-primary text-white rounded-circle mx-auto mb-3">
                <i :class="item.icon"></i>
              </div>
              <h6 class="fw-bold text-dark mb-2">{{ item.titulo }}</h6>
              <p class="text-sm text-muted mb-0">{{ item.texto }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA final -->
    <section class="py-5">
      <div class="container">
        <div class="card border-0 shadow-lg rounded-3 overflow-hidden cta-card">
          <div class="card-body p-5 text-center text-white">
            <h2 class="h3 fw-bold mb-3">¿Listo para empezar?</h2>
            <p class="opacity-90 mb-4">Únete a mi equipo y accede a formación, soporte y un plan de compensación claro.</p>
            <router-link to="/signup" class="btn btn-lg btn-white shadow">
              <i class="ni ni-bold-right me-2"></i>
              Crear mi cuenta gratis
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 footer-landing">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start text-muted text-sm">
            {{ perfil.nombre }} · Landing personal
          </div>
          <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
            <router-link to="/signin" class="text-muted text-sm me-3">Iniciar sesión</router-link>
            <router-link to="/" class="text-muted text-sm">Inicio</router-link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: "LandingPersonal",
  data() {
    return {
      perfil: {
        nombre: "Carlos Pérez",
        email: "carlos@correo.com",
        telefono: "+52 55 0000 0000",
        bio: "Emprendedor. Construyendo mi red multinivel.",
        tagline: "Emprendedor multinivel · Construyendo redes que transforman",
        foto: "",
      },
      videos: [
        { titulo: "Cómo funciona el negocio", descripcion: "Explicación del modelo y oportunidades.", duracion: "5 min" },
        { titulo: "Mi historia y resultados", descripcion: "De dónde vengo y qué he logrado.", duracion: "8 min" },
        { titulo: "Plan de compensación", descripcion: "Cómo se generan los ingresos.", duracion: "12 min" },
        { titulo: "Primeros pasos", descripcion: "Qué hacer al unirte al equipo.", duracion: "6 min" },
        { titulo: "Herramientas de marketing", descripcion: "Recursos para promocionar.", duracion: "10 min" },
        { titulo: "Testimonios del equipo", descripcion: "Experiencias de otros miembros.", duracion: "7 min" },
      ],
      beneficios: [
        { icon: "ni ni-money-coins", titulo: "Ingresos recurrentes", texto: "Plan de compensación diseñado para tu crecimiento." },
        { icon: "ni ni-istanbul", titulo: "Formación continua", texto: "Capacitación y materiales para que tengas éxito." },
        { icon: "ni ni-single-02", titulo: "Soporte personal", texto: "Te acompaño en tus primeros pasos." },
        { icon: "ni ni-world", titulo: "Flexibilidad", texto: "Trabaja desde donde quieras, cuando quieras." },
      ],
    };
  },
  created() {
    // Perfil guardado desde Mi cuenta al hacer clic en "Ver mi landing"
    try {
      const saved = sessionStorage.getItem("landingPerfil");
      if (saved) {
        const data = JSON.parse(saved);
        this.perfil = { ...this.perfil, ...data };
        return;
      }
    } catch (_) { /* sin perfil guardado */ }
    // Fallback: store
    try {
      const store = this.$store;
      if (store && store.state && store.state.user) {
        const u = store.state.user;
        this.perfil = {
          nombre: u.nombre || u.name || this.perfil.nombre,
          email: u.email || this.perfil.email,
          telefono: u.telefono || u.phone || this.perfil.telefono,
          bio: u.bio || this.perfil.bio,
          tagline: u.tagline || this.perfil.tagline,
          foto: u.foto || u.avatar || "",
        };
      }
    } catch (_) { /* store no disponible */ }
  },
  methods: {
    initials(nombre) {
      const parts = String(nombre || "").trim().split(/\s+/).filter(Boolean);
      const a = parts[0]?.[0] || "U";
      const b = parts[1]?.[0] || "";
      return `${a}${b}`.toUpperCase();
    },
  },
};
</script>

<style scoped>
.landing-personal {
  background: #fff;
}

.landing-topbar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
}

.hero-landing {
  min-height: 70vh;
  display: flex;
  align-items: center;
  padding-top: 3rem;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #54b144 0%, #222d25 100%);
}

.avatar-hero {
  width: 200px;
  height: 200px;
  border: 4px solid rgba(255, 255, 255, 0.3);
}

.icon-landing {
  width: 48px;
  height: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-benefit {
  width: 64px;
  height: 64px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.bg-soft-primary {
  background: rgba(84, 177, 68, 0.08);
}

.bg-light-green {
  background: rgba(84, 177, 68, 0.05);
}

.video-thumb .thumb-placeholder {
  aspect-ratio: 16/9;
  min-height: 180px;
}

.video-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12) !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cta-card .card-body {
  background: linear-gradient(135deg, #54b144 0%, #222d25 100%);
}

.btn-outline-white {
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.8);
  background: transparent;
}

.btn-outline-white:hover {
  color: #222d25;
  background: #fff;
  border-color: #fff;
}

.footer-landing {
  background: #222d25;
  color: rgba(255, 255, 255, 0.7);
}

.footer-landing a:hover {
  color: #54b144 !important;
}

.border-2 {
  border-width: 2px !important;
}
</style>
