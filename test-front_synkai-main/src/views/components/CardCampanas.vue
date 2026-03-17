<template>
    <div class="campaigns-container">
        <!-- Header -->
        <div class="header">
            <h1>Gestión de Campañas Automáticas</h1>
            <p>Automatiza tus comunicaciones y segmenta tus leads</p>
        </div>

        <!-- Cards Grid -->
        <div class="cards-grid">
            <!-- Email Campaigns -->
            <div class="card">
                <div class="card-icon email-icon">📧</div>
                <h3>Campañas Email</h3>
                <p>Crea y automatiza secuencias de email personalizadas para tus prospectos</p>
                <button class="btn btn-primary">Crear Campaña</button>
            </div>

            <!-- SMS Campaigns -->
            <div class="card">
                <div class="card-icon sms-icon">💬</div>
                <h3>Campañas SMS</h3>
                <p>Envía mensajes automáticos segmentados a tus leads en tiempo real</p>
                <button class="btn btn-primary">Crear Campaña</button>
            </div>

            <!-- WhatsApp Campaigns -->
            <div class="card">
                <div class="card-icon whatsapp-icon">📱</div>
                <h3>WhatsApp Automático</h3>
                <p>Automatiza conversaciones en WhatsApp con secuencias inteligentes</p>
                <button class="btn btn-primary">Crear Campaña</button>
            </div>

            <!-- Sequences -->
            <div class="card">
                <div class="card-icon sequence-icon">🔄</div>
                <h3>Secuencias Automáticas</h3>
                <p>Configura flujos automáticos multi-canal para convertir prospectos</p>
                <button class="btn btn-primary">Crear Secuencia</button>
            </div>

            <!-- Lead Segmentation -->
            <div class="card">
                <div class="card-icon segment-icon">🎯</div>
                <h3>Segmentación de Leads</h3>
                <p>Organiza y segmenta tu base de datos por criterios personalizados</p>
                <button class="btn btn-primary">Segmentar Leads</button>
            </div>

            <!-- Social Media Integration -->
            <div class="card">
                <div class="card-icon social-icon">🌐</div>
                <h3>Integración Redes Sociales</h3>
                <p>Conecta tus campañas con Instagram, Facebook, LinkedIn y más</p>
                <button class="btn btn-primary">Integrar Redes</button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <h4>Campañas Activas</h4>
                <p class="stat-number">12</p>
            </div>
            <div class="stat-card">
                <h4>Leads Segmentados</h4>
                <p class="stat-number">1,240</p>
            </div>
            <div class="stat-card">
                <h4>Tasa de Conversión</h4>
                <p class="stat-number">28%</p>
            </div>
            <div class="stat-card">
                <h4>Mensajes Enviados</h4>
                <p class="stat-number">5,890</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CardCampañas',
    data() {
        return {}
    }
}
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.campaigns-container {
    background: linear-gradient(135deg, #ffffff 0%, rgba(84, 177, 68, 0.08) 100%);
    padding: 40px 20px;
    min-height: 100vh;
}

.header {
    text-align: center;
    margin-bottom: 50px;
    animation: slideDown 0.6s ease-out;
}

.header h1 {
    font-size: 2.5rem;
    color: #222d25;
    margin-bottom: 10px;
    font-weight: 700;
}

.header p {
    font-size: 1.1rem;
    color: #54b144;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 50px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(84, 177, 68, 0.08);
    transition: all 0.3s ease;
    border-left: 4px solid #54b144;
    animation: slideUp 0.6s ease-out;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 25px rgba(84, 177, 68, 0.15);
    border-left-color: #222d25;
}

.card-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    display: inline-block;
    padding: 15px;
    border-radius: 10px;
}

.email-icon {
    background-color: #dcfce7;
}

.sms-icon {
    background-color: #bbf7d0;
}

.whatsapp-icon {
    background-color: #a7f3d0;
}

.sequence-icon {
    background-color: #86efac;
}

.segment-icon {
    background-color: #6ee7b7;
}

.social-icon {
    background-color: #5eead4;
}

.card h3 {
    font-size: 1.4rem;
    color: #222d25;
    margin-bottom: 10px;
    font-weight: 600;
}

.card p {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #54b144 0%, #222d25 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #222d25 0%, #1a2219 100%);
    transform: scale(1.02);
}

.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(84, 177, 68, 0.08);
    border-top: 3px solid #54b144;
}

.stat-card h4 {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 10px;
    font-weight: 500;
}

.stat-number {
    font-size: 2rem;
    color: #222d25;
    font-weight: 700;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .header h1 {
        font-size: 1.8rem;
    }

    .cards-grid {
        grid-template-columns: 1fr;
    }

    .stats-section {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>