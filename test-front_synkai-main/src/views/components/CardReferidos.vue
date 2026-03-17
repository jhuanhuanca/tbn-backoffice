<script setup>
import { computed } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";

const referidos = [
  { id: 1, nombre: "María García", email: "maria.garcia@email.com", fechaAlta: "15/01/2025", pierna: "left", volumen: "2,450 PV", estado: "Activo", rango: "Bronce", iniciales: "MG" },
  { id: 2, nombre: "Carlos López", email: "carlos.lopez@email.com", fechaAlta: "22/01/2025", pierna: "right", volumen: "1,890 PV", estado: "Activo", rango: "Bronce", iniciales: "CL" },
  { id: 3, nombre: "Ana Martínez", email: "ana.martinez@email.com", fechaAlta: "28/01/2025", pierna: "left", volumen: "3,120 PV", estado: "Activo", rango: "Plata", iniciales: "AM" },
  { id: 4, nombre: "Pedro Sánchez", email: "pedro.sanchez@email.com", fechaAlta: "05/02/2025", pierna: "right", volumen: "980 PV", estado: "Inactivo", rango: "Bronce", iniciales: "PS" },
  { id: 5, nombre: "Laura Fernández", email: "laura.fernandez@email.com", fechaAlta: "12/02/2025", pierna: "left", volumen: "4,200 PV", estado: "Activo", rango: "Oro", iniciales: "LF" },
];

const referidosIzquierda = computed(() => referidos.filter((r) => r.pierna === "left"));
const referidosDerecha = computed(() => referidos.filter((r) => r.pierna === "right"));

const stats = {
  total: referidos.length,
  izquierda: referidosIzquierda.value.length,
  derecha: referidosDerecha.value.length,
  activos: referidos.filter((r) => r.estado === "Activo").length,
};
</script>

