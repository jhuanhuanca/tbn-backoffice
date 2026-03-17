<template>
  <div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="card border-0 shadow flex-grow-1">
          <div class="p-4 card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
              <h2 class="mb-2 text-dark font-weight-bolder">Panel de comisiones</h2>
              <p class="text-muted mb-0 text-sm">
                Visualiza tus comisiones y entiende cómo funciona tu red binaria híbrida.
              </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" @click="refreshData">
                <i class="ni ni-refresh-02 me-2"></i>
                Actualizar datos
              </button>
              <button type="button" class="btn btn-sm btn-primary shadow-sm" @click="exportReport">
                <i class="ni ni-archive-2 me-2"></i>
                Descargar reporte
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Resumen principal -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 card-kpi kpi-primary">
          <div class="card-body d-flex justify-content-between align-items-start">
            <div>
              <span class="text-xs text-uppercase text-muted font-weight-bold">Total generado</span>
              <h4 class="mt-2 mb-1 text-dark">
                {{ currencySymbol }} {{ formatNumber(resumen.totalGenerado) }}
              </h4>
              <p class="text-xs mb-0 text-muted">
                Comisión acumulada desde tu inicio.
              </p>
            </div>
            <div class="kpi-icon bg-gradient-primary text-white shadow">
              <i class="ni ni-chart-bar-32"></i>
            </div>
          </div>
          <div class="card-footer border-0 pt-0">
            <span class="badge bg-light text-success text-xxs" v-if="resumen.variacionSemanal >= 0">
              <i class="ni ni-bold-up me-1"></i>
              +{{ resumen.variacionSemanal }}% esta semana
            </span>
            <span class="badge bg-light text-danger text-xxs" v-else>
              <i class="ni ni-bold-down me-1"></i>
              {{ resumen.variacionSemanal }}% esta semana
            </span>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 card-kpi kpi-success">
          <div class="card-body d-flex justify-content-between align-items-start">
            <div>
              <span class="text-xs text-uppercase text-muted font-weight-bold">Disponible para retiro</span>
              <h4 class="mt-2 mb-1 text-dark">
                {{ currencySymbol }} {{ formatNumber(resumen.disponible) }}
              </h4>
              <p class="text-xs mb-0 text-muted">
                Monto que puedes solicitar hoy.
              </p>
            </div>
            <div class="kpi-icon bg-gradient-success text-white shadow">
              <i class="ni ni-credit-card"></i>
            </div>
          </div>
          <div class="card-footer border-0 pt-0 d-flex justify-content-between align-items-center">
            <div class="text-xxs text-muted">
              Último retiro: {{ resumen.ultimoRetiro || "Sin retiros" }}
            </div>
            <button type="button" class="btn btn-xs btn-success" @click="openRetiroModal">
              Solicitar retiro
            </button>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 card-kpi kpi-warning">
          <div class="card-body d-flex justify-content-between align-items-start">
            <div>
              <span class="text-xs text-uppercase text-muted font-weight-bold">Pendiente en red</span>
              <h4 class="mt-2 mb-1 text-dark">
                {{ currencySymbol }} {{ formatNumber(resumen.pendiente) }}
              </h4>
              <p class="text-xs mb-0 text-muted">
                Comisiones en proceso por cierres y validaciones.
              </p>
            </div>
            <div class="kpi-icon bg-gradient-warning text-white shadow">
              <i class="ni ni-time-alarm"></i>
            </div>
          </div>
          <div class="card-footer border-0 pt-0">
            <span class="badge bg-gradient-warning text-white text-xxs">
              Cierre binario cada {{ configuracion.cierreSemanas }} semanas
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Layout secundario -->
    <div class="row">
      <!-- Tabla de comisiones -->
      <div class="col-xl-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Detalle de comisiones</h6>
              <p class="text-xs text-muted mb-0">
                Desglose por tipo de bono y estatus.
              </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <select v-model="filtros.mes" class="form-select form-select-sm w-auto">
                <option value="actual">Mes actual</option>
                <option value="anterior">Mes anterior</option>
                <option value="ultimos3">Últimos 3 meses</option>
              </select>
            </div>
          </div>
          <div class="card-body pt-3">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Tipo de bono</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-center">Red</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estatus</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="b in comisionesFiltradas" :key="b.id">
                    <td class="text-sm">
                      <div class="fw-semibold text-dark">{{ b.nombre }}</div>
                      <div class="text-xs text-muted">{{ b.descripcion }}</div>
                    </td>
                    <td class="text-center">
                      <span
                        class="badge text-xxs"
                        :class="b.lado === 'IZQ' ? 'bg-gradient-info text-white' : b.lado === 'DER' ? 'bg-gradient-primary text-white' : 'bg-secondary text-white'"
                      >
                        {{ labelLado(b.lado) }}
                      </span>
                    </td>
                    <td class="text-sm text-end">
                      {{ currencySymbol }} {{ formatNumber(b.monto) }}
                    </td>
                    <td class="text-sm text-end">
                      <span class="badge text-xxs" :class="badgeEstado(b.estado)">
                        {{ b.estado }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="comisionesFiltradas.length === 0">
                    <td colspan="4" class="text-center text-sm text-muted py-4">
                      No hay comisiones registradas para el filtro seleccionado.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <hr class="horizontal dark my-3" />

            <div class="row g-3">
              <div class="col-md-4">
                <div class="mini-stat">
                  <div class="label text-xs text-muted">Binario</div>
                  <div class="value text-sm text-dark fw-semibold">
                    {{ currencySymbol }} {{ formatNumber(resumenPorTipo.binario) }}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mini-stat">
                  <div class="label text-xs text-muted">Directo</div>
                  <div class="value text-sm text-dark fw-semibold">
                    {{ currencySymbol }} {{ formatNumber(resumenPorTipo.directo) }}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mini-stat">
                  <div class="label text-xs text-muted">Residual</div>
                  <div class="value text-sm text-dark fw-semibold">
                    {{ currencySymbol }} {{ formatNumber(resumenPorTipo.residual) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Explicación red binaria híbrida -->
      <div class="col-xl-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">¿Cómo funciona tu red binaria híbrida?</h6>
              <p class="text-xs text-muted mb-0">
                Visualización simple de volúmenes y derrame.
              </p>
            </div>
            <span class="badge bg-gradient-info text-white text-xxs">
              Guía rápida
            </span>
          </div>
          <div class="card-body pt-3">
            <!-- Esquema binario -->
            <div class="binary-wrapper mb-3">
              <div class="binary-node principal">
                <div class="circle bg-gradient-primary text-white shadow">
                  Tú
                </div>
                <div class="label text-xs text-muted mt-1">Patrocinador: {{ red.patrocinador }}</div>
              </div>
              <div class="binary-branches">
                <div class="branch-left">
                  <div class="circle bg-gradient-info text-white shadow">
                    IZQ
                  </div>
                  <div class="volumen text-xs mt-1">
                    Volumen: <span class="fw-semibold">{{ red.izquierda.volumen }} pts</span>
                  </div>
                  <div class="text-xxs text-muted">Activos: {{ red.izquierda.activos }}</div>
                </div>
                <div class="branch-right">
                  <div class="circle bg-gradient-success text-white shadow">
                    DER
                  </div>
                  <div class="volumen text-xs mt-1">
                    Volumen: <span class="fw-semibold">{{ red.derecha.volumen }} pts</span>
                  </div>
                  <div class="text-xxs text-muted">Activos: {{ red.derecha.activos }}</div>
                </div>
              </div>
              <div class="binary-footer text-xxs text-muted mt-2">
                El sistema paga sobre el <span class="fw-semibold">volumen menor</span> en cada cierre.
              </div>
            </div>

            <!-- Pasos explicativos -->
            <div class="steps-wrapper">
              <div class="step-item" v-for="(p, idx) in pasosBinario" :key="idx">
                <div class="step-index bg-gradient-primary text-white shadow">
                  {{ idx + 1 }}
                </div>
                <div class="step-content">
                  <div class="step-title text-sm text-dark fw-semibold">
                    {{ p.titulo }}
                  </div>
                  <div class="step-text text-xs text-muted">
                    {{ p.descripcion }}
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-info text-xs mt-3 mb-0">
              <div class="d-flex gap-2">
                <i class="ni ni-bulb-61 text-info mt-1"></i>
                <div>
                  Mantén siempre al menos <span class="fw-semibold">{{ configuracion.minActivosPorLado }}</span> socios activos por lado para cobrar el
                  <span class="fw-semibold">100% de tus comisiones binarias.</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Historial semanal -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
            <div>
              <h6 class="text-dark mb-1">Histórico semanal de comisiones</h6>
              <p class="text-xs text-muted mb-0">
                Vista rápida de tus cierres binarios por semana.
              </p>
            </div>
          </div>
          <div class="card-body pt-3">
            <div class="row g-3 align-items-center">
              <div class="col-lg-8">
                <div class="timeline">
                  <div
                    v-for="(s, idx) in historicoSemanal"
                    :key="s.semana"
                    class="timeline-item"
                  >
                    <div class="timeline-line" v-if="idx !== historicoSemanal.length - 1"></div>
                    <div class="timeline-badge" :class="s.monto > 0 ? 'bg-gradient-success' : 'bg-secondary'">
                      <i class="ni" :class="s.monto > 0 ? 'ni-check-bold' : 'ni-fat-remove'"></i>
                    </div>
                    <div class="timeline-content">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <div class="text-xs text-muted">
                            Semana {{ s.semana }} • Cierre {{ s.fecha }}
                          </div>
                          <div class="text-sm text-dark fw-semibold">
                            {{ currencySymbol }} {{ formatNumber(s.monto) }}
                          </div>
                        </div>
                        <div class="text-end text-xxs text-muted">
                          Vol. IZQ: {{ s.volumenIzq }} pts<br />
                          Vol. DER: {{ s.volumenDer }} pts
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="summary-box">
                  <div class="text-sm text-dark fw-semibold mb-1">
                    Promedios de los últimos cierres
                  </div>
                  <div class="text-xs text-muted mb-3">
                    Estos datos son referenciales. Conecta con tu backend para mostrar información real.
                  </div>
                  <ul class="list-unstyled mb-0 text-xs">
                    <li class="d-flex justify-content-between mb-2">
                      <span>Comisión promedio semanal</span>
                      <span class="fw-semibold">
                        {{ currencySymbol }} {{ formatNumber(promedios.semanal) }}
                      </span>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                      <span>Volumen promedio lado menor</span>
                      <span class="fw-semibold">
                        {{ promedios.volumenMenor }} pts
                      </span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span>Semanas con comisión</span>
                      <span class="fw-semibold">
                        {{ promedios.semanasConComision }}/{{ historicoSemanal.length }}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer border-0 pt-0 text-center text-xs text-muted">
            Datos de demostración. Integra tu API para valores en tiempo real.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CardComisiones",
  data() {
    return {
      currencySymbol: "USD",
      resumen: {
        totalGenerado: 12450.75,
        disponible: 1850.25,
        pendiente: 620.5,
        variacionSemanal: 18,
        ultimoRetiro: "05 Mar 2026",
      },
      configuracion: {
        cierreSemanas: 1,
        minActivosPorLado: 2,
      },
      filtros: {
        mes: "actual",
      },
      comisiones: [
        {
          id: 1,
          nombre: "Bono binario",
          descripcion: "Pago sobre volumen menor semanal.",
          lado: "MIXTO",
          monto: 450.0,
          estado: "Acreditado",
          periodo: "actual",
        },
        {
          id: 2,
          nombre: "Bono directo",
          descripcion: "Por referir un nuevo socio.",
          lado: "IZQ",
          monto: 120.5,
          estado: "Acreditado",
          periodo: "actual",
        },
        {
          id: 3,
          nombre: "Bono residual",
          descripcion: "Participación mensual por consumo de red.",
          lado: "DER",
          monto: 75.0,
          estado: "Pendiente",
          periodo: "actual",
        },
        {
          id: 4,
          nombre: "Bono binario",
          descripcion: "Cierre semana anterior.",
          lado: "MIXTO",
          monto: 380.0,
          estado: "Acreditado",
          periodo: "anterior",
        },
      ],
      red: {
        patrocinador: "ID-12345",
        izquierda: {
          volumen: 3250,
          activos: 14,
        },
        derecha: {
          volumen: 2890,
          activos: 11,
        },
      },
      pasosBinario: [
        {
          titulo: "Construye dos equipos",
          descripcion: "Desarrollas una red a la izquierda y otra a la derecha. Todos cuentan: tus directos y el derrame de tus patrocinadores.",
        },
        {
          titulo: "Se acumula volumen de puntos",
          descripcion: "Cada compra o paquete genera puntos que se suman al lado correspondiente (IZQ o DER) de tu organización.",
        },
        {
          titulo: "Cierre binario y pago",
          descripcion: "En cada cierre, el sistema identifica el lado con menor volumen y calcula tu comisión según el porcentaje definido en el plan.",
        },
        {
          titulo: "Red híbrida (binario + unilevel)",
          descripcion: "Además del binario, puedes ganar por bonos directos y residuales de varias generaciones, potenciando el ingreso recurrente.",
        },
      ],
      historicoSemanal: [
        { semana: "10", fecha: "09 Mar 2026", monto: 420.5, volumenIzq: 1100, volumenDer: 980 },
        { semana: "09", fecha: "02 Mar 2026", monto: 380.0, volumenIzq: 980, volumenDer: 1040 },
        { semana: "08", fecha: "24 Feb 2026", monto: 0, volumenIzq: 450, volumenDer: 520 },
        { semana: "07", fecha: "17 Feb 2026", monto: 315.75, volumenIzq: 870, volumenDer: 790 },
      ],
    };
  },
  computed: {
    comisionesFiltradas() {
      if (this.filtros.mes === "ultimos3") {
        return this.comisiones;
      }
      return this.comisiones.filter((c) => c.periodo === this.filtros.mes);
    },
    resumenPorTipo() {
      const base = { binario: 0, directo: 0, residual: 0 };
      this.comisiones.forEach((c) => {
        const n = (c.nombre || "").toLowerCase();
        if (n.includes("binario")) base.binario += c.monto;
        else if (n.includes("directo")) base.directo += c.monto;
        else if (n.includes("residual")) base.residual += c.monto;
      });
      return base;
    },
    promedios() {
      if (!this.historicoSemanal.length) {
        return { semanal: 0, volumenMenor: 0, semanasConComision: 0 };
      }
      let total = 0;
      let totalVolumenMenor = 0;
      let semanasCon = 0;
      this.historicoSemanal.forEach((s) => {
        total += s.monto;
        totalVolumenMenor += Math.min(s.volumenIzq, s.volumenDer);
        if (s.monto > 0) semanasCon += 1;
      });
      const len = this.historicoSemanal.length;
      return {
        semanal: total / len,
        volumenMenor: Math.round(totalVolumenMenor / len),
        semanasConComision: semanasCon,
      };
    },
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString("es-ES", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    labelLado(lado) {
      const v = String(lado || "").toUpperCase();
      if (v === "IZQ") return "Lado izquierdo";
      if (v === "DER") return "Lado derecho";
      return "Ambos lados";
    },
    badgeEstado(estado) {
      const e = String(estado || "").toLowerCase();
      if (e.includes("acred")) return "bg-gradient-success text-white";
      if (e.includes("pend")) return "bg-gradient-warning text-white";
      if (e.includes("rech")) return "bg-gradient-danger text-white";
      return "bg-secondary text-white";
    },
    refreshData() {
      // Aquí puedes integrar la llamada real a tu API
      // De momento solo simulamos una actualización visual.
      this.resumen.variacionSemanal = this.resumen.variacionSemanal + 1;
    },
    exportReport() {
      // Reemplaza por tu lógica real de exportación (CSV/PDF).
      // Esto es solo una marca de lugar amigable.
      alert("Función de exportar reporte pendiente de integración con el backend.");
    },
    openRetiroModal() {
      // Integra aquí tu modal o navegación a la pantalla de retiros.
      alert("Pantalla/modal de solicitud de retiro pendiente de integración.");
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}

.card-kpi {
  position: relative;
  overflow: hidden;
}

.card-kpi::before {
  content: "";
  position: absolute;
  inset: 0;
  opacity: 0.07;
  pointer-events: none;
  background-repeat: no-repeat;
  background-position: top right;
}

.card-kpi.kpi-primary::before {
  background-image: radial-gradient(circle at 80% 0, #54b144 0, transparent 55%);
}

.card-kpi.kpi-success::before {
  background-image: radial-gradient(circle at 80% 0, #54b144 0, transparent 55%);
}

.card-kpi.kpi-warning::before {
  background-image: radial-gradient(circle at 80% 0, #c9a227 0, transparent 55%);
}

.kpi-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.95rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.mini-stat {
  padding: 0.75rem 0.85rem;
  border-radius: 0.9rem;
  background: #f8f9fe;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.mini-stat .label {
  margin-bottom: 0.25rem;
}

.binary-wrapper {
  border-radius: 1.1rem;
  border: 1px solid #e9ecef;
  padding: 1.25rem;
  background: radial-gradient(800px circle at 0 0, rgba(84, 177, 68, 0.08), transparent 50%),
    #ffffff;
}

.binary-node .circle {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.binary-branches {
  margin-top: 1.25rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.25rem;
}

.branch-left,
.branch-right {
  text-align: center;
}

.branch-left .circle,
.branch-right .circle {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

.binary-footer {
  text-align: center;
}

.steps-wrapper {
  margin-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.step-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.step-index {
  width: 1.8rem;
  height: 1.8rem;
  border-radius: 0.7rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.step-content {
  flex: 1;
}

.timeline {
  position: relative;
}

.timeline-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.timeline-line {
  position: absolute;
  left: 0.9rem;
  top: 1.4rem;
  bottom: -0.6rem;
  width: 2px;
  background: linear-gradient(180deg, #e9ecef 0, #d2d6e0 100%);
}

.timeline-badge {
  width: 1.8rem;
  height: 1.8rem;
  border-radius: 0.65rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
}

.timeline-content {
  flex: 1;
  background: #f8f9fe;
  border-radius: 0.9rem;
  padding: 0.75rem 0.9rem;
  border: 1px solid rgba(226, 232, 240, 0.9);
}

.summary-box {
  border-radius: 1rem;
  background: #ffffff;
  border: 1px dashed rgba(94, 114, 228, 0.4);
  padding: 1rem;
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

@media (max-width: 768px) {
  .binary-branches {
    gap: 0.75rem;
  }

  .summary-box {
    margin-top: 1.5rem;
  }
}
</style>