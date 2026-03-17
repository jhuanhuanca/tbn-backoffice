<script setup>
import { computed, ref } from "vue";
import ArgonButton from "@/components/ArgonButton.vue";

const props = defineProps({
  paquete: {
    type: Object,
    default: () => ({
      nombre: "Paquete",
      precio: "Bs. 0",
      pv: "0 PV",
    }),
  },
});

// Paso 1: inscripción (demo UI)
const inscripcion = ref({
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
});

const inscripcionCreada = ref(false);

const formValido = computed(() => {
  const f = inscripcion.value;
  return (
    String(f.email).includes("@") &&
    String(f.codigoReferido).trim().length >= 3 &&
    String(f.nombre).trim().length >= 2 &&
    String(f.apellidos).trim().length >= 2 &&
    String(f.ciNit).trim().length >= 4 &&
    Boolean(f.fechaNacimiento) &&
    Number(f.edad) >= 18 &&
    String(f.direccion).trim().length >= 4 &&
    String(f.usuario).trim().length >= 3 &&
    String(f.contrasena).length >= 8
  );
});

const calcularEdad = () => {
  const f = inscripcion.value;
  if (!f.fechaNacimiento) return;
  const birth = new Date(f.fechaNacimiento);
  if (Number.isNaN(birth.getTime())) return;
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age -= 1;
  inscripcion.value.edad = Math.max(age, 0);
};

const enviarInscripcion = () => {
  if (!formValido.value) return;
  inscripcionCreada.value = true;
};

