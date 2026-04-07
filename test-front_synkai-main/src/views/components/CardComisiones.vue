<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div
            class="p-4 card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3"
          >
            <div>
              <h2 class="mb-2 text-dark font-weight-bolder">Comisiones</h2>
              <p class="text-muted mb-0 text-sm">Movimientos registrados en tu cuenta (auditoría MLM).</p>
            </div>
            <button
              type="button"
              class="btn btn-sm btn-outline-primary shadow-sm"
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

    <div v-if="error" class="alert alert-warning text-white mb-3">{{ error }}</div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 bir-line">
          <div class="card-body py-3">
            <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 1</span>
            <h5 class="mt-1 mb-0 text-dark">{{ formatBs(birByLevel[1]) }}</h5>
            <p class="text-xxs text-muted mb-0 mt-1">21 % sobre comisionable (1.ª generación)</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 bir-line">
          <div class="card-body py-3">
            <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 2</span>
            <h5 class="mt-1 mb-0 text-dark">{{ formatBs(birByLevel[2]) }}</h5>
            <p class="text-xxs text-muted mb-0 mt-1">15 % (2.ª generación)</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 bir-line">
          <div class="card-body py-3">
            <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 3</span>
            <h5 class="mt-1 mb-0 text-dark">{{ formatBs(birByLevel[3]) }}</h5>
            <p class="text-xxs text-muted mb-0 mt-1">6 % (3.ª generación)</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body">
            <span class="text-xs text-uppercase text-muted font-weight-bold">Total histórico</span>
            <h4 class="mt-2 mb-0 text-dark">{{ formatBs(resumen.total_accrued) }}</h4>
            <p class="text-xs text-muted mb-0 mt-2">Suma de eventos de comisión acreditados.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body">
            <span class="text-xs text-uppercase text-muted font-weight-bold">Saldo disponible</span>
            <h4 class="mt-2 mb-0 text-dark">{{ formatBs(disponible) }}</h4>
            <p class="text-xs text-muted mb-0 mt-2">Desde billetera (ledger).</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 d-flex align-items-stretch">
        <div class="card shadow-sm border-0 w-100">
          <div class="card-body d-flex align-items-center justify-content-center">
            <router-link to="/billetera" class="btn btn-success w-100">Ir a billetera</router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header border-0 pb-0">
        <h6 class="text-dark mb-1">Detalle</h6>
      </div>
      <div class="card-body pt-3">
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-xs text-uppercase text-muted font-weight-bold">Tipo</th>
                <th class="text-xs text-uppercase text-muted font-weight-bold text-center">Periodo</th>
                <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto</th>
                <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in items" :key="row.id">
                <td class="text-sm">
                  <div class="fw-semibold text-dark">{{ row.type_label }}</div>
                  <div class="text-xs text-muted" v-if="row.level != null">Nivel {{ row.level }}</div>
                </td>
                <td class="text-center text-xs text-muted">{{ row.period_key || "—" }}</td>
                <td class="text-sm text-end">{{ formatBs(row.amount) }}</td>
                <td class="text-end">
                  <span class="badge bg-gradient-success text-white text-xxs">{{ row.status }}</span>
                </td>
              </tr>
              <tr v-if="!loading && items.length === 0">
                <td colspan="4" class="text-center text-sm text-muted py-4">
                  Sin comisiones registradas aún.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { fetchCommissions } from "@/services/me";
import { fetchWalletBalance } from "@/services/wallet";

export default {
  name: "CardComisiones",
  data() {
    return {
      loading: false,
      error: null,
      resumen: { total_accrued: "0" },
      birByLevel: { 1: "0", 2: "0", 3: "0" },
      items: [],
      disponible: "0",
    };
  },
  mounted() {
    this.cargar();
  },
  methods: {
    formatBs(v) {
      const n = Number(v);
      if (Number.isNaN(n)) {
        return "—";
      }
      return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
      }).format(n);
    },
    async cargar() {
      if (!localStorage.getItem("token")) {
        this.error = "Inicia sesión.";
        return;
      }
      this.loading = true;
      this.error = null;
      try {
        const [c, w] = await Promise.all([fetchCommissions(), fetchWalletBalance()]);
        this.resumen = c.summary || { total_accrued: "0" };
        const br = c.bir_by_level || {};
        this.birByLevel = {
          1: br[1] ?? br["1"] ?? "0",
          2: br[2] ?? br["2"] ?? "0",
          3: br[3] ?? br["3"] ?? "0",
        };
        this.items = c.items || [];
        this.disponible = w.available || "0";
      } catch {
        this.error = "No se pudieron cargar las comisiones.";
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
.bir-line {
  border-left: 3px solid #54b144;
}
.bg-gradient-success {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}
</style>
