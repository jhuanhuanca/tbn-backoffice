<template>
    <div class="card-accion">
        <div class="header">
            <h1>Plan de Acción</h1>
            <p class="subtitle">30 Días Network Marketing</p>
        </div>
        
        <div class="plan-container">
            <div class="summary">
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-label">Completadas</span>
                        <span class="stat-value">{{ completadas }}/{{ tareas.length }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Progreso</span>
                        <span class="stat-value">{{ porcentaje }}%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress" :style="{ width: porcentaje + '%' }"></div>
                </div>
            </div>

            <div class="tareas-list">
                <div v-for="(tarea, index) in tareas" :key="index" class="tarea-item">
                    <input 
                        type="checkbox" 
                        :id="'tarea-' + index"
                        v-model="tarea.completada"
                        @change="actualizarProgreso"
                    >
                    <label :for="'tarea-' + index" :class="{ completada: tarea.completada }">
                        <span class="dia">Día {{ tarea.dia }}</span>
                        <span class="descripcion">{{ tarea.descripcion }}</span>
                    </label>
                </div>
            </div>

            <button @click="reiniciar" class="btn-reiniciar">↻ Reiniciar Plan</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CardAccion',
    data() {
        return {
            completadas: 0,
            tareas: [
                { dia: 1, descripcion: 'Definir objetivos personales y metas de ventas', completada: false },
                { dia: 2, descripcion: 'Crear lista de 50 contactos potenciales', completada: false },
                { dia: 3, descripcion: 'Contactar 10 personas de la lista', completada: false },
                { dia: 4, descripcion: 'Realizar 3 presentaciones del producto/servicio', completada: false },
                { dia: 5, descripcion: 'Seguimiento a contactos del día 3', completada: false },
                { dia: 6, descripcion: 'Reclutar 1 nuevo miembro al equipo', completada: false },
                { dia: 7, descripcion: 'Revisar resultados de la semana', completada: false },
                { dia: 8, descripcion: 'Capacitación con tu patrocinador', completada: false },
                { dia: 9, descripcion: 'Contactar 10 personas nuevas', completada: false },
                { dia: 10, descripcion: 'Hacer 2 presentaciones', completada: false },
                { dia: 11, descripcion: 'Seguimiento a prospects', completada: false },
                { dia: 12, descripcion: 'Planificar evento o reunión de equipo', completada: false },
                { dia: 13, descripcion: 'Capacitar a 1 nuevo miembro del equipo', completada: false },
                { dia: 14, descripcion: 'Análisis de avances - Semana 2', completada: false },
                { dia: 15, descripcion: 'Contactar 15 personas', completada: false },
                { dia: 16, descripcion: 'Realizar 3 presentaciones', completada: false },
                { dia: 17, descripcion: 'Seguimiento y cierre de ventas', completada: false },
                { dia: 18, descripcion: 'Reunión de motivación del equipo', completada: false },
                { dia: 19, descripcion: 'Contactar líderes potenciales', completada: false },
                { dia: 20, descripcion: 'Hacer 2 presentaciones', completada: false },
                { dia: 21, descripcion: 'Revisar KPIs y resultados - Semana 3', completada: false },
                { dia: 22, descripcion: 'Contactar 12 nuevas personas', completada: false },
                { dia: 23, descripcion: 'Realizar 3 presentaciones', completada: false },
                { dia: 24, descripcion: 'Seguimiento a conversiones', completada: false },
                { dia: 25, descripcion: 'Desarrollar 2 miembros nuevos', completada: false },
                { dia: 26, descripcion: 'Evento de capacitación grupal', completada: false },
                { dia: 27, descripcion: 'Contactar 10 personas', completada: false },
                { dia: 28, descripcion: 'Cierre de mes - 2 presentaciones', completada: false },
                { dia: 29, descripcion: 'Análisis completo del mes', completada: false },
                { dia: 30, descripcion: 'Planificación para el próximo mes', completada: false }
            ]
        }
    },
    computed: {
        porcentaje() {
            return this.tareas.length > 0 ? Math.round((this.completadas / this.tareas.length) * 100) : 0;
        }
    },
    methods: {
        actualizarProgreso() {
            this.completadas = this.tareas.filter(t => t.completada).length;
        },
        reiniciar() {
            this.tareas.forEach(t => t.completada = false);
            this.completadas = 0;
        }
    }
}
</script>

<style scoped>
* {
    box-sizing: border-box;
}

.card-accion {
    min-height: 100vh;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9f0 100%);
    padding: 40px 20px;
}

.header {
    text-align: center;
    margin-bottom: 40px;
}

h1 {
    color: #2d6a4f;
    font-size: 2.5em;
    margin: 0;
    font-weight: 700;
}

.subtitle {
    color: #52b788;
    font-size: 1.1em;
    margin: 10px 0 0 0;
}

.plan-container {
    max-width: 1200px;
    margin: 0 auto;
}

.summary {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(45, 106, 79, 0.08);
    margin-bottom: 40px;
    border-left: 5px solid #40916c;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.9em;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 1.8em;
    font-weight: 700;
    color: #2d6a4f;
}

.progress-bar {
    width: 100%;
    height: 30px;
    background: #e8f5e9;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.progress {
    height: 100%;
    background: linear-gradient(90deg, #40916c, #52b788);
    transition: width 0.4s ease;
    box-shadow: 0 0 10px rgba(64, 145, 108, 0.3);
}

.tareas-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.tarea-item {
    display: flex;
    align-items: flex-start;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(45, 106, 79, 0.06);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.tarea-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(45, 106, 79, 0.12);
    border-color: #d8f3dc;
}

input[type="checkbox"] {
    width: 22px;
    height: 22px;
    margin-right: 15px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #40916c;
    flex-shrink: 0;
}

label {
    cursor: pointer;
    flex: 1;
}

.dia {
    display: block;
    font-weight: 700;
    color: #40916c;
    font-size: 0.85em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.descripcion {
    display: block;
    color: #333;
    font-size: 0.95em;
    line-height: 1.4;
}

label.completada {
    opacity: 0.7;
}

label.completada .descripcion {
    text-decoration: line-through;
    color: #aaa;
}

label.completada .dia {
    color: #b7e4c7;
}

.btn-reiniciar {
    display: block;
    margin: 0 auto;
    padding: 14px 40px;
    background: linear-gradient(135deg, #40916c, #2d6a4f);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(64, 145, 108, 0.3);
}

.btn-reiniciar:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(64, 145, 108, 0.4);
}

.btn-reiniciar:active {
    transform: translateY(0);
}
</style>