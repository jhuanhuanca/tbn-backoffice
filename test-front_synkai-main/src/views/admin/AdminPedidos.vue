<script setup>
import { onMounted, ref, reactive } from "vue";
import { fetchAdminOrders, confirmOrderPayment } from "@/services/admin";

const loading = ref(true);
const error = ref("");
const items = ref([]);
const meta = ref(null);
const estadoFiltro = ref("pendiente_pago");
const procesandoId = ref(null);
const metodoConfirm = reactive({});
const notasConfirm = reactive({});

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await fetchAdminOrders({
      estado: estadoFiltro.value,
      per_page: 50,
    });
    const rows = data.data ?? data;
    items.value = Array.isArray(rows) ? rows : [];
    meta.value = data.meta ?? null;
    items.value.forEach((r) => {
      if (metodoConfirm[r.id] == null) {
        metodoConfirm[r.id] = r.payment_method || "efectivo";
      }
    });
  } catch {
    error.value = "No se pudieron cargar los pedidos.";
    items.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);

async function confirmarPago(row) {
  procesandoId.value = row.id;
  error.value = "";
  try {
    await confirmOrderPayment(row.id, {
      payment_method: metodoConfirm[row.id] || row.payment_method || "efectivo",
      notas: notasConfirm[row.id] || null,
    });
    notasConfirm[row.id] = "";
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || "No se pudo confirmar el pago.";
  } finally {
    procesandoId.value = null;
  }
}

function formatBs(v) {
  const n = Number(v);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(n);
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="mb-3">
      <router-link to="/admin/dashboard" class="text-sm text-muted">&larr; Volver al panel empresa</router-link>
    </div>
    <h4 class="mb-2">Pedidos y confirmación de pago</h4>
    <p class="text-sm text-muted mb-3">
      Confirma pagos en <strong>efectivo</strong>, <strong>QR</strong> u otros acordados fuera del cobro inmediato en
      sistema. Al confirmar, el pedido pasa a completado y se encolan comisiones MLM.
    </p>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
      <label class="text-sm mb-0">Ver:</label>
      <select v-model="estadoFiltro" class="form-select form-select-sm w-auto" @change="load">
        <option value="pendiente_pago">Pendientes de pago</option>
        <option value="completado">Completados</option>
      </select>
    </div>

    <p v-if="error" class="text-danger text-sm">{{ error }}</p>
    <div v-if="loading" class="text-sm text-muted">Cargando…</div>
    <div v-else class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-xxs">ID</th>
            <th class="text-uppercase text-xxs">Socio</th>
            <th class="text-uppercase text-xxs">Tipo</th>
            <th class="text-uppercase text-xxs text-end">Total</th>
            <th class="text-uppercase text-xxs">Estado</th>
            <th class="text-uppercase text-xxs">Pago ref.</th>
            <th class="text-uppercase text-xxs text-end">Acción / fecha</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in items" :key="row.id">
            <td class="text-sm">{{ row.id }}</td>
            <td class="text-sm">
              {{ row.user?.name }}
              <span class="text-muted">({{ row.user?.member_code || "—" }})</span>
            </td>
            <td class="text-sm">{{ row.tipo }}</td>
            <td class="text-sm text-end">{{ formatBs(row.total) }}</td>
            <td class="text-sm">
              <span
                v-if="row.estado === 'pendiente_pago'"
                class="badge badge-sm bg-gradient-warning text-white text-xxs"
              >
                Pendiente pago
              </span>
              <span v-else class="badge badge-sm bg-gradient-success text-white text-xxs">{{ row.estado }}</span>
            </td>
            <td class="text-sm text-muted">{{ row.payment_method || "—" }}</td>
            <td class="text-sm text-end">
              <template v-if="row.estado === 'pendiente_pago'">
                <div class="d-flex flex-wrap justify-content-end gap-1 align-items-center">
                  <select v-model="metodoConfirm[row.id]" class="form-select form-select-sm w-auto py-1">
                    <option value="efectivo">Efectivo</option>
                    <option value="qr">QR</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="otro">Otro</option>
                  </select>
                  <input
                    v-model="notasConfirm[row.id]"
                    type="text"
                    class="form-control form-control-sm d-inline-block"
                    style="max-width: 140px"
                    placeholder="Notas"
                  />
                  <button
                    type="button"
                    class="btn btn-sm btn-success mb-0"
                    :disabled="procesandoId === row.id"
                    @click="confirmarPago(row)"
                  >
                    {{ procesandoId === row.id ? "…" : "Confirmar pago" }}
                  </button>
                </div>
              </template>
              <span v-else class="text-xs text-muted">
                {{ row.payment_confirmed_at ? new Date(row.payment_confirmed_at).toLocaleString("es-BO") : "—" }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!items.length" class="text-sm text-muted mt-2">No hay pedidos en este filtro.</p>
      <p v-if="meta?.total != null" class="text-xs text-muted mt-2">Total resultados: {{ meta.total }}</p>
    </div>
  </div>
</template>
