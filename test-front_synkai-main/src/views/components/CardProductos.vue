<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useStore } from "vuex";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import { fetchProductsCatalog, createOrder, fetchProfile } from "@/services/me";
import { useMlmLiveRefresh } from "@/composables/useMlmLiveRefresh";

import producto1 from "@/assets/img/productos/1.png";
import producto2 from "@/assets/img/productos/2.png";
import producto3 from "@/assets/img/productos/3.png";
import producto4 from "@/assets/img/productos/4.png";
import producto5 from "@/assets/img/productos/5.png";
import producto6 from "@/assets/img/productos/6.png";
import MlmPaymentMethodPanel from "@/components/MlmPaymentMethodPanel.vue";

const FALLBACK_IMAGES = [producto1, producto2, producto3, producto4, producto5, producto6];

const store = useStore();

async function syncProfilePv() {
  if (!localStorage.getItem("token")) return;
  try {
    const profile = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: profile,
      token: localStorage.getItem("token"),
    });
  } catch {
    /* mantiene último PV en store */
  }
}

useMlmLiveRefresh(syncProfilePv, 12000);

const loading = ref(true);
const loadError = ref("");
const productos = ref([]);
const carrito = ref([]);
const checkoutLoading = ref(false);
const checkoutMsg = ref("");
const checkoutErr = ref("");
const paymentSettlement = ref("immediate");
const paymentMethodOffline = ref("efectivo");

// Nuevas propiedades para el modal de imagen
const modalOpen = ref(false);
const selectedProducto = ref(null);
const cardFlipped = ref(false);

const cartStorageKey = computed(() => {
  const id = store.state.auth.user?.id;
  return id ? `mlm_cart_${id}` : "mlm_cart_guest";
});

const userPv = computed(() => {
  const u = store.state.auth.user;
  if (u?.monthly_qualifying_pv != null) return Number(u.monthly_qualifying_pv);
  return null;
});

const pvPedido = computed(() =>
  carrito.value.reduce((sum, it) => sum + Number(it.pv_points || 0) * Number(it.cantidad || 0), 0)
);

const pvCatalogo = computed(() =>
  productos.value.reduce((sum, p) => sum + Number(p.puntosValor || 0), 0)
);

const totalProductosCatalogo = computed(() => productos.value.length);

function imagenFor(product, index) {
  if (product.image_url) return product.image_url;
  return FALLBACK_IMAGES[index % FALLBACK_IMAGES.length];
}

function mapApiProduct(p, index) {
  const precioSocio = Number(p.price);
  const precioClienteRaw = p.price_cliente_preferente != null ? Number(p.price_cliente_preferente) : precioSocio * 1.2;
  const precioCliente = Math.round(precioClienteRaw * 100) / 100;
  return {
    id: p.id,
    product_id: p.id,
    nombre: p.name,
    descripcion: p.description || "—",
    imagen: imagenFor(p, index),
    precioCliente,
    precioSocio,
    puntosValor: Number(p.pv_points || 0),
    categoria: p.category?.name || "General",
    esEstatico: false,
    keyCatalogo: `api-${p.id}`,
  };
}

async function cargarProductos() {
  loading.value = true;
  loadError.value = "";
  try {
    const res = await fetchProductsCatalog();
    const rows = res.data || [];
    productos.value = rows.map((p, i) => mapApiProduct(p, i));
  } catch {
    loadError.value = "No se pudo cargar el catálogo. ¿Sesión iniciada y API disponible?";
    productos.value = [];
  } finally {
    loading.value = false;
  }
}

function cartKeyForLine(item) {
  if (item.key) return item.key;
  if (item.product_id != null && item.product_id > 0) return `api-${item.product_id}`;
  if (item.esEstatico && item.localId != null) return `static-${item.localId}`;
  return `legacy-${item.product_id ?? "x"}`;
}

function loadCartFromStorage() {
  try {
    const raw = localStorage.getItem(cartStorageKey.value);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) {
        carrito.value = parsed.map((row) => ({
          ...row,
          key: cartKeyForLine(row),
        }));
      }
    }
  } catch {
    carrito.value = [];
  }
}

function cerrarModal() {
  modalOpen.value = false;
  cardFlipped.value = false;
  selectedProducto.value = null;
}

function onBackgroundClick(e) {
  if (e.target.classList.contains("modal-backdrop-custom")) {
    cerrarModal();
  }
}

// Nueva función para abrir modal con producto
function abrirModalProducto(producto) {
  selectedProducto.value = producto;
  modalOpen.value = true;
  cardFlipped.value = false;
}

// Nueva función para girar la tarjeta
function girarTarjeta() {
  cardFlipped.value = !cardFlipped.value;
}

