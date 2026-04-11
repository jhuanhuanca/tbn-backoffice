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

const FALLBACK_IMAGES = [producto1, producto2, producto3, producto4, producto5, producto6];

/** Mostrador fijo (no se elimina); el pedido por API solo incluye líneas con product_id del servidor. */
const PRODUCTOS_ESTATICOS = [
  {
    localId: 1,
    nombre: "Pack Bienvenida Essential",
    descripcion: "Kit de inicio con productos estrella. Ideal para nuevos socios.",
    imagen: producto1,
    precioCliente: 149.0,
    precioSocio: 119.0,
    puntosValor: 120,
    categoria: "Packs",
  },
  {
    localId: 2,
    nombre: "Suplemento Vitalidad",
    descripcion: "Fórmula con vitaminas y minerales para energía y bienestar diario.",
    imagen: producto2,
    precioCliente: 89.0,
    precioSocio: 69.0,
    puntosValor: 70,
    categoria: "Nutrición",
  },
  {
    localId: 3,
    nombre: "Crema Facial Antiedad",
    descripcion: "Hidratación intensiva y efecto lifting. Dermatológicamente testada.",
    imagen: producto3,
    precioCliente: 65.0,
    precioSocio: 49.0,
    puntosValor: 50,
    categoria: "Cuidado personal",
  },
  {
    localId: 4,
    nombre: "Aceite Esencial Relax",
    descripcion: "Blend natural para aromaterapia y masajes. 100% puro.",
    imagen: producto4,
    precioCliente: 32.0,
    precioSocio: 24.0,
    puntosValor: 25,
    categoria: "Bienestar",
  },
  {
    localId: 5,
    nombre: "Barrita Proteína Chocolate",
    descripcion: "Snack saludable con 20g de proteína. Sin azúcares añadidos.",
    imagen: producto5,
    precioCliente: 28.0,
    precioSocio: 21.0,
    puntosValor: 20,
    categoria: "Nutrición",
  },
  {
    localId: 6,
    nombre: "Set Regalo Premium",
    descripcion: "Caja regalo con los 3 best sellers. Presentación exclusiva.",
    imagen: producto6,
    precioCliente: 199.0,
    precioSocio: 159.0,
    puntosValor: 160,
    categoria: "Packs",
  },
];

function mapEstatico(p) {
  return {
    ...p,
    product_id: null,
    esEstatico: true,
    keyCatalogo: `static-${p.localId}`,
  };
}

const productosEstaticosVista = PRODUCTOS_ESTATICOS.map(mapEstatico);

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
/** immediate = comisión/PV al instante (simulado); manual = empresa confirma en panel admin */
const paymentSettlement = ref("immediate");
const paymentMethodOffline = ref("efectivo");

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

const pvCatalogoEstatico = computed(() =>
  PRODUCTOS_ESTATICOS.reduce((sum, p) => sum + Number(p.puntosValor || 0), 0)
);

const pvCatalogoApi = computed(() =>
  productos.value.reduce((sum, p) => sum + Number(p.puntosValor || 0), 0)
);

const pvCatalogo = computed(() => pvCatalogoEstatico.value + pvCatalogoApi.value);

const totalProductosCatalogo = computed(
  () => PRODUCTOS_ESTATICOS.length + productos.value.length
);

function imagenFor(product, index) {
  if (product.image_url) return product.image_url;
  return FALLBACK_IMAGES[index % FALLBACK_IMAGES.length];
}

