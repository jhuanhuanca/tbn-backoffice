## Cambios implementados (2026-04)

Este documento resume los cambios realizados para dejar el sistema MLM más cercano a producción (UI/UX + endpoints de soporte).

### 1) Dashboard — Rango visible y con iconografía
- **Archivo**: `test-front_synkai-main/src/views/Dashboard.vue`
- **Cambio**:
  - Se fortaleció el fallback del nombre de rango desde `dashboard.rank.name` y campos alternos.
  - Se agregó un icono dinámico por nombre de rango (p. ej. Bronce/Plata/Oro/Diamante).

### 2) Retiros — KPIs mensuales (ganado / retirado / balance)
- **Archivo**: `test-front_synkai-main/src/views/RetirosHistorial.vue`
- **Cambio**:
  - En el header del gráfico se muestran KPIs del mes actual:
    - **Ganaste**: suma de movimientos positivos en `/wallet/transactions` (aprox).
    - **Retiraste**: suma de retiros del mes en `/withdrawals` (excluye rechazados).
    - **Balance**: ganado − retirado.

### 3) Estadísticas del equipo — gráfico + recomendaciones
- **Archivo**: `test-front_synkai-main/src/views/EstadisticasEquipo.vue`
- **Cambio**:
  - Se agregó un gráfico (Chart.js) comparativo Izq/Der del periodo actual.
  - Se agregó un bloque de recomendaciones accionables para nivelar el desbalance.

### 4) Mi cuenta (`CardCuenta`) — acciones reales (perfil, password, wallet prefs, soporte)
- **Archivo**: `test-front_synkai-main/src/views/components/CardCuenta.vue`
- **Front**:
  - Guardar perfil usa `PUT /me/profile`.
  - Cambiar contraseña usa `PUT /me/password`.
  - Preferencias de billetera usa `GET/PUT /me/wallet-settings`.
  - Soporte / tickets usa `GET/POST /support/tickets`.
- **Back**:
  - Se añadió `users.meta` para persistir preferencias y contenido de landing.
  - Se creó tabla `support_tickets` + endpoints.

### 5) Mi landing — editor + landing pública con URL propia
- **Editor (privado)**:
  - **Ruta**: `/mi-landing`
  - **Archivo**: `test-front_synkai-main/src/views/LandingEditor.vue`
  - Guarda contenido con `PUT /me/landing`.
- **Landing pública**:
  - **Ruta**: `/p/:memberCode`
  - **Archivo**: `test-front_synkai-main/src/views/LandingPersonal.vue`
  - Carga contenido desde `GET /public/landing/{memberCode}`.
  - Se fuerza layout tipo landing (sin sidenav/navbar del dashboard).

### 6) Campañas — rediseño estilo Argon + bloques MLM
- **Archivo**: `test-front_synkai-main/src/views/components/CardCampanas.vue`
- **Cambio**:
  - Se reescribió a estilo Argon (cards, iconos `ni`, sombras, grid).
  - Se agregaron bloques sugeridos para automatizaciones MLM (onboarding 72h, reactivación, segmentación por rango).
  - Las métricas mostradas son “demo” hasta conectar un backend real de campañas.

---

## Endpoints añadidos (backend)

### Cuenta / Landing / Wallet settings
- `PUT /me/profile`
- `PUT /me/password`
- `GET /me/landing`
- `PUT /me/landing`
- `GET /me/wallet-settings`
- `PUT /me/wallet-settings`

### Landing pública
- `GET /public/landing/{memberCode}`

### Soporte / Tickets
- `GET /support/tickets`
- `POST /support/tickets`

---

## Notas para producción
- Migrar y ejecutar migraciones nuevas:
  - `2026_04_15_090000_add_meta_to_users_table.php`
  - `2026_04_15_091000_create_support_tickets_table.php`
- Verificar CORS/BASE_URL para que `/p/:memberCode` sea accesible públicamente.
- Las secciones “2FA” y “Campañas” requieren integración backend real para ser 100% productivas (por ahora UI lista + endpoints base de soporte).