const limpiarInscripcion = () => {
  inscripcion.value = {
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
  inscripcionCreada.value = false;
};

const metodo = ref("tarjeta"); // tarjeta | qr | efectivo

const isTarjeta = computed(() => metodo.value === "tarjeta");
const isQr = computed(() => metodo.value === "qr");

const tarjeta = ref({
  nombre: "",
  numero: "",
  exp: "",
  cvv: "",
});

const qr = ref({
  referencia: "",
});

const efectivo = ref({
  referencia: "",
  notas: "",
});

const pagar = () => {
  // Placeholder: aquí conectarías a tu backend / pasarela
  alert("Pago enviado (demo).");
};
</script>

<template>
  <div class="row g-4">
    <!-- Selector de método -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
          <!-- Paso 1: Inscripción -->
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <h6 class="mb-1 text-dark font-weight-bolder">Inscripción de nuevo socio</h6>
              <p class="mb-0 text-xs text-muted">
                Crea el usuario y contraseña. Luego podrás realizar el pago.
              </p>
            </div>
            <span class="badge bg-gradient-success text-white text-xxs">Demo</span>
          </div>

          <form class="mt-3" @submit.prevent="enviarInscripcion">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label text-xs text-muted mb-1">Email</label>
                <input
                  v-model.trim="inscripcion.email"
                  type="email"
                  class="form-control"
                  placeholder="correo@dominio.com"
                  required
                />
              </div>
              <div class="col-12">
                <label class="form-label text-xs text-muted mb-1">Código de referido</label>
                <input
                  v-model.trim="inscripcion.codigoReferido"
                  type="text"
                  class="form-control"
                  placeholder="Tu código"
                  required
                />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Nombre</label>
                <input v-model.trim="inscripcion.nombre" type="text" class="form-control" placeholder="Nombres" required />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Apellidos</label>
                <input v-model.trim="inscripcion.apellidos" type="text" class="form-control" placeholder="Apellidos" required />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">CI / NIT</label>
                <input v-model.trim="inscripcion.ciNit" type="text" class="form-control" placeholder="Documento" required />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Fecha de nacimiento</label>
                <input v-model="inscripcion.fechaNacimiento" type="date" class="form-control" required @change="calcularEdad" />
              </div>
              <div class="col-md-4">
                <label class="form-label text-xs text-muted mb-1">Edad</label>
                <input v-model.number="inscripcion.edad" type="number" min="18" class="form-control" placeholder="18+" required />
              </div>
              <div class="col-md-8">
                <label class="form-label text-xs text-muted mb-1">Dirección</label>
                <input v-model.trim="inscripcion.direccion" type="text" class="form-control" placeholder="Ciudad, país" required />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Usuario</label>
                <input v-model.trim="inscripcion.usuario" type="text" class="form-control" placeholder="usuario" required />
              </div>
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Contraseña</label>
                <input v-model="inscripcion.contrasena" type="password" class="form-control" placeholder="Mínimo 8 caracteres" required />
              </div>
            </div>

            <div v-if="inscripcionCreada" class="alert alert-success py-2 mt-3 mb-0">
              <div class="text-sm">
                <i class="ni ni-check-bold me-2"></i>
                Usuario creado (demo). Continúa con el pago.
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <argon-button color="success" variant="gradient" :disabled="!formValido">
                <i class="ni ni-check-bold me-2"></i>
                Crear usuario
              </argon-button>
              <argon-button color="secondary" variant="outline" type="button" @click="limpiarInscripcion">
                Limpiar
              </argon-button>
            </div>
          </form>

          <hr class="horizontal dark my-4" />

          <!-- Paso 2: Pago -->
          <h6 class="text-dark font-weight-bolder mb-1">Pago de suscripción</h6>
          <p class="text-sm text-muted mb-3">
            {{ inscripcionCreada ? 'Elige el método y confirma tu pago.' : 'Primero crea el usuario para habilitar el pago.' }}
          </p>

          <div class="p-3 rounded-3 bg-soft-primary mb-3">
            <div class="text-xs text-uppercase text-muted font-weight-bold">Paquete seleccionado</div>
            <div class="d-flex justify-content-between align-items-start mt-2">
              <div>
                <div class="text-dark font-weight-bolder">{{ props.paquete.nombre }}</div>
                <div class="text-xs text-muted">{{ props.paquete.pv }}</div>
              </div>
              <div class="text-success font-weight-bolder">{{ props.paquete.precio }}</div>
            </div>
          </div>

          <div class="list-group list-group-flush">
            <button
              type="button"
              class="list-group-item list-group-item-action px-0 d-flex align-items-center justify-content-between"
              :class="metodo === 'tarjeta' ? 'active-method' : ''"
              @click="metodo = 'tarjeta'"
              :disabled="!inscripcionCreada"
            >
              <span class="d-flex align-items-center">
                <i class="ni ni-credit-card text-primary me-2"></i>
                Tarjeta
              </span>
              <i class="ni ni-bold-right"></i>
            </button>
            <button
              type="button"
              class="list-group-item list-group-item-action px-0 d-flex align-items-center justify-content-between"
              :class="metodo === 'qr' ? 'active-method' : ''"
              @click="metodo = 'qr'"
              :disabled="!inscripcionCreada"
            >
              <span class="d-flex align-items-center">
                <i class="ni ni-camera-compact text-success me-2"></i>
                QR
              </span>
              <i class="ni ni-bold-right"></i>
            </button>
            <button
              type="button"
              class="list-group-item list-group-item-action px-0 d-flex align-items-center justify-content-between"
              :class="metodo === 'efectivo' ? 'active-method' : ''"
              @click="metodo = 'efectivo'"
              :disabled="!inscripcionCreada"
            >
              <span class="d-flex align-items-center">
                <i class="ni ni-money-coins text-dark me-2"></i>
                Efectivo
              </span>
              <i class="ni ni-bold-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel del método -->
    <div class="col-lg-8">
      <div v-if="!inscripcionCreada" class="card border-0 shadow-sm h-100">
        <div class="card-body p-4 p-lg-5 d-flex align-items-center justify-content-center text-center">
          <div>
            <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow mx-auto mb-3">
              <i class="ni ni-single-02"></i>
            </div>
            <h6 class="text-dark font-weight-bolder mb-2">Primero crea el usuario</h6>
            <p class="text-sm text-muted mb-0">
              Completa el formulario de inscripción para habilitar el pago de la suscripción.
            </p>
          </div>
        </div>
      </div>

      <!-- Tarjeta -->
      <div v-else-if="isTarjeta" class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0 pb-0 p-4">
          <h6 class="text-dark font-weight-bolder mb-1">Pago con tarjeta</h6>
          <p class="text-sm text-muted mb-0">Completa los datos para procesar tu suscripción.</p>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label text-sm">Nombre en la tarjeta</label>
              <input v-model.trim="tarjeta.nombre" type="text" class="form-control" placeholder="Nombre y apellido" />
            </div>
            <div class="col-12">
              <label class="form-label text-sm">Número de tarjeta</label>
              <input v-model.trim="tarjeta.numero" type="text" class="form-control" placeholder="0000 0000 0000 0000" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-sm">Expiración</label>
              <input v-model.trim="tarjeta.exp" type="text" class="form-control" placeholder="MM/AA" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-sm">CVV</label>
              <input v-model.trim="tarjeta.cvv" type="password" class="form-control" placeholder="***" />
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <argon-button color="success" variant="gradient" @click="pagar">
              <i class="ni ni-check-bold me-2"></i>
              Pagar ahora
            </argon-button>
            <argon-button
              color="secondary"
              variant="outline"
              @click="tarjeta = { nombre: '', numero: '', exp: '', cvv: '' }"
            >
              Limpiar
            </argon-button>
          </div>
        </div>
      </div>

      <!-- QR -->
      <div v-else-if="isQr" class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0 pb-0 p-4">
          <h6 class="text-dark font-weight-bolder mb-1">Pago por QR</h6>
          <p class="text-sm text-muted mb-0">Escanea el QR y registra tu referencia para validar el pago.</p>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="row g-4 align-items-center">
            <div class="col-md-5">
              <div class="qr-box d-flex align-items-center justify-content-center">
                <div class="text-center">
                  <div class="text-xs text-muted mb-2">QR (demo)</div>
                  <div class="qr-placeholder mx-auto"></div>
                  <div class="text-xs text-muted mt-2 mb-0">Conecta con backend para QR real</div>
                </div>
              </div>
            </div>
            <div class="col-md-7">
              <label class="form-label text-sm">Referencia / Comprobante</label>
              <input v-model.trim="qr.referencia" type="text" class="form-control" placeholder="Ej: 00012345" />
              <div class="form-text">
                Si tu banco entrega un número de comprobante, pégalo aquí para agilizar la verificación.
              </div>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <argon-button color="success" variant="gradient" @click="pagar">
                  <i class="ni ni-check-bold me-2"></i>
                  Ya pagué (enviar)
                </argon-button>
                <argon-button color="secondary" variant="outline" @click="qr = { referencia: '' }">
                  Limpiar
                </argon-button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Efectivo -->
      <div v-else class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0 pb-0 p-4">
          <h6 class="text-dark font-weight-bolder mb-1">Pago en efectivo</h6>
          <p class="text-sm text-muted mb-0">Registra tu referencia para coordinar la entrega y validación.</p>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="alert alert-info py-3">
            <div class="d-flex gap-3">
              <i class="ni ni-bulb-61 text-info mt-1"></i>
              <div class="text-sm">
                Puedes pagar en efectivo con un líder autorizado o punto de cobro. Te contactaremos para confirmar.
              </div>
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label text-sm">Referencia</label>
              <input v-model.trim="efectivo.referencia" type="text" class="form-control" placeholder="Ej: EF-0001" />
            </div>
            <div class="col-md-6">
              <label class="form-label text-sm">Notas (opcional)</label>
              <input v-model.trim="efectivo.notas" type="text" class="form-control" placeholder="Ciudad / punto de cobro" />
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <argon-button color="success" variant="gradient" @click="pagar">
              <i class="ni ni-check-bold me-2"></i>
              Enviar solicitud
            </argon-button>
            <argon-button
              color="secondary"
              variant="outline"
              @click="efectivo = { referencia: '', notas: '' }"
            >
              Limpiar
            </argon-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-soft-primary {
  background: rgba(84, 177, 68, 0.08);
}

.active-method {
  background: rgba(84, 177, 68, 0.12);
  border-radius: 0.5rem;
  font-weight: 700;
}

.qr-box {
  border-radius: 1rem;
  background: #f8f9fe;
  border: 1px solid #e9ecef;
  padding: 1rem;
  height: 100%;
  min-height: 220px;
}

.qr-placeholder {
  width: 160px;
  height: 160px;
  border-radius: 1rem;
  background: repeating-linear-gradient(
    45deg,
    rgba(84, 177, 68, 0.2),
    rgba(84, 177, 68, 0.2) 6px,
    rgba(84, 177, 68, 0.06) 6px,
    rgba(84, 177, 68, 0.06) 12px
  );
  border: 1px solid rgba(34, 45, 37, 0.2);
}
</style>