function mapApiProduct(p, index) {
  const precioSocio = Number(p.price);
  const precioCliente = Math.round(precioSocio * 1.2 * 100) / 100;
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

function persistCart() {
  try {
    localStorage.setItem(cartStorageKey.value, JSON.stringify(carrito.value));
  } catch {
    /* */
  }
}

watch(
  carrito,
  () => {
    persistCart();
  },
  { deep: true }
);

watch(cartStorageKey, () => {
  loadCartFromStorage();
});

onMounted(async () => {
  loadCartFromStorage();
  await cargarProductos();
});

function formatearPrecio(valor) {
  return new Intl.NumberFormat("es-BO", {
    style: "currency",
    currency: "BOB",
    minimumFractionDigits: 2,
  }).format(valor);
}

function claveCarritoDesdeProducto(producto) {
  if (producto.esEstatico) return `static-${producto.localId}`;
  return `api-${producto.product_id}`;
}

function añadirAlCarrito(producto) {
  const key = claveCarritoDesdeProducto(producto);
  const existe = carrito.value.find((p) => p.key === key);
  if (existe) {
    existe.cantidad += 1;
  } else {
    carrito.value.push({
      key,
      product_id: producto.product_id,
      esEstatico: !!producto.esEstatico,
      localId: producto.localId,
      nombre: producto.nombre,
      imagen: producto.imagen,
      precioSocio: producto.precioSocio,
      pv_points: producto.puntosValor,
      cantidad: 1,
    });
  }
}

function menos(key) {
  const i = carrito.value.findIndex((p) => p.key === key);
  if (i === -1) return;
  if (carrito.value[i].cantidad <= 1) {
    carrito.value.splice(i, 1);
  } else {
    carrito.value[i].cantidad -= 1;
  }
}

function mas(key) {
  const row = carrito.value.find((p) => p.key === key);
  if (row) row.cantidad += 1;
}

function quitar(key) {
  carrito.value = carrito.value.filter((p) => p.key !== key);
}

async function finalizarCompra() {
  checkoutMsg.value = "";
  checkoutErr.value = "";
  if (!carrito.value.length) return;
  if (!localStorage.getItem("token")) {
    checkoutErr.value = "Debes iniciar sesión para comprar.";
    return;
  }
  const lineasApi = carrito.value.filter((c) => c.product_id != null && c.product_id > 0);
  if (!lineasApi.length) {
    checkoutErr.value =
      "Solo se pueden pedir por el sistema los productos del catálogo en línea. Quita los de mostrador demo o añade artículos del API.";
    return;
  }
  checkoutLoading.value = true;
  try {
    const items = lineasApi.map((c) => ({
      product_id: c.product_id,
      cantidad: c.cantidad,
    }));
    const immediate = paymentSettlement.value === "immediate";
    const order = await createOrder({
      tipo: "producto",
      items,
      payment_settlement: immediate ? "immediate" : "manual",
      payment_method: immediate ? "tarjeta" : paymentMethodOffline.value,
    });
    carrito.value = [];
    persistCart();
    const profile = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: profile,
      token: localStorage.getItem("token"),
    });
    if (immediate && order?.estado === "completado") {
      checkoutMsg.value =
        "Pedido registrado. Los PV se acreditan según el procesamiento MLM (revisa tu PV mensual en el perfil).";
    } else {
      checkoutMsg.value =
        "Pedido registrado como pendiente de pago. La empresa lo confirmará (efectivo/QR/transferencia) y entonces se aplicarán PV y comisiones.";
    }
  } catch (e) {
    checkoutErr.value =
      e.response?.data?.message || "No se pudo completar el pedido. Inténtalo de nuevo.";
  } finally {
    checkoutLoading.value = false;
  }
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Catálogo de productos</h4>
            <p class="mb-1 text-sm text-secondary">
              Precios desde la base de datos. Puedes registrar pago inmediato (simulado) o pendiente (efectivo/QR) para
              que empresa confirme en administración.
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
          description="<span class='text-sm font-weight-bolder text-primary'>Demo + API</span>"
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

    <div class="row mt-4">
      <div class="col-12 mb-3">
        <h5 class="text-dark font-weight-bolder mb-1">Productos destacados (mostrador)</h5>
        <p class="text-xs text-secondary mb-0">
          Tarjetas fijas de referencia. El pedido en línea solo envía al servidor los del catálogo API.
        </p>
      </div>
      <div
        v-for="producto in productosEstaticosVista"
        :key="producto.keyCatalogo"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100 overflow-hidden card-producto">
          <div class="position-relative">
            <img
              :src="producto.imagen"
              :alt="producto.nombre"
              class="card-img-top w-100"
              style="height: 200px; object-fit: cover"
              loading="lazy"
            />
            <span class="badge badge-sm bg-gradient-dark position-absolute top-0 start-0 m-2 opacity-90">
              {{ producto.categoria }}
            </span>
            <span class="badge badge-sm bg-gradient-warning position-absolute top-0 end-0 m-2">
              {{ producto.puntosValor }} PV
            </span>
            <span class="badge badge-sm bg-gradient-secondary position-absolute bottom-0 end-0 m-2"> Demo </span>
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
                color="secondary"
                variant="gradient"
                size="sm"
                class="w-100"
                @click="añadirAlCarrito(producto)"
              >
                <i class="ni ni-cart me-1" aria-hidden="true"></i>
                Añadir (demo)
              </argon-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center text-muted py-4">Cargando catálogo en línea…</div>

    <div v-else class="row mt-2">
      <div class="col-12 mb-3">
        <h5 class="text-dark font-weight-bolder mb-1">Catálogo en línea (API)</h5>
        <p v-if="!productos.length" class="text-xs text-secondary mb-0">
          No hay productos en el servidor o no se pudo cargar. Los de mostrador siguen visibles arriba.
        </p>
      </div>
      <div
        v-for="producto in productos"
        :key="producto.keyCatalogo"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100 overflow-hidden card-producto">
          <div class="position-relative">
            <img
              :src="producto.imagen"
              :alt="producto.nombre"
              class="card-img-top w-100"
              style="height: 200px; object-fit: cover"
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
                      <span v-if="item.esEstatico" class="badge badge-xs bg-secondary ms-1">Demo</span>
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
              <div v-if="paymentSettlement === 'manual'" class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Detalle</label>
                <select v-model="paymentMethodOffline" class="form-control form-control-sm">
                  <option value="efectivo">Efectivo</option>
                  <option value="qr">QR</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
            </div>
            <p v-if="checkoutMsg" class="text-success text-sm">{{ checkoutMsg }}</p>
            <p v-if="checkoutErr" class="text-danger text-sm">{{ checkoutErr }}</p>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <span class="text-sm text-muted"
                >Total PV pedido: <strong>{{ pvPedido.toLocaleString("es-BO") }} PV</strong></span
              >
              <argon-button
                color="success"
                variant="gradient"
                size="md"
                :disabled="checkoutLoading"
                @click="finalizarCompra"
              >
                {{ checkoutLoading ? "Procesando…" : "Confirmar pedido" }}
              </argon-button>
            </div>
          </div>
        </div>
      </div>
    </div>
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
</style>
