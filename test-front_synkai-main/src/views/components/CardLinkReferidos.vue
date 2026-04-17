<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div
            class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3"
          >
            <div>
              <h2 class="mb-2 text-dark font-weight-bolder">Enlace de referidos</h2>
              <p class="mb-0 text-sm text-muted">
                Comparte tu enlace. Quien se registre desde él quedará vinculado a tu código de patrocinador.
              </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" @click="copiarLink">
                <i class="ni ni-single-copy-04 me-2"></i>
                Copiar enlace
              </button>
              <button
                type="button"
                class="btn btn-sm btn-primary shadow-sm"
                :disabled="loading"
                @click="cargar"
              >
                <i class="ni ni-refresh-02 me-2"></i>
                Actualizar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-warning text-white mb-3">{{ error }}</div>

    <div class="row">
      <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0">
            <h6 class="mb-1 text-dark">Tu enlace personal</h6>
            <p class="mb-0 text-xs text-muted">
              Formato: <code>/i/TUCODIGO</code> redirige al formulario de inscripción con el patrocinador cargado.
            </p>
          </div>
          <div class="card-body pt-3">
            <div class="ref-link-wrapper">
              <div class="text-xs text-muted mb-1">URL completa</div>
              <div class="input-group">
                <input ref="inputLink" type="text" class="form-control" :value="linkCompleto" readonly />
                <button class="btn btn-primary mb-0" type="button" @click="copiarLink">
                  <i class="ni ni-copy me-1"></i>
                  Copiar
                </button>
              </div>
              <div class="text-xs text-muted mb-1 mt-3">URL cliente preferente</div>
              <div class="input-group">
                <input ref="inputLinkPref" type="text" class="form-control" :value="linkPreferenteCompleto" readonly />
                <button class="btn btn-outline-success mb-0" type="button" @click="copiarLinkPreferente">
                  <i class="ni ni-copy me-1"></i>
                  Copiar
                </button>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge bg-light text-primary text-xxs">
                  Tu código: <strong>{{ miCodigo || "—" }}</strong>
                </span>
                <span class="badge bg-light text-success text-xxs">
                  Referidos: <strong>{{ resumen.total }}</strong>
                </span>
                <span class="badge bg-light text-warning text-xxs">
                  Pendientes: <strong>{{ resumen.pendientes }}</strong>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1 text-dark">Referidos directos</h6>
              <p class="mb-0 text-xs text-muted">Socios que te tienen como patrocinador.</p>
            </div>
          </div>
          <div class="card-body pt-3">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                      Socio
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                      Código
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                      Alta
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                      Estado
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in items" :key="r.id">
                    <td>
                      <div class="d-flex flex-column px-2 py-1">
                        <span class="text-sm font-weight-bold">{{ r.name }}</span>
                        <span class="text-xs text-muted">{{ r.email }}</span>
                      </div>
                    </td>
                    <td class="align-middle text-center text-sm">{{ r.referral_code }}</td>
                    <td class="align-middle text-center text-xs text-muted">
                      {{ formatFecha(r.created_at) }}
                    </td>
                    <td class="align-middle text-end">
                      <span class="badge badge-sm text-xxs" :class="badgeEstado(r.account_status)">
                        {{ r.account_status }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="!loading && items.length === 0">
                    <td colspan="4" class="text-center text-sm text-muted py-4">
                      Aún no tienes referidos directos.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0">
            <h6 class="mb-1 text-dark">Cómo invitar</h6>
          </div>
          <div class="card-body pt-3 text-sm text-muted">
            <ol class="ps-3 mb-0">
              <li class="mb-2">Copia tu enlace y envíalo por WhatsApp, correo o redes.</li>
              <li class="mb-2">El prospecto abrirá el formulario de inscripción con tu código.</li>
              <li>Tras registrarse, aparecerá en la tabla de referidos directos.</li>
            </ol>
            <router-link
              v-if="miCodigo"
              :to="{ name: 'Signup', query: { sponsor: miCodigo } }"
              class="btn btn-outline-success btn-sm mt-3 w-100"
            >
              Ver formulario de registro
            </router-link>
            <span v-else class="btn btn-outline-secondary btn-sm mt-3 w-100 disabled opacity-50">
              Sin código de socio
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import router from "@/router";
import { fetchReferrals } from "@/services/me";

export default {
  name: "CardLinkReferidos",
  data() {
    return {
      loading: false,
      error: null,
      items: [],
      resumen: { total: 0, pendientes: 0, activos: 0 },
    };
  },
  computed: {
    ...mapState("auth", ["user"]),
    miCodigo() {
      return this.user?.referral_code || "";
    },
    linkCompleto() {
      const code = this.miCodigo;
      if (!code) {
        return "";
      }
      // Robusto en dev/prod (baseUrl): construye path directo.
      const href = router.resolve({ path: `/i/${code}` }).href;
      return href.startsWith("http") ? href : `${window.location.origin}${href}`;
    },
    linkPreferenteCompleto() {
      const code = this.miCodigo;
      if (!code) return "";
      const href = router.resolve({ path: `/ref-pref/${code}` }).href;
      return href.startsWith("http") ? href : `${window.location.origin}${href}`;
    },
  },
  mounted() {
    this.cargar();
  },
  methods: {
    async cargar() {
      if (!localStorage.getItem("token")) {
        this.error = "Inicia sesión para ver tu red.";
        return;
      }
      this.loading = true;
      this.error = null;
      try {
        const data = await fetchReferrals();
        this.items = data.items || [];
        this.resumen = data.summary || { total: 0, pendientes: 0, activos: 0 };
      } catch {
        this.error = "No se pudieron cargar los referidos.";
        this.items = [];
      } finally {
        this.loading = false;
      }
    },
    formatFecha(iso) {
      if (!iso) {
        return "—";
      }
      try {
        return new Date(iso).toLocaleDateString("es-BO");
      } catch {
        return iso;
      }
    },
    badgeEstado(s) {
      const e = String(s || "").toLowerCase();
      if (e === "active" || e === "activo") {
        return "bg-gradient-success text-white";
      }
      if (e === "pending" || e === "pendiente") {
        return "bg-gradient-warning text-white";
      }
      if (e === "inactive" || e === "inactivo") {
        return "bg-secondary text-white";
      }
      return "bg-light text-dark";
    },
    copiarLink() {
      const text = this.linkCompleto;
      if (!text) {
        return;
      }
      if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text);
      } else {
        const el = this.$refs.inputLink;
        if (el) {
          el.select();
          document.execCommand("copy");
        }
      }
    },
    copiarLinkPreferente() {
      const text = this.linkPreferenteCompleto;
      if (!text) return;
      if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(text);
      } else {
        const el = this.$refs.inputLinkPref;
        if (el) {
          el.select();
          document.execCommand("copy");
        }
      }
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}
.ref-link-wrapper {
  border-radius: 1rem;
  border: 1px solid #e9ecef;
  padding: 1rem;
  background: #fff;
}
.text-xxs {
  font-size: 0.65rem;
}
.bg-gradient-primary {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}
.bg-gradient-success {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}
.bg-gradient-warning {
  background: linear-gradient(87deg, #c9a227 0, #b3891e 100%) !important;
}
</style>
