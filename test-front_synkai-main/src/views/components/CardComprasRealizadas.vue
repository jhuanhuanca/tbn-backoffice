<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h4 class="mb-1 text-dark font-weight-bolder">Mis pedidos</h4>
              <p class="mb-0 text-sm text-secondary">Pedidos registrados en el back office.</p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" :disabled="loading" @click="cargar">
              Actualizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-warning text-white mb-3">{{ error }}</div>

    <div class="row">
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">Total pedidos</div>
            <div class="h4 mb-0 font-weight-bold text-dark">{{ total }}</div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="text-uppercase text-muted text-xs font-weight-bold mb-1">Completados</div>
            <div class="h4 mb-0 font-weight-bold text-dark">{{ completados }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-body px-0">
            <div class="table-responsive">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">ID</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Tipo</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Total</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">PV</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Estado</th>
                    <th class="text-xs text-uppercase text-muted font-weight-bold">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="o in orders" :key="o.id">
                    <td class="text-sm">#{{ o.id }}</td>
                    <td class="text-sm">{{ o.tipo }}</td>
                    <td class="text-sm">{{ formatBs(o.total) }}</td>
                    <td class="text-sm">{{ o.total_pv }}</td>
                    <td class="text-sm">
                      <span class="badge bg-gradient-info text-white">{{ o.estado }}</span>
                    </td>
                    <td class="text-xs text-muted">{{ formatFecha(o.created_at) }}</td>
                  </tr>
                  <tr v-if="!loading && orders.length === 0">
                    <td colspan="6" class="text-center text-muted py-4">Sin pedidos.</td>
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
import { fetchOrders } from "@/services/me";

export default {
  name: "CardComprasRealizadas",
  data() {
    return {
      loading: false,
      error: null,
      orders: [],
    };
  },
  computed: {
    total() {
      return this.orders.length;
    },
    completados() {
      return this.orders.filter((o) => o.estado === "completado").length;
    },
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
    async cargar() {
      if (!localStorage.getItem("token")) {
        this.error = "Inicia sesión.";
        return;
      }
      this.loading = true;
      this.error = null;
      try {
        const page = await fetchOrders();
        this.orders = page.data || [];
      } catch {
        this.error = "No se pudieron cargar los pedidos.";
        this.orders = [];
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 1rem;
}
.bg-gradient-info {
  background: linear-gradient(87deg, #3d8b7a 0, #2d6b5d 100%) !important;
}
</style>
