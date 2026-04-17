<script setup>
import { computed, onMounted, ref } from "vue";
import { useStore } from "vuex";
import { fetchProfile, fetchMyLanding, updateMyLanding } from "@/services/me";

const store = useStore();

const loading = ref(true);
const saving = ref(false);
const error = ref("");
const ok = ref("");

const memberCode = ref("");

const form = ref({
  tagline: "",
  bio: "",
  phone: "",
  email: "",
  whatsapp: "",
  videos: [
    { titulo: "¿Qué es TBN?", descripcion: "Conoce el modelo y el plan.", duracion: "3:20" },
    { titulo: "Cómo ganar en el sistema", descripcion: "Unilevel + binario explicado.", duracion: "4:10" },
    { titulo: "Preguntas frecuentes", descripcion: "Dudas comunes de nuevos socios.", duracion: "2:45" },
  ],
});

const publicLink = computed(() => (memberCode.value ? `/p/${memberCode.value}` : ""));

function openPublic() {
  if (!publicLink.value) return;
  window.open(publicLink.value, "_blank", "noopener,noreferrer");
}

async function load() {
  loading.value = true;
  error.value = "";
  ok.value = "";
  try {
    const [u, l] = await Promise.all([fetchProfile(), fetchMyLanding()]);
    memberCode.value = u.member_code ? String(u.member_code) : "";
    const landing = l.landing || {};
    form.value = {
      ...form.value,
      tagline: landing.tagline || "",
      bio: landing.bio || "",
      phone: landing.phone || u.phone || "",
      email: landing.email || u.email || "",
      whatsapp: landing.whatsapp || "",
      videos: Array.isArray(landing.videos) && landing.videos.length ? landing.videos : form.value.videos,
    };
  } catch {
    error.value = "No se pudo cargar tu landing.";
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  error.value = "";
  ok.value = "";
  try {
    await updateMyLanding({ landing: form.value });
    ok.value = "Landing guardada correctamente.";
  } catch (e) {
    error.value = e?.response?.data?.message || "No se pudo guardar la landing.";
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  // Layout de landing: fuera del shell del dashboard
  store.state.hideConfigButton = true;
  store.state.showNavbar = false;
  store.state.showSidenav = false;
  store.state.showFooter = false;
  store.state.layout = "landing";
  document.body.classList.remove("bg-gray-100");
  await load();
});
</script>

<template>
  <div class="container py-4">
    <div class="card border-0 shadow mb-4">
      <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <h3 class="mb-1 text-dark font-weight-bolder">Editor de landing personal</h3>
          <p class="text-sm text-secondary mb-0">Edita tu página pública y compártela con tu equipo.</p>
          <p v-if="publicLink" class="text-xs text-secondary mb-0 mt-2">
            Link público: <code>{{ publicLink }}</code>
          </p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" :disabled="loading" @click="openPublic">
            Ver landing
          </button>
          <button type="button" class="btn btn-success" :disabled="loading || saving" @click="save">
            {{ saving ? "Guardando…" : "Guardar cambios" }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger text-white">{{ error }}</div>
    <div v-if="ok" class="alert alert-success text-white">{{ ok }}</div>

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Contenido</h6>
            <p class="text-xs text-secondary mb-0">Lo que verá el visitante en tu landing.</p>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label text-sm">Tagline</label>
              <input v-model.trim="form.tagline" class="form-control" type="text" placeholder="Ej: Emprendedor multinivel" />
            </div>
            <div class="mb-3">
              <label class="form-label text-sm">Bio</label>
              <textarea v-model.trim="form.bio" class="form-control" rows="4" placeholder="Cuenta quién eres y tu propuesta de valor." />
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-sm">Teléfono</label>
                <input v-model.trim="form.phone" class="form-control" type="text" placeholder="+591..." />
              </div>
              <div class="col-md-6">
                <label class="form-label text-sm">WhatsApp</label>
                <input v-model.trim="form.whatsapp" class="form-control" type="text" placeholder="+591..." />
              </div>
              <div class="col-12">
                <label class="form-label text-sm">Email de contacto</label>
                <input v-model.trim="form.email" class="form-control" type="email" placeholder="correo@empresa.com" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header pb-0">
            <h6 class="text-dark mb-0">Videos</h6>
            <p class="text-xs text-secondary mb-0">Lista de videos (título, descripción, duración).</p>
          </div>
          <div class="card-body">
            <div v-for="(v, idx) in form.videos" :key="idx" class="p-3 border rounded-3 mb-3">
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label text-xs mb-1">Título</label>
                  <input v-model.trim="v.titulo" class="form-control form-control-sm" type="text" />
                </div>
                <div class="col-12">
                  <label class="form-label text-xs mb-1">Descripción</label>
                  <input v-model.trim="v.descripcion" class="form-control form-control-sm" type="text" />
                </div>
                <div class="col-6">
                  <label class="form-label text-xs mb-1">Duración</label>
                  <input v-model.trim="v.duracion" class="form-control form-control-sm" type="text" placeholder="3:20" />
                </div>
              </div>
            </div>
            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              @click="form.videos.push({ titulo: 'Nuevo video', descripcion: '', duracion: '0:00' })"
            >
              Añadir video
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

