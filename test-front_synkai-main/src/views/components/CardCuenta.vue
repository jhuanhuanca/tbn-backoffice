<template>
  <div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
          <h2 class="mb-2 text-dark font-weight-bolder">Mi cuenta</h2>
          <p class="text-muted mb-0">
            Administra tu perfil, seguridad, billetera y soporte.
          </p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" @click="resetDemo">
            <i class="ni ni-refresh me-2"></i>
            Restablecer
          </button>
          <button type="button" class="btn btn-sm btn-primary shadow-sm" @click="saveAll">
            <i class="ni ni-check-bold me-2"></i>
            Guardar cambios
          </button>
        </div>
      </div>
    </div>
    </div>

    <!-- Layout principal -->
    <div class="row">
      <!-- Navegación interna -->
      <div class="col-lg-4 col-xl-3 mb-4">
        <div class="card shadow-sm border-0 position-sticky account-sticky">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-lg rounded-circle bg-gradient-primary shadow text-white">
                <span class="fw-bold">{{ initials(perfil.nombre) }}</span>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold text-dark">{{ perfil.nombre || "—" }}</div>
                <div class="text-xs text-muted text-truncate">{{ perfil.email }}</div>
                <div v-if="perfil.member_code" class="text-xxs text-success mt-1">Código: {{ perfil.member_code }}</div>
                <div v-if="perfil.rango" class="text-xxs text-warning mt-1">Rango: {{ perfil.rango }}</div>
                <div v-if="perfil.country_label" class="text-xxs text-muted">{{ perfil.country_label }}</div>
              </div>
            </div>

            <hr class="horizontal dark my-4" />

            <div class="list-group list-group-flush">
              <a class="list-group-item list-group-item-action px-0" href="#perfil">
                <i class="ni ni-single-02 text-primary me-2"></i>
                Editar perfil
              </a>
              <a class="list-group-item list-group-item-action px-0" href="#password">
                <i class="ni ni-lock-circle-open text-warning me-2"></i>
                Cambiar contraseña
              </a>
              <a class="list-group-item list-group-item-action px-0" href="#wallet">
                <i class="ni ni-wallet text-success me-2"></i>
                Configurar billetera
              </a>
              <a class="list-group-item list-group-item-action px-0" href="#twofa">
                <i class="ni ni-key-25 text-info me-2"></i>
                Autenticación 2FA
              </a>
              <a class="list-group-item list-group-item-action px-0" href="#soporte">
                <i class="ni ni-support-16 text-danger me-2"></i>
                Soporte / Tickets
              </a>
              <router-link to="/mi-landing" class="list-group-item list-group-item-action px-0 text-decoration-none text-dark">
                <i class="ni ni-world text-success me-2"></i>
                Mi landing personal
              </router-link>
            </div>

            <div class="mt-4 p-3 rounded-3 tip-card">
              <div class="d-flex align-items-start gap-3">
                <div class="tip-icon bg-white text-primary shadow-sm">
                  <i class="ni ni-bulb-61"></i>
                </div>
                <div>
                  <div class="text-sm fw-semibold text-white mb-1">Consejo de seguridad</div>
                  <div class="text-xs text-white-50 mb-0">
                    Activa 2FA y utiliza una contraseña única para proteger tu cuenta.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido -->
      <div class="col-lg-8 col-xl-9">
        <!-- Mi landing personal -->
        <div class="card shadow-sm border-0 mb-4 bg-soft-success">
          <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="icon-landing-card bg-gradient-success text-white rounded-circle shadow">
                <i class="ni ni-world"></i>
              </div>
              <div>
                <h6 class="text-dark fw-bold mb-1">Mi landing personal</h6>
                <p class="text-sm text-muted mb-0">Tu página pública con tu foto, datos, videos y llamadas a la acción para tu red multinivel.</p>
              </div>
            </div>
            <button type="button" class="btn btn-success shadow-sm" @click="goToMiLanding">
              <i class="ni ni-bold-right me-2"></i>
              Ver / Editar mi landing
            </button>
          </div>
        </div>

        <!-- Editar perfil -->
        <div id="perfil" class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Editar perfil</h6>
              <p class="text-xs text-muted mb-0">Actualiza tus datos personales y de contacto.</p>
            </div>
            <span class="badge bg-gradient-primary text-white">Perfil</span>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-sm">Nombre completo</label>
                <input v-model.trim="perfil.nombre" type="text" class="form-control" placeholder="Tu nombre" />
              </div>
              <div class="col-md-6">
                <label class="form-label text-sm">Usuario</label>
                <input v-model.trim="perfil.username" type="text" class="form-control" placeholder="@usuario" />
              </div>
              <div class="col-md-6">
                <label class="form-label text-sm">Email</label>
                <input v-model.trim="perfil.email" type="email" class="form-control" placeholder="correo@dominio.com" />
              </div>
              <div class="col-md-6">
                <label class="form-label text-sm">Teléfono</label>
                <input v-model.trim="perfil.telefono" type="text" class="form-control" placeholder="+(xx) xxx xxx" />
              </div>
              <div class="col-12">
                <label class="form-label text-sm">Bio</label>
                <textarea v-model.trim="perfil.bio" class="form-control" rows="3" placeholder="Cuéntanos algo sobre ti"></textarea>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="button" class="btn btn-primary" @click="savePerfil">
                <i class="ni ni-check-bold me-2"></i>
                Guardar perfil
              </button>
              <button type="button" class="btn btn-outline-secondary" @click="resetPerfil">
                Cancelar
              </button>
            </div>
          </div>
        </div>

        <!-- Cambiar contraseña -->
        <div id="password" class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Cambiar contraseña</h6>
              <p class="text-xs text-muted mb-0">Recomendado: 12+ caracteres con letras y números.</p>
            </div>
            <span class="badge bg-gradient-warning text-white">Seguridad</span>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label text-sm">Contraseña actual</label>
                <input v-model="password.actual" type="password" class="form-control" autocomplete="current-password" />
              </div>
              <div class="col-md-4">
                <label class="form-label text-sm">Nueva contraseña</label>
                <input v-model="password.nueva" type="password" class="form-control" autocomplete="new-password" />
              </div>
              <div class="col-md-4">
                <label class="form-label text-sm">Confirmar nueva</label>
                <input v-model="password.confirmar" type="password" class="form-control" autocomplete="new-password" />
              </div>
            </div>

            <div class="mt-3">
              <div class="text-xs text-muted mb-1">Fuerza</div>
              <div class="progress">
                <div
                  class="progress-bar"
                  :class="passwordStrengthClass"
                  role="progressbar"
                  :style="{ width: `${passwordStrength}%` }"
                  :aria-valuenow="passwordStrength"
                  aria-valuemin="0"
                  aria-valuemax="100"
                ></div>
              </div>
              <div class="text-xs mt-2" :class="passwordStrengthTextClass">
                {{ passwordStrengthLabel }}
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="button" class="btn btn-warning" @click="savePassword" :disabled="!canSavePassword">
                <i class="ni ni-lock-circle-open me-2"></i>
                Actualizar contraseña
              </button>
              <button type="button" class="btn btn-outline-secondary" @click="resetPassword">
                Limpiar
              </button>
            </div>
          </div>
        </div>

        <!-- Configurar billetera -->
        <div id="wallet" class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Configurar billetera</h6>
              <p class="text-xs text-muted mb-0">Define tu método de cobro para comisiones y retiros.</p>
            </div>
            <span class="badge bg-gradient-success text-white">Billetera</span>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-sm">Método de pago</label>
                <select v-model="wallet.metodo" class="form-select">
                  <option value="USDT">USDT (TRC20)</option>
                  <option value="BTC">Bitcoin</option>
                  <option value="BANK">Transferencia bancaria</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-sm">Moneda preferida</label>
                <select v-model="wallet.moneda" class="form-select">
                  <option v-for="m in latamMonedas" :key="m.value" :value="m.value">
                    {{ m.label }}
                  </option>
                </select>
              </div>

              <div class="col-12" v-if="wallet.metodo !== 'BANK'">
                <label class="form-label text-sm">Dirección / Wallet</label>
                <input v-model.trim="wallet.direccion" type="text" class="form-control" placeholder="Pega tu dirección" />
                <div class="form-text">
                  Asegúrate de que la red coincida con el método seleccionado.
                </div>
              </div>

              <template v-else>
                <div class="col-md-6">
                  <label class="form-label text-sm">Banco</label>
                  <input v-model.trim="wallet.banco" type="text" class="form-control" placeholder="Nombre del banco" />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-sm">Titular</label>
                  <input v-model.trim="wallet.titular" type="text" class="form-control" placeholder="Nombre del titular" />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-sm">Cuenta / IBAN</label>
                  <input v-model.trim="wallet.cuenta" type="text" class="form-control" placeholder="Número de cuenta" />
                </div>
                <div class="col-md-6">
                  <label class="form-label text-sm">SWIFT (opcional)</label>
                  <input v-model.trim="wallet.swift" type="text" class="form-control" placeholder="SWIFT/BIC" />
                </div>
              </template>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="button" class="btn btn-success" @click="saveWallet">
                <i class="ni ni-check-bold me-2"></i>
                Guardar billetera
              </button>
              <button type="button" class="btn btn-outline-secondary" @click="resetWallet">
                Cancelar
              </button>
            </div>
          </div>
        </div>

        <!-- 2FA -->
        <div id="twofa" class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Autenticación 2FA</h6>
              <p class="text-xs text-muted mb-0">Añade una capa extra de seguridad con una app autenticadora.</p>
            </div>
            <span class="badge" :class="twofa.enabled ? 'bg-gradient-success text-white' : 'bg-secondary text-white'">
              {{ twofa.enabled ? "Activado" : "Desactivado" }}
            </span>
          </div>
          <div class="card-body pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="secure-icon bg-gradient-info text-white shadow">
                  <i class="ni ni-key-25"></i>
                </div>
                <div>
                  <div class="text-sm fw-semibold text-dark">2FA con TOTP</div>
                  <div class="text-xs text-muted">
                    Compatible con Google Authenticator, Authy y similares.
                  </div>
                </div>
              </div>

              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" v-model="twofa.enabled" />
                <label class="form-check-label text-sm">
                  {{ twofa.enabled ? "Activo" : "Inactivo" }}
                </label>
              </div>
            </div>

            <div v-if="twofa.enabled" class="mt-4">
              <div class="row g-3">
                <div class="col-md-7">
                  <div class="alert alert-info py-3 mb-0">
                    <div class="d-flex gap-3">
                      <i class="ni ni-mobile-button text-info mt-1"></i>
                      <div class="text-sm">
                        Escanea el QR en tu app autenticadora y guarda tus códigos de respaldo.
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="qr-box d-flex align-items-center justify-content-center">
                    <div class="text-center">
                      <div class="text-xs text-muted mb-2">QR de ejemplo</div>
                      <div class="qr-placeholder mx-auto"></div>
                      <div class="text-xs text-muted mt-2 mb-0">Conecta con tu backend para el QR real</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="button" class="btn btn-info" @click="save2fa">
                  <i class="ni ni-check-bold me-2"></i>
                  Confirmar 2FA
                </button>
                <button type="button" class="btn btn-outline-danger" @click="disable2fa">
                  Desactivar 2FA
                </button>
              </div>
            </div>

            <div v-else class="mt-4">
              <div class="alert alert-warning py-3 mb-0">
                <div class="d-flex gap-3">
                  <i class="ni ni-fat-remove text-warning mt-1"></i>
                  <div class="text-sm">
                    Tu cuenta es más vulnerable sin 2FA. Recomendamos activarlo.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Soporte / Tickets -->
        <div id="soporte" class="card shadow-sm border-0 mb-4">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Soporte / Tickets</h6>
              <p class="text-xs text-muted mb-0">Crea un ticket y consulta el estado de tus solicitudes.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" @click="newTicketOpen = !newTicketOpen">
              <i class="ni ni-email-83 me-2"></i>
              {{ newTicketOpen ? "Ocultar" : "Nuevo ticket" }}
            </button>
          </div>
          <div class="card-body pt-3">
            <div v-if="newTicketOpen" class="mb-4">
              <div class="row g-3">
                <div class="col-md-5">
                  <label class="form-label text-sm">Asunto</label>
                  <input v-model.trim="ticketDraft.asunto" type="text" class="form-control" placeholder="Ej: Problema con retiro" />
                </div>
                <div class="col-md-3">
                  <label class="form-label text-sm">Categoría</label>
                  <select v-model="ticketDraft.categoria" class="form-select">
                    <option value="BILLETERA">Billetera</option>
                    <option value="COMPRAS">Compras</option>
                    <option value="CUENTA">Cuenta</option>
                    <option value="OTRO">Otro</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label text-sm">Prioridad</label>
                  <select v-model="ticketDraft.prioridad" class="form-select">
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label text-sm">Mensaje</label>
                  <textarea v-model.trim="ticketDraft.mensaje" class="form-control" rows="4" placeholder="Describe tu solicitud..."></textarea>
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-primary" @click="createTicket" :disabled="!canCreateTicket">
                  <i class="ni ni-send me-2"></i>
                  Enviar ticket
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="resetTicketDraft">
                  Limpiar
                </button>
              </div>
              <hr class="horizontal dark my-4" />
            </div>

            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Ticket</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Categoría</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Fecha</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in tickets" :key="t.id">
                    <td class="text-sm">
                      <div class="fw-semibold text-dark">{{ t.asunto }}</div>
                      <div class="text-xs text-muted">#{{ t.codigo }}</div>
                    </td>
                    <td class="text-sm text-muted">{{ t.categoria }}</td>
                    <td class="text-sm text-muted">{{ t.fecha }}</td>
                    <td class="text-end">
                      <span class="badge" :class="ticketBadge(t.estado)">{{ t.estado }}</span>
                    </td>
                  </tr>
                  <tr v-if="tickets.length === 0">
                    <td colspan="4" class="text-center text-sm text-muted py-4">
                      No tienes tickets aún.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="text-center text-xs text-muted pb-2">
          Última actualización: {{ lastUpdated }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  fetchProfile,
  updateMyProfile,
  changeMyPassword,
  fetchWalletSettings,
  updateWalletSettings,
  fetchSupportTickets,
  createSupportTicket,
} from "@/services/me";
import { getEffectiveRankName } from "@/utils/mlm";
import { labelCountry } from "@/constants/latamCountries";

