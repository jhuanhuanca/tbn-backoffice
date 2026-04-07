<template>
  <div class="container-fluid py-4">
    <div v-if="walletError" class="alert alert-warning text-white mb-3" role="alert">
      {{ walletError }}
    </div>

    <div class="row mb-4">
      <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h4 class="mb-1 text-dark font-weight-bolder">Billetera</h4>
          <p class="mb-0 text-sm text-secondary">
            Saldo calculado desde el libro de movimientos (créditos, débitos, retenciones).
          </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button
            type="button"
            class="btn btn-sm btn-outline-primary shadow-sm"
            :disabled="walletLoading"
            @click="cargarTodo"
          >
            <i class="ni ni-refresh me-2"></i>
            {{ walletLoading ? "Cargando…" : "Actualizar" }}
          </button>
          <button type="button" class="btn btn-sm btn-primary shadow-sm" @click="solicitarRetiro">
            <i class="ni ni-money-coins me-2"></i>
            Solicitar retiro
          </button>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-xl-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">Saldo disponible</div>
            <div class="h4 mb-0 font-weight-bold text-dark">
              {{ formatCurrency(saldoDisponible) }}
            </div>
            <p class="mb-0 mt-2 text-xs text-muted">Moneda BOB · Sin monto mínimo de retiro.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header border-0 pb-0">
            <h6 class="text-dark mb-1">Movimientos recientes</h6>
            <p class="text-xs text-muted mb-0">Últimos 100 registros del ledger.</p>
          </div>
          <div class="card-body pt-3">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Tipo</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Referencia</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Fecha</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="m in movimientos" :key="m.id">
                    <td class="text-sm">{{ etiquetaTipo(m.type) }}</td>
                    <td class="text-xs text-muted">{{ m.description || m.reference || "—" }}</td>
                    <td class="text-xs text-muted">{{ formatFecha(m.created_at) }}</td>
                    <td class="text-sm text-end fw-semibold" :class="montoClass(m.type, m.amount)">
                      {{ formatMonto(m.type, m.amount) }}
                    </td>
                  </tr>
                  <tr v-if="!walletLoading && movimientos.length === 0">
                    <td colspan="4" class="text-center text-muted py-4 text-sm">
                      No hay movimientos.
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
import api from "@/services/api";
import { fetchWalletBalance } from "@/services/wallet";
import { fetchWalletTransactions } from "@/services/me";

export default {
  name: "CardBilletera",
  data() {
    return {
      walletLoading: false,
      walletError: null,
      saldoDisponible: null,
      movimientos: [],
    };
  },
  mounted() {
    this.cargarTodo();
  },
  methods: {
    async cargarTodo() {
      if (!localStorage.getItem("token")) {
        this.walletError = "Inicia sesión para ver tu billetera.";
        return;
      }
      this.walletLoading = true;
      this.walletError = null;
      try {
        const [bal, tx] = await Promise.all([fetchWalletBalance(), fetchWalletTransactions()]);
        this.saldoDisponible = Number(bal.available);
        this.movimientos = tx.data || [];
      } catch (e) {
        this.walletError = e.response?.data?.message || "No se pudo cargar la billetera.";
        this.movimientos = [];
      } finally {
        this.walletLoading = false;
      }
    },
    etiquetaTipo(t) {
      const x = {
        credit: "Crédito",
        debit: "Débito",
        retention: "Retención (retiro)",
        retention_release: "Liberación retención",
      };
      return x[t] || t;
    },
    formatFecha(iso) {
      if (!iso) {
        return "—";
      }
      try {
        return new Date(iso).toLocaleString("es-BO");
      } catch {
        return iso;
      }
    },
    formatCurrency(value) {
      if (value === null || value === undefined || Number.isNaN(Number(value))) {
        return "—";
      }
      return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
      }).format(Number(value));
    },
    formatMonto(type, amount) {
      const n = Math.abs(Number(amount));
      const isOut = type === "debit" || type === "retention";
      return (isOut ? "- " : "+ ") + this.formatCurrency(n);
    },
    montoClass(type) {
      if (type === "credit" || type === "retention_release") {
        return "text-success";
      }
      if (type === "debit" || type === "retention") {
        return "text-danger";
      }
      return "text-dark";
    },
    async solicitarRetiro() {
      const raw = window.prompt("Monto a retirar (Bs)", "");
      if (raw === null || raw === "") {
        return;
      }
      const monto = String(raw).replace(",", ".").trim();
      try {
        await api.post("/withdrawals", { monto, notas: "" });
        window.alert("Solicitud registrada. Un administrador la procesará.");
        await this.cargarTodo();
      } catch (e) {
        const msg =
          e.response?.data?.message ||
          (e.response?.data?.errors && JSON.stringify(e.response.data.errors)) ||
          "Error al solicitar retiro.";
        window.alert(msg);
      }
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}
</style>
