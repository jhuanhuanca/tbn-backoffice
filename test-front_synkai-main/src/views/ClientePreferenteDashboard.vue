<script setup>
import { ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import ArgonButton from "@/components/ArgonButton.vue";
import MlmPaymentMethodPanel from "@/components/MlmPaymentMethodPanel.vue";
import { fetchProductsCatalog, createOrder, fetchOrders, fetchProfile } from "@/services/me";

const store = useStore();
const loading = ref(true);
const err = ref("");
const productos = ref([]);
const orders = ref([]);
const carrito = ref([]);
const checkoutLoading = ref(false);
const checkoutMsg = ref("");
const paymentMethod = ref("transferencia");

const paymentSettlement = computed(() =>
  ["tarjeta", "online"].includes(paymentMethod.value) ? "immediate" : "manual"
);

const totalCarrito = computed(() =>
  carrito.value.reduce((s, it) => s + Number(it.precio) * Number(it.cantidad), 0)
);

function addToCart(p) {
  const precio = Number(p.price ?? p.precio_mostrar ?? 0);
  const ex = carrito.value.find((x) => x.id === p.id);
  if (ex) {
    ex.cantidad += 1;
  } else {
    carrito.value.push({
      id: p.id,
      name: p.name,
      precio,
      pv_points: Number(p.pv_points || 0),
      cantidad: 1,
    });
  }
}

function removeOne(id) {
  const ex = carrito.value.find((x) => x.id === id);
  if (!ex) return;
  ex.cantidad -= 1;
  if (ex.cantidad <= 0) {
    carrito.value = carrito.value.filter((x) => x.id !== id);
  }
}

async function load() {
  loading.value = true;
  err.value = "";
  try {
    const [cat, ord, prof] = await Promise.all([
      fetchProductsCatalog(),
      fetchOrders({ per_page: 10 }),
      fetchProfile(),
    ]);
    productos.value = cat.data || [];
    orders.value = ord.data || [];
    await store.dispatch("auth/setAuth", {
      user: prof,
      token: localStorage.getItem("token"),
    });
  } catch {
    err.value = "No se pudo cargar el catálogo o tus pedidos.";
  } finally {
    loading.value = false;
  }
}

onMounted(load);

async function checkout() {
  if (!carrito.value.length) return;
  checkoutLoading.value = true;
  checkoutMsg.value = "";
  try {
    const order = await createOrder({
      tipo: "producto",
      payment_settlement: paymentSettlement.value,
      payment_method: paymentMethod.value,
      items: carrito.value.map((it) => ({
        product_id: it.id,
        cantidad: it.cantidad,
      })),
    });
    carrito.value = [];
    checkoutMsg.value =
      order?.estado === "pendiente_pago"
        ? "Pedido registrado como pendiente de pago. La empresa confirmará según tu método (transferencia, QR, etc.)."
        : "Pedido registrado. Gracias por tu compra.";
    await load();
  } catch (e) {
    checkoutMsg.value = e.response?.data?.message || "Error al crear el pedido.";
  } finally {
    checkoutLoading.value = false;
  }
}

function formatBs(n) {
  return new Intl.NumberFormat("es-BO", { style: "currency", currency: "BOB" }).format(Number(n) || 0);
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <h4 class="text-dark font-weight-bolder">Cliente preferente</h4>
        <p class="text-sm text-secondary mb-0">
          Precios mostrados: <strong>precio de cliente</strong>. Tu patrocinador recibe en su billetera el bono de
          venta directa por la diferencia con el precio socio.
        </p>
      </div>
    </div>

    <div v-if="err" class="alert alert-warning text-white">{{ err }}</div>
    <div v-if="checkoutMsg" class="alert alert-success text-white">{{ checkoutMsg }}</div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0">Productos</h6>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-muted text-sm">Cargando…</div>
            <div v-else class="row g-3">
              <div v-for="p in productos" :key="p.id" class="col-md-6">
                <div class="card h-100 border">
                  <div class="card-body p-3">
                    <h6 class="mb-1">{{ p.name }}</h6>
                    <p class="text-xs text-muted mb-2 text-truncate-3" style="min-height: 2.5rem">
                      {{ p.description }}
                    </p>
                    <p class="text-sm font-weight-bold mb-2">{{ formatBs(p.price) }}</p>
                    <argon-button color="success" size="sm" @click="addToCart(p)">Agregar</argon-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header pb-0">
            <h6 class="mb-0">Últimas compras</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table mb-0">
                <thead>
                  <tr>
                    <th class="text-xs">#</th>
                    <th class="text-xs">Estado</th>
                    <th class="text-xs">Total</th>
                    <th class="text-xs">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="o in orders" :key="o.id">
                    <td class="text-sm">{{ o.id }}</td>
                    <td class="text-sm">{{ o.estado }}</td>
                    <td class="text-sm">{{ formatBs(o.total) }}</td>
                    <td class="text-sm">{{ o.completed_at || o.created_at }}</td>
                  </tr>
                  <tr v-if="!orders.length">
                    <td colspan="4" class="text-center text-muted text-sm py-3">Aún no hay compras.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm position-sticky" style="top: 1rem">
          <div class="card-header pb-0">
            <h6 class="mb-0">Carrito</h6>
          </div>
          <div class="card-body">
            <ul v-if="carrito.length" class="list-group list-group-flush mb-3">
              <li
                v-for="it in carrito"
                :key="it.id"
                class="list-group-item d-flex justify-content-between align-items-center px-0"
              >
                <span class="text-sm"
                  >{{ it.name }} × {{ it.cantidad }} · {{ formatBs(it.precio * it.cantidad) }}</span
                >
                <button type="button" class="btn btn-link text-danger btn-sm p-0" @click="removeOne(it.id)">−</button>
              </li>
            </ul>
            <p v-else class="text-sm text-muted">Vacío</p>
            <p v-if="carrito.length" class="text-sm font-weight-bold">Total: {{ formatBs(totalCarrito) }}</p>
            <div v-if="carrito.length" class="mb-3">
              <MlmPaymentMethodPanel
                v-model="paymentMethod"
                :show-method-select="true"
                title="Pago y entrega"
              />
            </div>
            <argon-button
              color="dark"
              class="w-100"
              :disabled="!carrito.length || checkoutLoading"
              @click="checkout"
            >
              {{ checkoutLoading ? "Procesando…" : "Confirmar pedido" }}
            </argon-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
