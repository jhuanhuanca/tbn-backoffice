<script setup>
import { onMounted, ref, computed } from "vue";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import {
  fetchAdminPackages,
  createAdminPackage,
  updateAdminPackage,
  deleteAdminPackage,
} from "@/services/admin";

const loading = ref(true);
const saving = ref(false);
const error = ref("");
const items = ref([]);

const form = ref({
  id: null,
  slug: "",
  name: "",
  price: "",
  pv_points: "",
  commissionable_amount: "",
  estado: "activo",
});

const isEditing = computed(() => form.value.id != null);

function resetForm() {
  form.value = {
    id: null,
    slug: "",
    name: "",
    price: "",
    pv_points: "",
    commissionable_amount: "",
    estado: "activo",
  };
}

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const { data } = await fetchAdminPackages();
    items.value = data?.data ?? [];
  } catch {
    error.value = "No se pudieron cargar los paquetes.";
  } finally {
    loading.value = false;
  }
}

function editar(p) {
  form.value = {
    id: p.id,
    slug: p.slug ?? "",
    name: p.name ?? "",
    price: String(p.price ?? ""),
    pv_points: String(p.pv_points ?? ""),
    commissionable_amount: p.commissionable_amount != null ? String(p.commissionable_amount) : "",
    estado: p.estado ?? "activo",
  };
}

async function guardar() {
  saving.value = true;
  error.value = "";
  try {
    const payload = {
      slug: form.value.slug.trim().toLowerCase(),
      name: form.value.name.trim(),
      price: parseFloat(form.value.price),
      pv_points: parseFloat(form.value.pv_points),
      commissionable_amount: form.value.commissionable_amount
        ? parseFloat(form.value.commissionable_amount)
        : null,
      estado: form.value.estado,
    };
    if (isEditing.value) {
      await updateAdminPackage(form.value.id, {
        name: payload.name,
        price: payload.price,
        pv_points: payload.pv_points,
        commissionable_amount: payload.commissionable_amount,
        estado: payload.estado,
      });
    } else {
      await createAdminPackage(payload);
    }
    resetForm();
    await load();
  } catch (e) {
    error.value =
      e.response?.data?.message || "Error al guardar. El slug debe ser único (solo minúsculas y guiones).";
  } finally {
    saving.value = false;
  }
}

async function desactivar(p) {
  if (!confirm(`¿Desactivar paquete "${p.name}"? No aparecerá en inscripción ni catálogo público.`)) return;
  try {
    await deleteAdminPackage(p.id);
    await load();
  } catch {
    error.value = "No se pudo desactivar.";
  }
}

onMounted(load);
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="mb-3">
      <router-link to="/admin/dashboard" class="text-sm text-muted">&larr; Volver al panel empresa</router-link>
    </div>
    <h4 class="mb-3">Paquetes de inscripción y venta</h4>
    <p class="text-sm text-muted">
      Se usan en <strong>registro</strong> (<code>registration_package_id</code>) y en pedidos tipo paquete. El
      monto comisionable opcional alimenta el bono inicio rápido (BIR) si está configurado.
    </p>
    <p v-if="error" class="text-danger text-sm">{{ error }}</p>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent">
        <h6 class="mb-0">{{ isEditing ? "Editar paquete" : "Nuevo paquete" }}</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label text-sm">Slug (único)</label>
            <argon-input
              v-model="form.slug"
              type="text"
              placeholder="ej. fundador"
              :disabled="isEditing"
            />
            <span v-if="isEditing" class="text-xxs text-muted">El slug no se edita; crea otro registro si cambia.</span>
          </div>
          <div class="col-md-8">
            <label class="form-label text-sm">Nombre visible</label>
            <argon-input v-model="form.name" type="text" />
          </div>
          <div class="col-md-3">
            <label class="form-label text-sm">Precio (BOB)</label>
            <argon-input v-model="form.price" type="number" step="0.01" min="0" />
          </div>
          <div class="col-md-3">
            <label class="form-label text-sm">PV</label>
            <argon-input v-model="form.pv_points" type="number" step="0.01" min="0" />
          </div>
          <div class="col-md-3">
            <label class="form-label text-sm">Monto comisionable (opc.)</label>
            <argon-input v-model="form.commissionable_amount" type="number" step="0.01" min="0" />
          </div>
          <div class="col-md-3">
            <label class="form-label text-sm">Estado</label>
            <select v-model="form.estado" class="form-select">
              <option value="activo">activo</option>
              <option value="inactivo">inactivo</option>
            </select>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <argon-button color="primary" variant="gradient" :disabled="saving" @click="guardar">
            {{ saving ? "Guardando…" : isEditing ? "Actualizar" : "Crear" }}
          </argon-button>
          <button v-if="isEditing" type="button" class="btn btn-sm btn-outline-secondary" @click="resetForm">
            Cancelar edición
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-sm text-muted">Cargando…</div>
    <div v-else class="table-responsive">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-xxs">Slug</th>
            <th class="text-uppercase text-xxs">Nombre</th>
            <th class="text-uppercase text-xxs">Precio</th>
            <th class="text-uppercase text-xxs">PV</th>
            <th class="text-uppercase text-xxs">Estado</th>
            <th class="text-uppercase text-xxs"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in items" :key="p.id">
            <td class="text-sm font-monospace">{{ p.slug }}</td>
            <td class="text-sm">{{ p.name }}</td>
            <td class="text-sm">{{ p.price }}</td>
            <td class="text-sm">{{ p.pv_points }}</td>
            <td class="text-sm">
              <span class="badge" :class="p.estado === 'activo' ? 'bg-success' : 'bg-secondary'">{{
                p.estado
              }}</span>
            </td>
            <td class="text-end text-sm">
              <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="editar(p)">Editar</button>
              <button
                v-if="p.estado === 'activo'"
                type="button"
                class="btn btn-sm btn-outline-danger"
                @click="desactivar(p)"
              >
                Desactivar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