const LATAM_MONEDAS = [
  { value: "BOB", label: "Boliviano (BOB)" },
  { value: "ARS", label: "Peso argentino (ARS)" },
  { value: "BRL", label: "Real (BRL)" },
  { value: "CLP", label: "Peso chileno (CLP)" },
  { value: "COP", label: "Peso colombiano (COP)" },
  { value: "CRC", label: "Colón costarricense (CRC)" },
  { value: "USD", label: "Dólar estadounidense (USD)" },
  { value: "EUR", label: "Euro (EUR)" },
  { value: "GTQ", label: "Quetzal (GTQ)" },
  { value: "HNL", label: "Lempira (HNL)" },
  { value: "MXN", label: "Peso mexicano (MXN)" },
  { value: "PAB", label: "Balboa (PAB)" },
  { value: "PEN", label: "Sol (PEN)" },
  { value: "PYG", label: "Guaraní (PYG)" },
  { value: "UYU", label: "Peso uruguayo (UYU)" },
  { value: "VES", label: "Bolívar (VES)" },
  { value: "DOP", label: "Peso dominicano (DOP)" },
  { value: "NIO", label: "Córdoba (NIO)" },
];

export default {
  name: "CardCuenta",
  data() {
    return {
      perfil: {
        nombre: "",
        username: "",
        email: "",
        telefono: "",
        bio: "",
        member_code: "",
        rango: "",
        country_label: "",
      },
      perfilInitial: null,
      password: {
        actual: "",
        nueva: "",
        confirmar: "",
      },
      wallet: {
        metodo: "USDT",
        moneda: "BOB",
        direccion: "",
        banco: "",
        titular: "",
        cuenta: "",
        swift: "",
      },
      walletInitial: null,
      twofa: {
        enabled: false,
      },
      tickets: [],
      newTicketOpen: false,
      ticketDraft: {
        asunto: "",
        categoria: "BILLETERA",
        prioridad: "Media",
        mensaje: "",
      },
      lastUpdated: "",
      latamMonedas: LATAM_MONEDAS,
    };
  },
  computed: {
    passwordStrength() {
      const p = String(this.password.nueva || "");
      if (!p) return 0;

      let score = 0;
      if (p.length >= 8) score += 25;
      if (p.length >= 12) score += 15;
      if (/[a-z]/.test(p)) score += 15;
      if (/[A-Z]/.test(p)) score += 15;
      if (/\d/.test(p)) score += 15;
      if (/[^a-zA-Z0-9]/.test(p)) score += 15;
      return Math.min(score, 100);
    },
    passwordStrengthLabel() {
      const s = this.passwordStrength;
      if (s === 0) return "Ingresa una nueva contraseña";
      if (s < 45) return "Débil";
      if (s < 75) return "Media";
      return "Fuerte";
    },
    passwordStrengthClass() {
      const s = this.passwordStrength;
      if (s === 0) return "bg-secondary";
      if (s < 45) return "bg-gradient-danger";
      if (s < 75) return "bg-gradient-warning";
      return "bg-gradient-success";
    },
    passwordStrengthTextClass() {
      const s = this.passwordStrength;
      if (s === 0) return "text-muted";
      if (s < 45) return "text-danger";
      if (s < 75) return "text-warning";
      return "text-success";
    },
    canSavePassword() {
      return (
        this.password.actual.length > 0 &&
        this.password.nueva.length >= 8 &&
        this.password.nueva === this.password.confirmar
      );
    },
    canCreateTicket() {
      return this.ticketDraft.asunto.length >= 4 && this.ticketDraft.mensaje.length >= 10;
    },
  },
  created() {
    this.perfilInitial = JSON.parse(JSON.stringify(this.perfil));
    this.walletInitial = JSON.parse(JSON.stringify(this.wallet));
    this.lastUpdated = this.nowLabel();
  },
  async mounted() {
    await this.loadProfileFromApi();
    await this.loadWalletSettings();
    await this.loadTickets();
  },
  methods: {
    labelCountry,
    async loadProfileFromApi() {
      if (!localStorage.getItem("token")) return;
      try {
        const u = await fetchProfile();
        const rango = getEffectiveRankName(u);
        this.perfil = {
          nombre: u.name || "",
          username: (u.email && u.email.split("@")[0]) || "",
          email: u.email || "",
          telefono: u.phone || "",
          bio: this.perfil.bio || "",
          member_code: u.member_code != null ? String(u.member_code) : "",
          rango: rango ? String(rango) : "",
          country_label: labelCountry(u.country_code),
        };
        this.perfilInitial = JSON.parse(JSON.stringify(this.perfil));
        this.lastUpdated = this.nowLabel();
      } catch {
        /* sesión inválida */
      }
    },
    async loadWalletSettings() {
      if (!localStorage.getItem("token")) return;
      try {
        const r = await fetchWalletSettings();
        const s = r.wallet_settings || null;
        if (s) {
          this.wallet = {
            metodo: s.method || this.wallet.metodo,
            moneda: s.currency || this.wallet.moneda,
            direccion: s.address || "",
            banco: s.bank || "",
            titular: s.holder || "",
            cuenta: s.account || "",
            swift: s.swift || "",
          };
        }
        this.walletInitial = JSON.parse(JSON.stringify(this.wallet));
      } catch {
        /* ignore */
      }
    },
    async loadTickets() {
      if (!localStorage.getItem("token")) return;
      try {
        const r = await fetchSupportTickets();
        this.tickets = (r.items || []).map((t) => ({
          id: t.id,
          codigo: t.code,
          asunto: t.subject,
          categoria: t.category,
          fecha: t.created_at ? new Date(t.created_at).toLocaleDateString("es-BO") : "—",
          estado: t.status,
        }));
      } catch {
        this.tickets = [];
      }
    },
    nowLabel() {
      const d = new Date();
      return d.toLocaleString("es-ES", { year: "numeric", month: "short", day: "2-digit", hour: "2-digit", minute: "2-digit" });
    },
    initials(nombre) {
      const parts = String(nombre || "").trim().split(/\s+/).filter(Boolean);
      const a = parts[0]?.[0] || "U";
      const b = parts[1]?.[0] || "";
      return `${a}${b}`.toUpperCase();
    },
    resetDemo() {
      this.resetPerfil();
      this.resetPassword();
      this.resetWallet();
      this.twofa.enabled = false;
      this.resetTicketDraft();
      this.newTicketOpen = false;
      this.lastUpdated = this.nowLabel();
    },
    saveAll() {
      this.savePerfil();
      this.saveWallet();
      if (this.canSavePassword) this.savePassword();
      if (this.twofa.enabled) this.save2fa();
      this.lastUpdated = this.nowLabel();
    },
    // Perfil
    async savePerfil() {
      try {
        const r = await updateMyProfile({
          name: this.perfil.nombre,
          phone: this.perfil.telefono,
        });
        const u = r.user || null;
        if (u) {
          this.perfil.nombre = u.name || this.perfil.nombre;
          this.perfil.telefono = u.phone || this.perfil.telefono;
        }
        this.perfilInitial = JSON.parse(JSON.stringify(this.perfil));
        this.lastUpdated = this.nowLabel();
      } catch {
        /* ignore */
      }
    },
    resetPerfil() {
      this.perfil = JSON.parse(JSON.stringify(this.perfilInitial || this.perfil));
      this.lastUpdated = this.nowLabel();
    },
    // Password
    async savePassword() {
      if (!this.canSavePassword) return;
      try {
        await changeMyPassword({
          current_password: this.password.actual,
          password: this.password.nueva,
          password_confirmation: this.password.confirmar,
        });
        this.password.actual = "";
        this.password.nueva = "";
        this.password.confirmar = "";
        this.lastUpdated = this.nowLabel();
      } catch {
        /* ignore */
      }
    },
    resetPassword() {
      this.password.actual = "";
      this.password.nueva = "";
      this.password.confirmar = "";
      this.lastUpdated = this.nowLabel();
    },
    // Wallet
    async saveWallet() {
      try {
        await updateWalletSettings({
          wallet_settings: {
            method: this.wallet.metodo,
            currency: this.wallet.moneda,
            address: this.wallet.direccion,
            bank: this.wallet.banco,
            holder: this.wallet.titular,
            account: this.wallet.cuenta,
            swift: this.wallet.swift,
          },
        });
        this.walletInitial = JSON.parse(JSON.stringify(this.wallet));
        this.lastUpdated = this.nowLabel();
      } catch {
        /* ignore */
      }
    },
    resetWallet() {
      this.wallet = JSON.parse(JSON.stringify(this.walletInitial || this.wallet));
      this.lastUpdated = this.nowLabel();
    },
    // 2FA
    save2fa() {
      this.lastUpdated = this.nowLabel();
    },
    disable2fa() {
      this.twofa.enabled = false;
      this.lastUpdated = this.nowLabel();
    },
    // Tickets
    resetTicketDraft() {
      this.ticketDraft = {
        asunto: "",
        categoria: "BILLETERA",
        prioridad: "Media",
        mensaje: "",
      };
      this.lastUpdated = this.nowLabel();
    },
    createTicket() {
      if (!this.canCreateTicket) return;
      this.createTicketApi();
    },
    async createTicketApi() {
      try {
        const r = await createSupportTicket({
          subject: this.ticketDraft.asunto,
          category: this.ticketDraft.categoria,
          priority: this.ticketDraft.prioridad,
          message: this.ticketDraft.mensaje,
        });
        const t = r.ticket;
        if (t) {
          this.tickets = [
            {
              id: t.id,
              codigo: t.code,
              asunto: t.subject,
              categoria: t.category,
              fecha: t.created_at ? new Date(t.created_at).toLocaleDateString("es-BO") : this.nowLabel(),
              estado: t.status,
            },
            ...this.tickets,
          ];
        }
        this.resetTicketDraft();
        this.newTicketOpen = false;
        this.lastUpdated = this.nowLabel();
      } catch {
        /* ignore */
      }
    },
    ticketBadge(estado) {
      const e = String(estado || "").toLowerCase();
      if (e.includes("resu")) return "bg-gradient-success text-white";
      if (e.includes("proceso")) return "bg-gradient-warning text-white";
      if (e.includes("abier")) return "bg-gradient-info text-white";
      return "bg-secondary text-white";
    },
    goToMiLanding() {
      this.$router.push("/mi-landing");
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}

.account-sticky {
  top: 1.25rem;
}

.avatar {
  width: 56px;
  height: 56px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.tip-card {
  background: radial-gradient(900px circle at 10% 10%, rgba(84, 177, 68, 0.06), transparent 45%),
    linear-gradient(87deg, #737b7c 0, #00050c 100%);
  box-shadow: 0 14px 34px rgba(17, 205, 239, 0.25);
}

.tip-icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.secure-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
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
  width: 120px;
  height: 120px;
  border-radius: 1rem;
  background: repeating-linear-gradient(45deg, rgba(84, 177, 68, 0.2), rgba(84, 177, 68, 0.2) 6px, rgba(84, 177, 68, 0.06) 6px, rgba(84, 177, 68, 0.06) 12px);
  border: 1px solid rgba(94, 114, 228, 0.25);
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

.bg-gradient-danger {
  background: linear-gradient(87deg, #c0392b 0, #a93226 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(87deg, #3d8b7a 0, #2d6b5d 100%) !important;
}

.table thead th {
  border-bottom: 1px solid #e9ecef;
}

.table tbody tr + tr td {
  border-top: 1px solid #f1f3f9;
}

.list-group-item {
  border: 0;
  color: #344767;
}

.bg-soft-success {
  background: rgba(84, 177, 68, 0.08);
}

.icon-landing-card {
  width: 3rem;
  height: 3rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
</style>

