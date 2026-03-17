<script setup>
import { ref } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import ArgonButton from "@/components/ArgonButton.vue";

// Imágenes locales (deben existir en: src/assets/img/productos/1.png ... 6.png)
import producto1 from "@/assets/img/productos/1.png";
import producto2 from "@/assets/img/productos/2.png";
import producto3 from "@/assets/img/productos/3.png";
import producto4 from "@/assets/img/productos/4.png";
import producto5 from "@/assets/img/productos/5.png";
import producto6 from "@/assets/img/productos/6.png";

const productos = [
  {
    id: 1,
    nombre: "Pack Bienvenida Essential",
    descripcion: "Kit de inicio con productos estrella. Ideal para nuevos socios.",
    imagen: producto1,
    precioCliente: 149.00,
    precioSocio: 119.00,
    puntosValor: 120,
    categoria: "Packs",
  },
  {
    id: 2,
    nombre: "Suplemento Vitalidad",
    descripcion: "Fórmula con vitaminas y minerales para energía y bienestar diario.",
    imagen: producto2,
    precioCliente: 89.00,
    precioSocio: 69.00,
    puntosValor: 70,
    categoria: "Nutrición",
  },
  {
    id: 3,
    nombre: "Crema Facial Antiedad",
    descripcion: "Hidratación intensiva y efecto lifting. Dermatológicamente testada.",
    imagen: producto3,
    precioCliente: 65.00,
    precioSocio: 49.00,
    puntosValor: 50,
    categoria: "Cuidado personal",
  },
  {
    id: 4,
    nombre: "Aceite Esencial Relax",
    descripcion: "Blend natural para aromaterapia y masajes. 100% puro.",
    imagen: producto4,
    precioCliente: 32.00,
    precioSocio: 24.00,
    puntosValor: 25,
    categoria: "Bienestar",
  },
  {
    id: 5,
    nombre: "Barrita Proteína Chocolate",
    descripcion: "Snack saludable con 20g de proteína. Sin azúcares añadidos.",
    imagen: producto5,
    precioCliente: 28.00,
    precioSocio: 21.00,
    puntosValor: 20,
    categoria: "Nutrición",
  },
  {
    id: 6,
    nombre: "Set Regalo Premium",
    descripcion: "Caja regalo con los 3 best sellers. Presentación exclusiva.",
    imagen: producto6,
    precioCliente: 199.00,
    precioSocio: 159.00,
    puntosValor: 160,
    categoria: "Packs",
  },
];

const carrito = ref([]);

function formatearPrecio(valor) {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 2,
  }).format(valor);
}

function añadirAlCarrito(producto) {
  const existe = carrito.value.find((p) => p.id === producto.id);
  if (existe) {
    existe.cantidad += 1;
  } else {
    carrito.value.push({ ...producto, cantidad: 1 });
  }
}
</script>

<template>
  <div class="py-4 container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Catálogo de productos</h4>
            <p class="mb-0 text-sm text-secondary">
              Precios para cliente y socio, puntos valor (PV) y opción de añadir al carrito. Los socios disfrutan de precios preferentes.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Total productos"
          :value="String(productos.length)"
          description="<span class='text-sm font-weight-bolder text-primary'>En catálogo</span>"
          :icon="{
            component: 'ni ni-box-2',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Packs"
          :value="String(productos.filter(p => p.categoria === 'Packs').length)"
          description="<span class='text-sm font-weight-bolder text-info'>Kits y sets</span>"
          :icon="{
            component: 'ni ni-gift',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Nutrición"
          :value="String(productos.filter(p => p.categoria === 'Nutrición').length)"
          description="<span class='text-sm font-weight-bolder text-success'>Productos</span>"
          :icon="{
            component: 'ni ni-ungroup',
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

    <!-- Grid de productos -->
    <div class="row mt-4">
      <div
        v-for="producto in productos"
        :key="producto.id"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100 overflow-hidden card-producto">
          <div class="position-relative">
            <img
              :src="producto.imagen"
              :alt="producto.nombre"
              class="card-img-top w-100"
              style="height: 200px; object-fit: cover;"
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
            <p class="text-xs text-secondary mb-3 flex-grow-1" style="min-height: 2.5rem;">
              {{ producto.descripcion }}
            </p>
            <div class="mb-2">
              <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-1">
                <div>
                  <span class="text-xxs text-secondary d-block">Precio cliente</span>
                  <span class="text-sm text-secondary text-decoration-line-through">{{ formatearPrecio(producto.precioCliente) }}</span>
                </div>
                <div class="text-end">
                  <span class="text-xxs text-primary font-weight-bolder d-block">Precio socio</span>
                  <span class="text-sm font-weight-bolder text-primary">{{ formatearPrecio(producto.precioSocio) }}</span>
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

    <!-- Resumen carrito (si hay items) -->
    <div v-if="carrito.length > 0" class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Resumen del carrito</h6>
            <p class="text-xs text-secondary mt-1 mb-0">Productos añadidos. Puedes integrar el checkout con tu backend.</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Cantidad</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Precio socio</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">PV</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in carrito" :key="item.id">
                  <td>
                    <div class="d-flex align-items-center px-2 py-1">
                      <img
                        :src="item.imagen"
                        :alt="item.nombre"
                        class="avatar avatar-sm rounded me-2"
                        style="object-fit: cover;"
                      />
                      <span class="text-sm font-weight-bold">{{ item.nombre }}</span>
                    </div>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-primary">{{ item.cantidad }}</span>
                  </td>
                  <td class="align-middle text-center text-sm font-weight-bold">
                    {{ formatearPrecio(item.precioSocio * item.cantidad) }}
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-warning">{{ item.puntosValor * item.cantidad }} PV</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 d-flex justify-content-end">
            <argon-button color="success" variant="gradient" size="md">
              Ir al checkout
            </argon-button>
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
