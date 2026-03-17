<template>
  <div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
              <h2 class="mb-2 text-dark font-weight-bolder">Red de referidos</h2>
              <p class="mb-0 text-sm text-muted">
                Comparte tu enlace, haz seguimiento de tus referidos e inscribe nuevos socios directamente desde este panel.
              </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" @click="copiarLink">
                <i class="ni ni-single-copy-04 me-2"></i>
                Copiar enlace
              </button>
              <button type="button" class="btn btn-sm btn-primary shadow-sm" @click="refrescarDatos">
                <i class="ni ni-refresh-02 me-2"></i>
                Actualizar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Layout principal -->
    <div class="row">
      <!-- Link de referidos + lista -->
      <div class="col-lg-7 mb-4">
        <!-- Link de referidos -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1 text-dark">Tu enlace de referidos</h6>
              <p class="mb-0 text-xs text-muted">
                Envía este enlace a tus prospectos. Cuando se registren desde aquí, quedarán vinculados a tu red.
              </p>
            </div>
            <span class="badge bg-gradient-info text-white text-xxs">
              Compartible
            </span>
          </div>
          <div class="card-body pt-3">
            <div class="ref-link-wrapper">
              <div class="text-xs text-muted mb-1">Enlace personal</div>
              <div class="input-group">
                <input
                  ref="inputLink"
                  type="text"
                  class="form-control"
                  :value="linkReferido"
                  readonly
                />
                <button class="btn btn-primary mb-0" type="button" @click="copiarLink">
                  <i class="ni ni-copy me-1"></i>
                  Copiar
                </button>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge bg-light text-primary text-xxs">
                  Total referidos: <strong>{{ resumen.totalReferidos }}</strong>
                </span>
                <span class="badge bg-light text-success text-xxs">
                  Activos: <strong>{{ resumen.activos }}</strong>
                </span>
                <span class="badge bg-light text-warning text-xxs">
                  Pendientes: <strong>{{ resumen.pendientes }}</strong>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de referidos -->
        <div class="card shadow-sm border-0">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1 text-dark">Lista de referidos</h6>
              <p class="mb-0 text-xs text-muted">
                Visualiza tus referidos directos y su estado dentro del plan.
              </p>
            </div>
            <select v-model="filtroEstado" class="form-select form-select-sm w-auto">
              <option value="TODOS">Todos</option>
              <option value="ACTIVO">Activos</option>
              <option value="PENDIENTE">Pendientes</option>
              <option value="INACTIVO">Inactivos</option>
            </select>
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
                      Usuario
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                      Fecha alta
                    </th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                      Estado
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in referidosFiltrados" :key="r.id">
                    <td>
                      <div class="d-flex align-items-center px-2 py-1">
                        <div class="avatar avatar-sm bg-gradient-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center text-xs">
                          {{ iniciales(r.nombre, r.apellidos) }}
                        </div>
                        <div class="d-flex flex-column">
                          <span class="text-sm font-weight-bold">
                            {{ r.nombre }} {{ r.apellidos }}
                          </span>
                          <span class="text-xs text-muted">
                            {{ r.email }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle text-center text-sm">
                      <span class="text-xs text-dark">@{{ r.usuario }}</span>
                    </td>
                    <td class="align-middle text-center text-xs text-muted">
                      {{ r.fechaAlta }}
                    </td>
                    <td class="align-middle text-end">
                      <span class="badge badge-sm text-xxs" :class="badgeEstado(r.estado)">
                        {{ r.estado }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="referidosFiltrados.length === 0">
                    <td colspan="4" class="text-center text-sm text-muted py-4">
                      Aún no tienes referidos con el filtro seleccionado.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulario de inscripción nuevo socio -->
      <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-1 text-dark">Inscripción de nuevo socio</h6>
              <p class="mb-0 text-xs text-muted">
                Completa los datos del prospecto para crear su cuenta vinculada a tu código de referido.
              </p>
            </div>
            <span class="badge bg-gradient-success text-white text-xxs">
              Demo (sin envío real)
            </span>
          </div>
          <div class="card-body pt-3">
            <form @submit.prevent="enviarInscripcion">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-xs text-muted mb-1">Email</label>
                  <input
                    v-model.trim="form.email"
                    type="email"
                    class="form-control"
                    placeholder="correo@dominio.com"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">codigo de referido</label>
                  <input
                    v-model.trim="form.codigoReferido"
                    type="text"
                    class="form-control"
                    placeholder="codigo de referido"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">Nombre</label>
                  <input
                    v-model.trim="form.nombre"
                    type="text"
                    class="form-control"
                    placeholder="Nombres"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">Apellidos</label>
                  <input
                    v-model.trim="form.apellidos"
                    type="text"
                    class="form-control"
                    placeholder="Apellidos"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">CI / NIT</label>
                  <input
                    v-model.trim="form.ciNit"
                    type="text"
                    class="form-control"
                    placeholder="Documento"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">Fecha de nacimiento</label>
                  <input
                    v-model="form.fechaNacimiento"
                    type="date"
                    class="form-control"
                    required
                    @change="calcularEdad"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label text-xs text-muted mb-1">Edad</label>
                  <input
                    v-model.number="form.edad"
                    type="number"
                    min="18"
                    class="form-control"
                    placeholder="18+"
                    required
                  />
                </div>
                <div class="col-md-8">
                  <label class="form-label text-xs text-muted mb-1">Dirección</label>
                  <input
                    v-model.trim="form.direccion"
                    type="text"
                    class="form-control"
                    placeholder="Ciudad, país"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">Usuario</label>
                  <input
                    v-model.trim="form.usuario"
                    type="text"
                    class="form-control"
                    placeholder="usuario"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-xs text-muted mb-1">Contraseña</label>
                  <input
                    v-model="form.contrasena"
                    type="password"
                    class="form-control"
                    placeholder="Mínimo 8 caracteres"
                    required
                  />
                </div>
              </div>

              <div class="form-text text-xxs text-muted mt-2">
                Este formulario es solo un ejemplo de interfaz. Conecta tu backend para guardar realmente los datos y enviar correos de bienvenida.
              </div>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-success" :disabled="!formValido">
                  <i class="ni ni-check-bold me-2"></i>
                  Registrar socio
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="limpiarFormulario">
                  Limpiar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CardLinkReferidos",
  data() {
    return {
      linkReferido: "https://miempresa.com/registro?ref=ABC123",
      resumen: {
        totalReferidos: 8,
        activos: 5,
        pendientes: 3,
      },
      filtroEstado: "TODOS",
      referidos: [
        { id: 1, nombre: "Laura", apellidos: "García", email: "laura@example.com", usuario: "laura.g", fechaAlta: "05 Mar 2026", estado: "ACTIVO" },
        { id: 2, nombre: "Miguel", apellidos: "Pérez", email: "miguel@example.com", usuario: "mikep", fechaAlta: "02 Mar 2026", estado: "PENDIENTE" },
        { id: 3, nombre: "Ana", apellidos: "López", email: "ana@example.com", usuario: "analo", fechaAlta: "25 Feb 2026", estado: "ACTIVO" },
        { id: 4, nombre: "José", apellidos: "Ramírez", email: "jose@example.com", usuario: "jramirez", fechaAlta: "20 Feb 2026", estado: "INACTIVO" },
      ],
      form: {
        email: "",
        codigoReferido: "",
        nombre: "",
        apellidos: "",
        ciNit: "",
        fechaNacimiento: "",
        edad: null,
        direccion: "",
        usuario: "",
        contrasena: "",
      },
    };
  },
  computed: {
    referidosFiltrados() {
      if (this.filtroEstado === "TODOS") return this.referidos;
      return this.referidos.filter((r) => r.estado === this.filtroEstado);
    },
    formValido() {
      const f = this.form;
      return (
        f.email &&
        f.codigoReferido &&
        f.nombre &&
        f.apellidos &&
        f.ciNit &&
        f.fechaNacimiento &&
        f.edad &&
        f.direccion &&
        f.usuario &&
        f.contrasena &&
        String(f.contrasena).length >= 8
      );
    },
  },
  methods: {
    iniciales(nombre, apellidos) {
      const n = String(nombre || "").trim().split(/\s+/)[0] || "";
      const a = String(apellidos || "").trim().split(/\s+/)[0] || "";
      return `${n[0] || ""}${a[0] || ""}`.toUpperCase();
    },
    badgeEstado(estado) {
      const e = String(estado || "").toUpperCase();
      if (e === "ACTIVO") return "bg-gradient-success text-white";
      if (e === "PENDIENTE") return "bg-gradient-warning text-white";
      if (e === "INACTIVO") return "bg-secondary text-white";
      return "bg-light text-dark";
    },
    copiarLink() {
      try {
        const el = this.$refs.inputLink;
        if (el && el.select) {
          el.select();
          document.execCommand("copy");
        } else if (navigator.clipboard?.writeText) {
          navigator.clipboard.writeText(this.linkReferido);
        }
        // Aquí podrías mostrar una notificación (toast) integrada con tu sistema.
      } catch (e) {
        console.error("No se pudo copiar el enlace", e);
      }
    },
    refrescarDatos() {
      // Marca de lugar para conectar con tu API real
      // Ejemplo: this.fetchReferidos();
    },
    calcularEdad() {
      if (!this.form.fechaNacimiento) return;
      const hoy = new Date();
      const nacimiento = new Date(this.form.fechaNacimiento);
      let edad = hoy.getFullYear() - nacimiento.getFullYear();
      const m = hoy.getMonth() - nacimiento.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
      }
      this.form.edad = edad > 0 ? edad : null;
    },
    limpiarFormulario() {
      this.form = {
        email: "",
        codigoReferido: "",
        nombre: "",
        apellidos: "",
        ciNit: "",
        fechaNacimiento: "",
        edad: null,
        direccion: "",
        usuario: "",
        contrasena: "",
      };
    },
    enviarInscripcion() {
      if (!this.formValido) return;
      // Aquí deberías llamar a tu backend para registrar al nuevo socio.
      // Por ahora solo reseteamos el formulario.
      this.limpiarFormulario();
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
  background: radial-gradient(800px circle at 0 0, rgba(84, 177, 68, 0.05), transparent 55%), #ffffff;
}

.avatar {
  font-size: 0.7rem;
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

.bg-gradient-info {
  background: linear-gradient(87deg, #3d8b7a 0, #2d6b5d 100%) !important;
}
</style>