// ... resto del código ...
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Catálogo de productos</h4>
            <p class="mb-1 text-sm text-secondary">
              Solo productos reales del catálogo. Pago inmediato (simulado) o pendiente según método; la empresa confirma
              transferencias / QR en administración.
            </p>
            <p v-if="userPv != null" class="mb-0 text-sm">
              <strong>Tu PV mensual (calificación):</strong>
              {{ userPv.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV
            </p>
            <p v-if="carrito.length" class="mb-0 text-sm text-primary mt-1">
              <strong>PV del carrito actual:</strong> {{ pvPedido.toLocaleString("es-BO") }} PV
            </p>
            <p v-if="loadError" class="text-danger text-sm mt-2 mb-0">{{ loadError }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Total productos"
          :value="String(totalProductosCatalogo)"
          description="<span class='text-sm font-weight-bolder text-primary'>Catálogo API</span>"
          :icon="{
            component: 'ni ni-box-2',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="PV catálogo"
          :value="String(pvCatalogo.toLocaleString('es-BO'))"
          description="<span class='text-sm font-weight-bolder text-info'>Suma PV</span>"
          :icon="{
            component: 'ni ni-chart-bar-32',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="PV carrito"
          :value="String(pvPedido.toLocaleString('es-BO'))"
          description="<span class='text-sm font-weight-bolder text-success'>Acumulado</span>"
          :icon="{
            component: 'ni ni-money-coins',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="En carrito"
          :value="String(carrito.length)"
          description="<span class='text-sm font-weight-bolder text-warning'>Referencias</span>"
          :icon="{
            component: 'ni ni-cart',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div v-if="loading" class="text-center text-muted py-4">Cargando catálogo…</div>

    <div v-else class="row mt-2">
      <div class="col-12 mb-3">
        <h5 class="text-dark font-weight-bolder mb-1">Catálogo de productos</h5>
        <p v-if="!productos.length" class="text-xs text-secondary mb-0">
          No hay productos activos en el servidor. Carga el catálogo desde administración.
        </p>
      </div>
      <div
        v-for="producto in productos"
        :key="producto.keyCatalogo"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100 overflow-hidden card-producto">
          <div class="position-relative cursor-pointer" @click="abrirModalProducto(producto)">
            <img
              :src="producto.imagen"
              :alt="producto.nombre"
              class="card-img-top w-100"
              style="height: 200px; object-fit: cover; cursor: pointer;"
              loading="lazy"
            />
            <span class="badge badge-sm bg-gradient-warning position-absolute top-0 end-0 m-2">
              {{ producto.puntosValor }} PV
            </span>
            <span class="badge badge-sm bg-gradient-dark position-absolute top-0 start-0 m-2 opacity-90">
              {{ producto.categoria }}
            </span>
          </div>
          <div class="card-body p-3 d-flex flex-column">
            <h6 class="mb-2 text-dark font-weight-bolder text-sm">{{ producto.nombre }}</h6>
            <p class="text-xs text-secondary mb-3 flex-grow-1" style="min-height: 2.5rem">
              {{ producto.descripcion }}
            </p>
            <div class="mb-2">
              <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-1">
                <div>
                  <span class="text-xxs text-secondary d-block">Precio público</span>
                  <span class="text-sm text-secondary text-decoration-line-through">{{
                    formatearPrecio(producto.precioCliente)
                  }}</span>
                </div>
                <div class="text-end">
                  <span class="text-xxs text-primary font-weight-bolder d-block">Precio socio</span>
                  <span class="text-sm font-weight-bolder text-primary">{{
                    formatearPrecio(producto.precioSocio)
                  }}</span>
                </div>
              </div>
            </div>
            <div class="mt-auto pt-2">
              <argon-button
                color="primary"
                variant="gradient"
                size="sm"
                class="w-100"
                @click="añadirAlCarrito(producto)"
              >
                <i class="ni ni-cart me-1" aria-hidden="true"></i>
                Añadir al carrito
              </argon-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="carrito.length > 0" class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Carrito y checkout</h6>
            <p class="text-xs text-secondary mt-1 mb-0">
              Al confirmar se crea el pedido en el servidor; los PV se integran en tu historial MLM.
            </p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    Cantidad
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    Subtotal
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    PV
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in carrito" :key="item.key">
                  <td>
                    <div class="d-flex align-items-center px-2 py-1">
                      <img
                        :src="item.imagen"
                        :alt="item.nombre"
                        class="avatar avatar-sm rounded me-2"
                        style="object-fit: cover"
                      />
                      <span class="text-sm font-weight-bold">{{ item.nombre }}</span>
                    </div>
                  </td>
                  <td class="align-middle text-center">
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="menos(item.key)">
                        −
                      </button>
                      <span class="btn btn-sm btn-light disabled text-dark">{{ item.cantidad }}</span>
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="mas(item.key)">
                        +
                      </button>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm font-weight-bold">
                    {{ formatearPrecio(item.precioSocio * item.cantidad) }}
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-warning"
                      >{{ item.pv_points * item.cantidad }} PV</span
                    >
                  </td>
                  <td class="align-middle text-end">
                    <button type="button" class="btn btn-link text-danger text-sm p-0" @click="quitar(item.key)">
                      Quitar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3">
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Forma de pago</label>
                <select v-model="paymentSettlement" class="form-control form-control-sm">
                  <option value="immediate">Inmediato (simulado en sistema)</option>
                  <option value="manual">Efectivo / QR / transferencia (pendiente en empresa)</option>
                </select>
              </div>
              <div v-if="paymentSettlement === 'manual'" class="col-12">
                <label class="form-label text-xs text-muted mb-1">Método de pago (pendiente de confirmación)</label>
                <select v-model="paymentMethodOffline" class="form-control form-control-sm mb-2">
                  <option value="efectivo">Efectivo</option>
                  <option value="qr">QR</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="tarjeta">Tarjeta (referencia)</option>
                  <option value="otro">Otro</option>
                </select>
                <MlmPaymentMethodPanel
                  v-model="paymentMethodOffline"
                  :show-method-select="false"
                  title="Instrucciones de pago y entrega"
                />
              </div>
            </div>

            <!-- Botón cerrar -->
            <button
              type="button"
              class="btn-close position-absolute"
              style="top: 10px; right: 10px; z-index: 10;"
              @click="cerrarModal"
              aria-label="Close"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- ... resto del template ... -->
  </div>
</template>

<style scoped>
.card-producto {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-producto:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
}
.text-xxs {
  font-size: 0.65rem;
}
.cursor-pointer {
  cursor: pointer;
}
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.flip-container {
  perspective: 1000px;
}
.flip-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}
</style>
