<script setup>
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import DefaultInfoCard from "@/examples/Cards/DefaultInfoCard.vue";

const treeStats = {
  leftLeg: "12,450 PV",
  rightLeg: "10,980 PV",
  binaryEarnings: "$1,240.00",
  levelEarnings: "$580.00",
  currentLevel: "Nivel 5",
  totalMembers: "127",
};

const hybridPlanLevels = [
  { level: 1, name: "Directos", binary: "10%", unilevel: "5%", match: "5%", total: "20%" },
  { level: 2, name: "Segundo nivel", binary: "8%", unilevel: "4%", match: "4%", total: "16%" },
  { level: 3, name: "Tercer nivel", binary: "6%", unilevel: "3%", match: "3%", total: "12%" },
  { level: 4, name: "Cuarto nivel", binary: "4%", unilevel: "2%", match: "2%", total: "8%" },
  { level: 5, name: "Quinto nivel", binary: "2%", unilevel: "1%", match: "1%", total: "4%" },
];
</script>

<template>
  <div class="py-4 container-fluid">
    <!-- Encabezado de página -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">
              Red de árbol binario · Plan binario híbrido
            </h4>
            <p class="mb-0 text-sm text-secondary">
              Visualiza tu red multinivel, el equilibrio de piernas y las ganancias del plan de compensación binario híbrido (matching + bonos por nivel).
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- KPIs principales -->
    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna izquierda"
          :value="treeStats.leftLeg"
          description="<span class='text-sm font-weight-bolder text-primary'>Volumen</span> acumulado"
          :icon="{
            component: 'ni ni-bold-left',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Pierna derecha"
          :value="treeStats.rightLeg"
          description="<span class='text-sm font-weight-bolder text-info'>Volumen</span> acumulado"
          :icon="{
            component: 'ni ni-bold-right',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Ganancias binario"
          :value="treeStats.binaryEarnings"
          description="<span class='text-sm font-weight-bolder text-success'>Matching</span> del período"
          :icon="{
            component: 'ni ni-money-coins',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Ganancias multinivel"
          :value="treeStats.levelEarnings"
          description="<span class='text-sm font-weight-bolder text-warning'>Por niveles</span>"
          :icon="{
            component: 'ni ni-chart-bar-32',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Nivel actual"
          :value="treeStats.currentLevel"
          description="<span class='text-sm font-weight-bolder text-dark'>Profundidad</span> en la red"
          :icon="{
            component: 'ni ni-world-2',
            background: 'bg-gradient-dark',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Miembros en red"
          :value="treeStats.totalMembers"
          description="<span class='text-sm font-weight-bolder text-secondary'>Total</span> en tu árbol"
          :icon="{
            component: 'ni ni-single-02',
            background: 'bg-gradient-secondary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-6 col-12 d-flex align-items-stretch">
        <default-info-card
          title="Binario híbrido"
          description="Combinación de compensación binaria (matching entre piernas) y bonos unilevel por niveles de profundidad."
          value="Matching + Unilevel"
          :icon="{
            component: 'ni ni-trophy',
            background: 'bg-gradient-success',
          }"
        />
      </div>
    </div>

    <!-- Esquema visual del árbol binario -->
    <div class="row mt-4">
      <div class="col-lg-6 col-12 mb-4 mb-lg-0">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Estructura del árbol binario</h6>
            <p class="text-xs text-secondary mt-1 mb-0">Cada nodo tiene máximo 2 referidos directos (izquierda y derecha).</p>
          </div>
          <div class="p-3 card-body overflow-auto">
            <div class="tree-diagram">
              <div class="tree-node root">
                <span class="node-badge bg-gradient-primary">Tú</span>
              </div>
              <div class="tree-connector"></div>
              <div class="tree-children">
                <div class="tree-branch left">
                  <div class="tree-connector vertical"></div>
                  <div class="tree-node">
                    <span class="node-badge bg-gradient-primary">I1</span>
                  </div>
                  <div class="tree-connector small"></div>
                  <div class="tree-grandchildren">
                    <div class="tree-node small"><span class="node-badge bg-light text-dark">I2</span></div>
                    <div class="tree-node small"><span class="node-badge bg-light text-dark">D2</span></div>
                  </div>
                </div>
                <div class="tree-branch right">
                  <div class="tree-connector vertical"></div>
                  <div class="tree-node">
                    <span class="node-badge bg-gradient-info">D1</span>
                  </div>
                  <div class="tree-connector small"></div>
                  <div class="tree-grandchildren">
                    <div class="tree-node small"><span class="node-badge bg-light text-dark">I2</span></div>
                    <div class="tree-node small"><span class="node-badge bg-light text-dark">D2</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla plan binario híbrido -->
      <div class="col-lg-6 col-12">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Plan de compensación binario híbrido</h6>
            <p class="text-xs text-secondary mt-1 mb-0">Porcentajes por nivel: matching binario + bono unilevel.</p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nivel</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Binario</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Unilevel</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Match</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in hybridPlanLevels" :key="row.level">
                  <td>
                    <span class="text-sm font-weight-bold">{{ row.name }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-primary">{{ row.binary }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-info">{{ row.unilevel }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-success">{{ row.match }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-sm font-weight-bolder text-dark">{{ row.total }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Resumen explicativo -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="p-4 card-body">
            <h6 class="mb-3 font-weight-bolder text-dark">¿Cómo funciona el binario híbrido?</h6>
            <div class="row">
              <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-primary rounded-circle me-2 shadow">
                    <i class="ni ni-bold-left text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">Dos piernas</span>
                    <p class="text-xs text-secondary mb-0">Izquierda y derecha. Las ventas se suman por pierna y se hace matching entre ambas.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mb-3 mb-md-0">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-success rounded-circle me-2 shadow">
                    <i class="ni ni-money-coins text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">Matching binario</span>
                    <p class="text-xs text-secondary mb-0">Ganas un porcentaje sobre el volumen matcheado (el menor entre ambas piernas) en cada ciclo.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex align-items-start">
                  <div class="icon icon-shape icon-sm bg-gradient-info rounded-circle me-2 shadow">
                    <i class="ni ni-chart-bar-32 text-white opacity-10" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-sm font-weight-bolder text-dark">Multinivel (unilevel)</span>
                    <p class="text-xs text-secondary mb-0">Bonos por profundidad: ganas un % sobre las ventas de cada nivel debajo de ti en la red.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tree-diagram {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem 0;
  min-height: 220px;
}
.tree-node {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.tree-node.root {
  width: 56px;
  height: 56px;
}
.tree-node.small {
  width: 36px;
  height: 36px;
}
.node-badge {
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  padding: 0.25rem 0.4rem;
  border-radius: 50%;
  min-width: 2rem;
  display: inline-block;
  text-align: center;
}
.tree-node.small .node-badge {
  font-size: 0.6rem;
  padding: 0.15rem 0.3rem;
  min-width: 1.5rem;
}
.tree-connector {
  width: 2px;
  height: 24px;
  background: linear-gradient(180deg, #5e72e4 0%, transparent 100%);
  margin: 0 auto;
}
.tree-connector.vertical {
  height: 20px;
  background: #dee2e6;
}
.tree-connector.small {
  height: 12px;
  background: #e9ecef;
}
.tree-children {
  display: flex;
  gap: 2rem;
  margin-top: 0.25rem;
}
.tree-branch {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.tree-grandchildren {
  display: flex;
  gap: 1.5rem;
  margin-top: 0.25rem;
}
</style>
