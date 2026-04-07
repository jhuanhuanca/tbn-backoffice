<script setup>
import { onMounted, ref } from "vue";
import { fetchAdminWithdrawals, approveWithdrawal, rejectWithdrawal } from "@/services/admin";

const loading = ref(true);
const error = ref("");
const items = ref([]);
const meta = ref(null);

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await fetchAdminWithdrawals({ estado: "pendiente", per_page: 50 });
    items.value = data.data ?? data;
    meta.value = data.meta ?? null;
  } catch (e) {
    error.value = "No se pudieron cargar los retiros.";
  } finally {
    loading.value = false;
  }
}

onMounted(load);

async function aprobar(id) {
  try {
    await approveWithdrawal(id);
    await load();
  } catch {
    error.value = "Error al aprobar.";
  }
}

const notasRechazo = ref({});

async function rechazar(id) {
  try {
    await rejectWithdrawal(id, { notas_admin: notasRechazo.value[id] || null });
    notasRechazo.value[id] = "";
    await load();
  } catch {
    error.value = "Error al rechazar.";
  }
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="mb-3">
      <router-link to="/admin/dashboard" class="text-sm text-muted">&larr; Volver al panel empresa</router-link>
    </div>
    <h4 class="mb-3">Retiros pendientes</h4>
    <p v-if="error" class="text-danger text-sm">{{ error }}</p>
    <div v-if="loading" class="text-sm text-muted">Cargando…</div>
    <div v-else class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-xxs">ID</th>
            <th class="text-uppercase text-xxs">Socio</th>
            <th class="text-uppercase text-xxs">Monto</th>
            <th class="text-uppercase text-xxs">Estado</th>
            <th class="text-uppercase text-xxs"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="w in items" :key="w.id">
            <td class="text-sm">{{ w.id }}</td>
            <td class="text-sm">
              {{ w.user?.name }} <span class="text-muted">({{ w.user?.member_code }})</span>
            </td>
            <td class="text-sm">{{ w.monto }}</td>
            <td class="text-sm">{{ w.estado }}</td>
            <td class="text-sm text-end">
              <button type="button" class="btn btn-sm btn-success me-1" @click="aprobar(w.id)">Aprobar</button>
              <input
                v-model="notasRechazo[w.id]"
                type="text"
                class="form-control form-control-sm d-inline-block w-auto me-1"
                placeholder="Notas rechazo"
              />
              <button type="button" class="btn btn-sm btn-outline-danger" @click="rechazar(w.id)">Rechazar</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!items.length" class="text-sm text-muted mt-2">No hay retiros pendientes.</p>
    </div>
  </div>
</template>