<template>
  <div class="py-4 container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Referidos directos</h4>
            <p class="mb-0 text-sm text-secondary">
              Consulta la información de cada referido directo y su posición en la estructura binaria (pierna izquierda o derecha).
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Total referidos"
          :value="String(stats.total)"
          description="<span class='text-sm font-weight-bolder text-primary'>Directos</span> en tu red"
          :icon="{
            component: 'ni ni-single-02',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna izquierda"
          :value="String(stats.izquierda)"
          description="<span class='text-sm font-weight-bolder text-info'>Referidos</span> en izquierda"
          :icon="{
            component: 'ni ni-bold-left',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna derecha"
          :value="String(stats.derecha)"
          description="<span class='text-sm font-weight-bolder text-success'>Referidos</span> en derecha"
          :icon="{
            component: 'ni ni-bold-right',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Activos"
          :value="String(stats.activos)"
          description="<span class='text-sm font-weight-bolder text-warning'>Calificados</span> este mes"
          :icon="{
            component: 'ni ni-check-bold',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div class="row mt-4">
      <!-- Estructura binaria -->
      <div class="col-lg-5 col-12 mb-4 mb-lg-0">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Estructura binaria</h6>
            <p class="text-xs text-secondary mt-1 mb-0">Referidos directos por pierna (izquierda / derecha).</p>
          </div>
          <div class="p-3 card-body overflow-auto">
            <div class="referidos-tree">
              <div class="tree-node-root">
                <div class="avatar-root bg-gradient-primary rounded-circle shadow d-flex align-items-center justify-content-center text-white font-weight-bolder">
                  Tú
                </div>
              </div>
              <div class="tree-connector-main"></div>
              <div class="tree-branches">
                <div class="tree-branch left">
                  <div class="tree-connector-v"></div>
                  <div class="tree-label text-primary text-xs font-weight-bolder mb-1">Izquierda</div>
                  <div class="tree-nodes">
                    <div
                      v-for="ref in referidosIzquierda"
                      :key="ref.id"
                      class="tree-node-ref"
                      :class="ref.estado === 'Activo' ? 'node-active' : 'node-inactive'"
                    >
                      <div class="avatar-ref rounded-circle d-flex align-items-center justify-content-center text-white text-xs font-weight-bolder bg-gradient-primary">
                        {{ ref.iniciales }}
                      </div>
                      <span class="ref-name text-xs font-weight-bold">{{ ref.nombre.split(' ')[0] }}</span>
                      <span class="ref-vol text-xxs text-secondary">{{ ref.volumen }}</span>
                    </div>
                  </div>
                </div>
                <div class="tree-branch right">
                  <div class="tree-connector-v"></div>
                  <div class="tree-label text-success text-xs font-weight-bolder mb-1">Derecha</div>
                  <div class="tree-nodes">
                    <div
                      v-for="ref in referidosDerecha"
                      :key="ref.id"
                      class="tree-node-ref"
                      :class="ref.estado === 'Activo' ? 'node-active' : 'node-inactive'"
                    >
                      <div class="avatar-ref rounded-circle d-flex align-items-center justify-content-center text-white text-xs font-weight-bolder bg-gradient-success">
                        {{ ref.iniciales }}
                      </div>
                      <span class="ref-name text-xs font-weight-bold">{{ ref.nombre.split(' ')[0] }}</span>
                      <span class="ref-vol text-xxs text-secondary">{{ ref.volumen }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de referidos -->
      <div class="col-lg-7 col-12">
        <div class="card">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Información de referidos directos</h6>
            <p class="text-xs text-secondary mt-1 mb-0">Detalle de cada referido: contacto, pierna, volumen y estado.</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Referido</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha alta</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Pierna</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Volumen</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Rango</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ref in referidos" :key="ref.id">
                  <td>
                    <div class="d-flex px-2 py-1 align-items-center">
                      <div
                        class="avatar avatar-sm rounded-circle me-3 d-flex align-items-center justify-content-center text-white font-weight-bolder text-xs"
                        :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
                      >
                        {{ ref.iniciales }}
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ ref.nombre }}</h6>
                        <p class="text-xs text-secondary mb-0">{{ ref.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="text-xs font-weight-bold text-secondary">{{ ref.fechaAlta }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span
                      class="badge badge-sm"
                      :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
                    >
                      {{ ref.pierna === "left" ? "Izq." : "Der." }}
                    </span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-sm font-weight-bold">{{ ref.volumen }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-warning">{{ ref.rango }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span
                      class="badge badge-sm"
                      :class="ref.estado === 'Activo' ? 'bg-gradient-success' : 'bg-gradient-secondary'"
                    >
                      {{ ref.estado }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards individuales (vista detalle) -->
    <div class="row mt-4">
      <div class="col-12">
        <h6 class="mb-3 font-weight-bolder text-dark">Detalle por referido</h6>
      </div>
      <div
        v-for="ref in referidos"
        :key="'card-' + ref.id"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100">
          <div class="p-3 card-body">
            <div class="d-flex align-items-center mb-3">
              <div
                class="avatar avatar-lg rounded-circle me-3 d-flex align-items-center justify-content-center text-white font-weight-bolder"
                :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
              >
                {{ ref.iniciales }}
              </div>
              <div>
                <h6 class="mb-0 text-sm font-weight-bolder">{{ ref.nombre }}</h6>
                <p class="text-xs text-secondary mb-0">{{ ref.email }}</p>
              </div>
            </div>
            <hr class="horizontal dark" />
            <div class="row text-center">
              <div class="col-4">
                <p class="text-xxs text-secondary mb-0">Pierna</p>
                <span
                  class="badge badge-sm mt-1"
                  :class="ref.pierna === 'left' ? 'bg-gradient-primary' : 'bg-gradient-success'"
                >
                  {{ ref.pierna === "left" ? "Izquierda" : "Derecha" }}
                </span>
              </div>
              <div class="col-4">
                <p class="text-xxs text-secondary mb-0">Volumen</p>
                <p class="text-sm font-weight-bolder mb-0">{{ ref.volumen }}</p>
              </div>
              <div class="col-4">
                <p class="text-xxs text-secondary mb-0">Estado</p>
                <span
                  class="badge badge-sm mt-1"
                  :class="ref.estado === 'Activo' ? 'bg-gradient-success' : 'bg-gradient-secondary'"
                >
                  {{ ref.estado }}
                </span>
              </div>
            </div>
            <div class="mt-2 pt-2 d-flex justify-content-between align-items-center">
              <span class="text-xs text-secondary">Alta: {{ ref.fechaAlta }}</span>
              <span class="badge badge-sm bg-gradient-warning">{{ ref.rango }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.referidos-tree {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem 0;
  min-height: 280px;
}
.tree-node-root {
  margin-bottom: 0;
}
.avatar-root {
  width: 56px;
  height: 56px;
  font-size: 0.85rem;
}
.tree-connector-main {
  width: 2px;
  height: 20px;
  background: linear-gradient(180deg, #54b144 0%, #e9ecef 100%);
  margin: 0.25rem auto;
}
.tree-branches {
  display: flex;
  gap: 2rem;
  margin-top: 0.5rem;
  width: 100%;
  justify-content: center;
}
.tree-branch {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  max-width: 220px;
}
.tree-connector-v {
  width: 2px;
  height: 12px;
  background: #dee2e6;
  margin-bottom: 0.25rem;
}
.tree-nodes {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}
.tree-node-ref {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem;
  border-radius: 0.5rem;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  transition: box-shadow 0.2s ease;
}
.tree-node-ref.node-active {
  border-color: rgba(45, 206, 137, 0.3);
  background: rgba(84, 177, 68, 0.06);
}
.tree-node-ref.node-inactive {
  opacity: 0.85;
}
.tree-node-ref:hover {
  box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.08);
}
.avatar-ref {
  width: 36px;
  height: 36px;
  font-size: 0.65rem;
  margin-bottom: 0.25rem;
}
.ref-name {
  display: block;
  color: #344767;
}
.ref-vol {
  display: block;
}
.text-xxs {
  font-size: 0.65rem;
}
</style>
