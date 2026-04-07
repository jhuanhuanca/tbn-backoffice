<script setup>
import { onMounted, ref } from "vue";
import { fetchCommissionSummary, fetchLeadershipMonth } from "@/services/admin";

const periodKey = ref("");
const periodType = ref("");
const loading = ref(false);
const error = ref("");
const summary = ref([]);
const leadership = ref(null);

async function cargarResumen() {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await fetchCommissionSummary({
      period_key: periodKey.value || undefined,
      period_type: periodType.value || undefined,
    });
    summary.value = data.data ?? [];
  } catch (e) {
    error.value = "Error al cargar el resumen de comisiones.";
  } finally {
    loading.value = false;
  }
}

async function cargarLiderazgo() {
  if (!periodKey.value || !/^\d{4}-\d{2}$/.test(periodKey.value)) {
    leadership.value = null;
    return;
  }
  try {
    const { data } = await fetchLeadershipMonth(periodKey.value);
    leadership.value = data;
  } catch {
    leadership.value = null;
  }
}

onMounted(async () => {
  const d = new Date();
  periodKey.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
  await cargarResumen();
  await cargarLiderazgo();
});

async function aplicarFiltros() {
  await cargarResumen();
  await cargarLiderazgo();
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="mb-3">
      <router-link to="/admin/dashboard" class="text-sm text-muted">&larr; Volver al panel empresa</router-link>
    </div>
    <h4 class="mb-3">Reconciliación y liderazgo</h4>
    <p class="text-sm text-muted">
      Agregados por tipo desde <code>commission_events</code>. Liderazgo mensual (YYYY-MM).
    </p>
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label text-sm">period_key</label>
        <input v-model="periodKey" class="form-control form-control-sm" placeholder="2026-03" />
      </div>
      <div class="col-md-3">
        <label class="form-label text-sm">period_type (opcional)</label>
        <input v-model="periodType" class="form-control form-control-sm" placeholder="weekly / monthly" />
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="button" class="btn btn-sm btn-primary" @click="aplicarFiltros">Aplicar</button>
      </div>
    </div>
    <p v-if="error" class="text-danger text-sm">{{ error }}</p>
    <div v-if="loading" class="text-sm text-muted">Cargando…</div>
    <div v-else class="table-responsive mb-4">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>period_key</th>
            <th>period_type</th>
            <th>Eventos</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in summary" :key="idx">
            <td>{{ row.type }}</td>
            <td>{{ row.period_key }}</td>
            <td>{{ row.period_type }}</td>
            <td>{{ row.events_count }}</td>
            <td>{{ row.total_amount }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <h6 class="mb-2">Bono liderazgo (mes {{ periodKey }})</h6>
    <div v-if="leadership" class="text-sm">
      <p>Total: <strong>{{ leadership.total_amount }}</strong> — Eventos: {{ leadership.events_count }}</p>
      <ul class="list-unstyled">
        <li v-for="(b, i) in leadership.by_beneficiary" :key="i">
          Usuario {{ b.user_id }}: {{ b.total }} ({{ b.events }} evt.)
        </li>
      </ul>
    </div>
    <p v-else class="text-sm text-muted">Sin datos de liderazgo para el mes indicado.</p>
  </div>
</template>
