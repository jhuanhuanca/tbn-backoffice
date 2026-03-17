<template>
  <div class="container-fluid py-4">
    <!-- Título y descripción -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
        <div class="p-4 card-body">
          <h4 class="mb-2 text-dark font-weight-bolder">Resumen de compras</h4>
          <p class="mb-0 text-sm text-secondary">
            Visualiza el estado general de las compras realizadas en tu plataforma.
          </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjetas de estado (estilo Argon Dashboard) -->
    <div class="row">
      <!-- Compras realizadas -->
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Compras realizadas
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ comprasRealizadas.toLocaleString() }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                  <i class="ni ni-cart"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-success">
              <i class="ni ni-bold-up me-1"></i>
              <span class="font-weight-bold">{{ variacionRealizadas }}%</span>
              vs. periodo anterior
            </p>
          </div>
        </div>
      </div>

      <!-- Compras enviadas -->
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Compras enviadas
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ comprasEnviadas.toLocaleString() }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                  <i class="ni ni-send"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-success">
              <i class="ni ni-bold-up me-1"></i>
              <span class="font-weight-bold">{{ variacionEnviadas }}%</span>
              tasa de cumplimiento
            </p>
          </div>
        </div>
      </div>

      <!-- Compras por enviar -->
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Compras por enviar
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ comprasPorEnviar.toLocaleString() }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                  <i class="ni ni-delivery-fast"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-warning">
              <i class="ni ni-watch-time me-1"></i>
              Pendientes de despacho
            </p>
          </div>
        </div>
      </div>

      <!-- Compras por pagar -->
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">
                  Compras por pagar
                </div>
                <div class="h4 mb-0 font-weight-bold text-dark">
                  {{ formatCurrency(comprasPorPagar) }}
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow">
                  <i class="ni ni-credit-card"></i>
                </div>
              </div>
            </div>
            <p class="mb-0 mt-3 text-sm text-danger">
              <i class="ni ni-fat-remove me-1"></i>
              Revisar facturas pendientes
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla resumen (opcional, estilo Argon) -->
    <div class="row mt-3">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-dark mb-1">Detalle rápido</h6>
              <p class="text-xs text-muted mb-0">
                Vista general de los últimos movimientos de compra.
              </p>
            </div>
            <div class="btn-group btn-group-sm">
              <button
                type="button"
                class="btn btn-outline-primary active"
              >
                Hoy
              </button>
              <button
                type="button"
                class="btn btn-outline-primary"
              >
                7 días
              </button>
              <button
                type="button"
                class="btn btn-outline-primary"
              >
                30 días
              </button>
            </div>
          </div>
          <div class="card-body px-3 py-3">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">
                      Tipo
                    </th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">
                      Cantidad
                    </th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">
                      Tendencia
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-sm">
                      Compras realizadas
                    </td>
                    <td class="text-sm font-weight-bold">
                      {{ comprasRealizadas.toLocaleString() }}
                    </td>
                    <td class="text-sm text-success">
                      +{{ variacionRealizadas }}%
                    </td>
                  </tr>
                  <tr>
                    <td class="text-sm">
                      Compras enviadas
                    </td>
                    <td class="text-sm font-weight-bold">
                      {{ comprasEnviadas.toLocaleString() }}
                    </td>
                    <td class="text-sm text-success">
                      +{{ variacionEnviadas }}%
                    </td>
                  </tr>
                  <tr>
                    <td class="text-sm">
                      Compras por enviar
                    </td>
                    <td class="text-sm font-weight-bold">
                      {{ comprasPorEnviar.toLocaleString() }}
                    </td>
                    <td class="text-sm text-warning">
                      En proceso
                    </td>
                  </tr>
                  <tr>
                    <td class="text-sm">
                      Compras por pagar
                    </td>
                    <td class="text-sm font-weight-bold">
                      {{ formatCurrency(comprasPorPagar) }}
                    </td>
                    <td class="text-sm text-danger">
                      Pendiente
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
  name: 'CardComprasRealizadas',
  data() {
    return {
      // Datos de ejemplo; aquí puedes conectar tu API o Vuex
      comprasRealizadas: 1243,
      comprasEnviadas: 980,
      comprasPorEnviar: 263,
      comprasPorPagar: 15230.75,
      variacionRealizadas: 12.5,
      variacionEnviadas: 8.3,
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

.table thead th {
  border-bottom: 1px solid #e9ecef;
}

.table tbody tr + tr td {
  border-top: 1px solid #f1f3f9;
}
</style>

