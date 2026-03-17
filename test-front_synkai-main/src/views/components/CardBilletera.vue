<template>
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="card border-0 shadow flex-grow-1">
          <div class="p-4 card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
            <h4 class="mb-2 text-dark font-weight-bolder">Billetera</h4>
            <p class="mb-0 text-sm text-secondary">
            Controla tu saldo, comisiones multinivel y movimientos recientes. </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary shadow-sm">
            <i class="ni ni-refresh me-2"></i>
            Actualizar
        </button>
        <button type="button" class="btn btn-sm btn-primary shadow-sm">
            <i class="ni ni-money-coins me-2"></i>
            Solicitar retiro
        </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Saldo disponible
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ formatCurrency(saldoDisponible) }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                  <i class="ni ni-wallet"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-success">
              <i class="ni ni-bold-up me-1"></i>
              <span class="font-weight-bold">{{ variacionSaldo }}%</span>
              últimas 24h
            </p>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Comisiones hoy
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ formatCurrency(comisionesHoy) }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                  <i class="ni ni-chart-bar-32"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-muted">
              <i class="ni ni-calendar-grid-58 me-1"></i>
              Actualizado en tiempo real
            </p>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Pendiente por liberar
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ formatCurrency(pendienteLiberar) }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                  <i class="ni ni-watch-time"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-warning">
              <i class="ni ni-notification-70 me-1"></i>
              Sujeto a validación
            </p>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Retiros en proceso
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ retirosEnProceso.toLocaleString() }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow">
                  <i class="ni ni-delivery-fast"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-danger">
              <i class="ni ni-time-alarm me-1"></i>
              En revisión
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel principal -->
    <div class="row">
      <!-- Resumen + Acciones -->
      <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="text-dark mb-1">Resumen</h6>
                <p class="text-xs text-muted mb-0">
                  Vista rápida de tu billetera multinivel.
                </p>
              </div>
              <span class="badge bg-gradient-success text-white">
                Cuenta activa
              </span>
            </div>
          </div>
          <div class="card-body pt-3">
            <div class="wallet-hero p-4 rounded-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-xs text-white-50 text-uppercase mb-1">
                    Saldo disponible
                  </div>
                  <div class="display-6 text-white fw-bold mb-0">
                    {{ formatCurrency(saldoDisponible) }}
                  </div>
                  <div class="text-sm text-white-50 mt-2">
                    Mínimo de retiro: <span class="fw-semibold">{{ formatCurrency(minimoRetiro) }}</span>
                  </div>
                </div>
                <div class="wallet-chip">
                  <i class="ni ni-credit-card text-white"></i>
                </div>
              </div>
              <div class="row mt-4 g-3">
                <div class="col-6">
                  <div class="mini-stat">
                    <div class="text-xs text-white-50 mb-1">Nivel 1</div>
                    <div class="text-white fw-semibold">{{ formatCurrency(comisionNivel1) }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mini-stat">
                    <div class="text-xs text-white-50 mb-1">Niveles 2+</div>
                    <div class="text-white fw-semibold">{{ formatCurrency(comisionNiveles2Plus) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
              <button type="button" class="btn btn-outline-primary flex-grow-1">
                <i class="ni ni-send me-2"></i>
                Transferir
              </button>
              <button type="button" class="btn btn-primary flex-grow-1">
                <i class="ni ni-money-coins me-2"></i>
                Retirar
              </button>
            </div>

            <div class="mt-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="text-sm text-muted">
                  Progreso a tu siguiente nivel
                </div>
                <div class="text-sm fw-semibold text-dark">
                  {{ progresoNivel }}%
                </div>
              </div>
              <div class="progress">
                <div
                  class="progress-bar bg-gradient-primary"
                  role="progressbar"
                  :style="{ width: `${progresoNivel}%` }"
                  :aria-valuenow="progresoNivel"
                  aria-valuemin="0"
                  aria-valuemax="100"
                ></div>
              </div>
              <p class="text-xs text-muted mt-2 mb-0">
                Completa más compras en tu red para aumentar tu porcentaje de comisión.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Movimientos -->
      <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Movimientos</h6>
              <p class="text-xs text-muted mb-0">
                Últimas transacciones de comisiones, retiros y ajustes.
              </p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary">
              Ver todo
            </button>
          </div>
          <div class="card-body pt-3">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Concepto</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Fecha</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in movimientos" :key="m.id">
                    <td class="text-sm">
                      <div class="d-flex align-items-center gap-3">
                        <div
                          class="tx-icon rounded-circle shadow-sm"
                          :class="statusIconClass(m.tipo)"
                        >
                          <i :class="statusIcon(m.tipo)"></i>
                        </div>
                        <div>
                          <div class="fw-semibold text-dark">{{ m.concepto }}</div>
                          <div class="text-xs text-muted">{{ m.tipo }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="text-sm text-muted">{{ m.fecha }}</td>
                    <td class="text-sm text-end fw-semibold" :class="amountClass(m.monto)">
                      {{ formatSignedCurrency(m.monto) }}
                    </td>
                    <td class="text-end">
                      <span class="badge" :class="badgeClass(m.estado)">
                        {{ m.estado }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="movimientos.length === 0">
                    <td colspan="4" class="text-center text-sm text-muted py-4">
                      No hay movimientos para mostrar.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CardBilletera",
  data() {
    return {
      // Datos de ejemplo; aquí conectas tu API/Vuex/Pinia
      saldoDisponible: 3250.5,
      variacionSaldo: 4.2,
      comisionesHoy: 128.25,
      pendienteLiberar: 420.0,
      retirosEnProceso: 2,
      minimoRetiro: 50,
      comisionNivel1: 86.15,
      comisionNiveles2Plus: 42.1,
      progresoNivel: 62,
      movimientos: [
        {
          id: 1,
          tipo: "Comisión",
          concepto: "Bono por compra (Nivel 1)",
          fecha: "Hoy, 10:14",
          monto: 24.5,
          estado: "Aprobado",
        },
        {
          id: 2,
          tipo: "Retiro",
          concepto: "Solicitud de retiro",
          fecha: "Ayer, 18:02",
          monto: -150,
          estado: "En proceso",
        },
        {
          id: 3,
          tipo: "Ajuste",
          concepto: "Ajuste de balance",
          fecha: "Mar 10, 09:30",
          monto: 10,
          estado: "Aprobado",
        },
      ],
    };
  },
  methods: {
    formatCurrency(value) {
      if (value === null || value === undefined || Number.isNaN(Number(value))) return "-";
      return new Intl.NumberFormat("es-ES", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
      }).format(Number(value));
    },
    formatSignedCurrency(value) {
      if (value === null || value === undefined || Number.isNaN(Number(value))) return "-";
      const n = Number(value);
      const sign = n > 0 ? "+" : "";
      return `${sign}${this.formatCurrency(n)}`;
    },
    badgeClass(estado) {
      const e = String(estado || "").toLowerCase();
      if (e.includes("aprob")) return "bg-gradient-success text-white";
      if (e.includes("proceso") || e.includes("pend")) return "bg-gradient-warning text-white";
      if (e.includes("rech")) return "bg-gradient-danger text-white";
      return "bg-secondary text-white";
    },
    amountClass(monto) {
      const n = Number(monto);
      if (Number.isNaN(n)) return "text-muted";
      return n >= 0 ? "text-success" : "text-danger";
    },
    statusIcon(tipo) {
      const t = String(tipo || "").toLowerCase();
      if (t.includes("retiro")) return "ni ni-money-coins text-white";
      if (t.includes("ajuste")) return "ni ni-settings-gear-65 text-white";
      return "ni ni-chart-bar-32 text-white";
    },
    statusIconClass(tipo) {
      const t = String(tipo || "").toLowerCase();
      if (t.includes("retiro")) return "bg-gradient-danger";
      if (t.includes("ajuste")) return "bg-gradient-warning";
      return "bg-gradient-primary";
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}

.icon-shape {
  width: 3rem;
  height: 3rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.tx-icon {
  width: 2.5rem;
  height: 2.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.wallet-hero {
  background: radial-gradient(1200px circle at 10% 10%, rgba(84, 177, 68, 0.08), transparent 40%),
    linear-gradient(87deg, #929396 0, #19191a 100%);
  box-shadow: 0 12px 30px rgba(94, 114, 228, 0.35);
}

.wallet-chip {
  width: 3rem;
  height: 3rem;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(6px);
}

.mini-stat {
  padding: 0.85rem 1rem;
  border-radius: 0.9rem;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.16);
}

.bg-gradient-primary {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}

.bg-gradient-success {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}

.bg-gradient-warning {
  background: linear-gradient(87deg, #52bd61 0, #034d1b 100%) !important;
}

.bg-gradient-danger {
  background: linear-gradient(87deg, #1b0907 0, #110504 100%) !important;
}

.table thead th {
  border-bottom: 1px solid #e9ecef;
}

.table tbody tr + tr td {
  border-top: 1px solid #f1f3f9;
}
</style>

