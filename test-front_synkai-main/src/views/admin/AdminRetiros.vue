<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { fetchAdminWithdrawals, approveWithdrawal, rejectWithdrawal } from "@/services/admin";

const loading = ref(true);
const error = ref("");
const items = ref([]);
const meta = ref(null);
const filtroEstado = ref("pendiente");
const notasRechazo = ref({});

function normalizeWithdrawalsResponse(res) {
  const body = res?.data;
  if (!body) {
    return { rows: [], meta: null };
  }
  if (Array.isArray(body.data)) {
    return {
      rows: body.data,
      meta: {
        current_page: body.current_page,
        last_page: body.last_page,
        total: body.total,
        per_page: body.per_page,
      },
    };
  }
  if (Array.isArray(body)) {
    return { rows: body, meta: null };
  }
  return { rows: [], meta: null };
}

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const res = await fetchAdminWithdrawals({
      estado: filtroEstado.value,
      per_page: 50,
    });
    const { rows, meta: m } = normalizeWithdrawalsResponse(res);
    items.value = rows;
    meta.value = m;
  } catch (e) {
    const msg = e?.response?.data?.message || e?.message || "";
    error.value = msg
      ? `No se pudieron cargar los retiros: ${msg}`
      : "No se pudieron cargar los retiros (¿sesión admin o permisos?).";
    items.value = [];
    meta.value = null;
  } finally {
    loading.value = false;
  }
}

watch(filtroEstado, () => load());

onMounted(load);

function formatBs(value) {
  const n = Number(value);
  if (Number.isNaN(n)) return String(value ?? "—");
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(n);
}

const estadoBadgeClass = computed(() => (estado) => {
  const m = {
    pendiente: "bg-gradient-warning",
    aprobado: "bg-gradient-info",
    completado: "bg-gradient-success",
    rechazado: "bg-gradient-danger",
  };
  return m[estado] || "bg-secondary";
});

async function aprobar(id) {
  error.value = "";
  try {
    await approveWithdrawal(id);
    await load();
  } catch (e) {
    error.value = e?.response?.data?.message || "Error al aprobar.";
  }
}

async function rechazar(id) {
  error.value = "";
  try {
    await rejectWithdrawal(id, { notas_admin: notasRechazo.value[id] || null });
    notasRechazo.value[id] = "";
    await load();
  } catch (e) {
    error.value = e?.response?.data?.message || "Error al rechazar.";
  }
}
</script>

<template>
  <div class="py-4 container-fluid admin-retiros">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <router-link to="/admin/dashboard" class="text-sm text-muted text-decoration-none d-inline-block mb-1">
          &larr; Volver al panel empresa
        </router-link>
        <h4 class="mb-0 text-dark font-weight-bolder">Gestión de retiros</h4>
        <p class="text-sm text-secondary mb-0">
          Aprueba o rechaza solicitudes de socios. El saldo quedó retenido al solicitar.
        </p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <label class="text-xs text-secondary mb-0 me-1">Estado</label>
        <select v-model="filtroEstado" class="form-select form-select-sm admin-retiros__filter">
          <option value="pendiente">Pendientes</option>
          <option value="aprobado">Aprobados</option>
          <option value="completado">Completados</option>
          <option value="rechazado">Rechazados</option>
          <option value="all">Todos</option>
        </select>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger text-white mb-3" role="alert">
      {{ error }}
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-gradient-dark py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <span class="text-white font-weight-bold mb-0">Listado</span>
          <span v-if="meta?.total != null" class="text-white text-sm opacity-8">
            {{ meta.total }} registro(s)
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="p-5 text-center text-muted text-sm">Cargando…</div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-items-center mb-0">
            <thead class="bg-light">
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-4">ID</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Socio</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Monto</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Estado</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end pe-4">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in items" :key="w.id">
                <td class="ps-4 text-sm font-weight-bold">{{ w.id }}</td>
                <td class="text-sm">
                  <div class="font-weight-bold text-dark">{{ w.user?.name || "—" }}</div>
                  <div class="text-xs text-muted">
                    {{ w.user?.email }} · código {{ w.user?.member_code || "—" }}
                  </div>
                </td>
                <td class="text-sm font-weight-bold text-dark">{{ formatBs(w.monto) }}</td>
                <td>
                  <span
                    class="badge text-white text-xxs px-2 py-1"
                    :class="estadoBadgeClass(w.estado)"
                  >
                    {{ w.estado }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <template v-if="w.estado === 'pendiente'">
                    <button
                      type="button"
                      class="btn btn-sm btn-success me-1 mb-1"
                      @click="aprobar(w.id)"
                    >
                      Aprobar
                    </button>
                    <div class="d-inline-flex flex-wrap align-items-center gap-1 justify-content-end">
                      <input
                        v-model="notasRechazo[w.id]"
                        type="text"
                        class="form-control form-control-sm"
                        style="min-width: 140px; max-width: 200px"
                        placeholder="Motivo rechazo (opc.)"
                      />
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger mb-1"
                        @click="rechazar(w.id)"
                      >
                        Rechazar
                      </button>
                    </div>
                  </template>
                  <span v-else class="text-xs text-muted">—</span>
                </td>
              </tr>
              <tr v-if="!items.length">
                <td colspan="5" class="text-center text-muted py-5 text-sm">
                  No hay retiros con este filtro.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-retiros__filter {
  min-width: 160px;
}
.admin-retiros .table thead th {
  border-bottom: 1px solid #e9ecef;
}
</style>
