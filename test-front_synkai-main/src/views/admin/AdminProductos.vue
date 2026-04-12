<script setup>
import { onMounted, ref, computed } from "vue";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import {
  fetchAdminProducts,
  fetchAdminCategories,
  createAdminProduct,
  updateAdminProduct,
  deleteAdminProduct,
} from "@/services/admin";

const loading = ref(true);
const saving = ref(false);
const error = ref("");
const items = ref([]);
const categories = ref([]);

const form = ref({
  id: null,
  name: "",
  description: "",
  price: "",
  price_cliente_preferente: "",
  stock: 0,
  image_url: "",
  category_id: "",
  pv_points: "",
  estado: "activo",
});

const isEditing = computed(() => form.value.id != null);

function resetForm() {
  form.value = {
    id: null,
    name: "",
    description: "",
    price: "",
    price_cliente_preferente: "",
    stock: 0,
    image_url: "",
    category_id: "",
    pv_points: "",
    estado: "activo",
  };
}

async function load() {
  loading.value = true;
  error.value = "";
  try {
    const [catRes, prodRes] = await Promise.all([fetchAdminCategories(), fetchAdminProducts()]);
    categories.value = catRes.data?.data ?? [];
    items.value = prodRes.data?.data ?? [];
  } catch {
    error.value = "No se pudieron cargar productos o categorías.";
  } finally {
    loading.value = false;
  }
}

function editar(p) {
  form.value = {
    id: p.id,
    name: p.name ?? "",
    description: p.description ?? "",
    price: String(p.price ?? ""),
    price_cliente_preferente:
      p.price_cliente_preferente != null && p.price_cliente_preferente !== ""
        ? String(p.price_cliente_preferente)
        : "",
    stock: p.stock ?? 0,
    image_url: p.image_url ?? "",
    category_id: p.category_id ? String(p.category_id) : "",
    pv_points: String(p.pv_points ?? ""),
    estado: p.estado ?? "activo",
  };
}

async function guardar() {
  saving.value = true;
  error.value = "";
  try {
    const payload = {
      name: form.value.name.trim(),
      description: form.value.description?.trim() || null,
      price: parseFloat(form.value.price),
      stock: parseInt(String(form.value.stock), 10) || 0,
      image_url: form.value.image_url?.trim() || null,
      category_id: form.value.category_id ? parseInt(form.value.category_id, 10) : null,
      pv_points: parseFloat(form.value.pv_points),
      estado: form.value.estado,
    };
    const pcp = form.value.price_cliente_preferente;
    if (pcp !== "" && pcp != null && !Number.isNaN(parseFloat(String(pcp)))) {
      payload.price_cliente_preferente = parseFloat(String(pcp));
    }
    if (isEditing.value) {
      await updateAdminProduct(form.value.id, payload);
    } else {
      await createAdminProduct(payload);
    }
    resetForm();
    await load();
  } catch (e) {
    error.value =
      e.response?.data?.message || "Error al guardar. Revisa los datos (precio, PV, etc.).";
  } finally {
    saving.value = false;
  }
}

async function desactivar(p) {
  if (!confirm(`¿Desactivar "${p.name}"? Seguirá en base de datos como inactivo.`)) return;
  try {
    await deleteAdminProduct(p.id);
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
    <h4 class="mb-3">Productos del catálogo</h4>
    <p class="text-sm text-muted">
      Los socios ven solo productos con estado <strong>activo</strong> en la tienda. El PV del ítem alimenta
      pedidos y calificación mensual. El <strong>precio cliente preferente</strong> es el que pagan los clientes
      preferentes; si lo dejas vacío al crear, el backend calcula uno por defecto (socio + 12&nbsp;%).
    </p>
    <p v-if="error" class="text-danger text-sm">{{ error }}</p>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent">
        <h6 class="mb-0">{{ isEditing ? "Editar producto" : "Nuevo producto" }}</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-sm">Nombre</label>
            <argon-input v-model="form.name" type="text" placeholder="Nombre" />
          </div>
          <div class="col-md-2">
            <label class="form-label text-sm">Precio socio (BOB)</label>
            <argon-input v-model="form.price" type="number" step="0.01" min="0" />
          </div>
          <div class="col-md-2">
            <label class="form-label text-sm">Cliente pref. (opc.)</label>
            <argon-input
              v-model="form.price_cliente_preferente"
              type="number"
              step="0.01"
              min="0"
              placeholder="Auto"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label text-sm">PV</label>
            <argon-input v-model="form.pv_points" type="number" step="0.01" min="0" />
          </div>
          <div class="col-md-4">
            <label class="form-label text-sm">Categoría</label>
            <select v-model="form.category_id" class="form-select">
              <option value="">— Sin categoría —</option>
              <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label text-sm">Stock</label>
            <argon-input v-model="form.stock" type="number" min="0" />
          </div>
          <div class="col-md-3">
            <label class="form-label text-sm">Estado</label>
            <select v-model="form.estado" class="form-select">
              <option value="activo">activo</option>
              <option value="inactivo">inactivo</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label text-sm">Descripción</label>
            <textarea v-model="form.description" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label text-sm">URL imagen (opcional)</label>
            <argon-input v-model="form.image_url" type="text" placeholder="https://..." />
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
            <th class="text-uppercase text-xxs">ID</th>
            <th class="text-uppercase text-xxs">Nombre</th>
            <th class="text-uppercase text-xxs">Precio socio</th>
            <th class="text-uppercase text-xxs">Cliente pref.</th>
            <th class="text-uppercase text-xxs">PV</th>
            <th class="text-uppercase text-xxs">Estado</th>
            <th class="text-uppercase text-xxs"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in items" :key="p.id">
            <td class="text-sm">{{ p.id }}</td>
            <td class="text-sm">{{ p.name }}</td>
            <td class="text-sm">{{ p.price }}</td>
            <td class="text-sm">{{ p.price_cliente_preferente ?? "—" }}</td>
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
