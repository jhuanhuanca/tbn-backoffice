import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "../views/Dashboard.vue";
import Tables from "../views/Tables.vue";
import Billing from "../views/Billing.vue";
import VirtualReality from "../views/VirtualReality.vue";
import RTL from "../views/Rtl.vue";
import Profile from "../views/Profile.vue";
import Signup from "../views/Signup.vue";
import Signin from "../views/Signin.vue";
import CardCampanas from "@/views/components/CardCampanas.vue";
import CardArbol from "@/views/components/CardArbol.vue";
import CardReferidos from "@/views/components/CardReferidos.vue";
import Productos from "@/views/Productos.vue";
import Welcom from "../views/welcom.vue";
import ComprasRealizadas from "../views/ComprasRealizadas.vue";
import Billetera from "../views/Billetera.vue";
import Cuenta from "../views/Cuenta.vue";
import LandingPersonal from "../views/LandingPersonal.vue";
import SuscripcionPago from "../views/SuscripcionPago.vue";
import VerificarCorreo from "../views/VerificarCorreo.vue";
import ActivacionBinaria from "../views/ActivacionBinaria.vue";
import Comisiones from "../views/Comisiones.vue";
import LinkReferidos from "../views/linkReferidos.vue";
import AdminDashboard from "../views/admin/AdminDashboard.vue";
import AdminRetiros from "../views/admin/AdminRetiros.vue";
import AdminReconciliacion from "../views/admin/AdminReconciliacion.vue";
import AdminProductos from "../views/admin/AdminProductos.vue";
import AdminPaquetes from "../views/admin/AdminPaquetes.vue";
import AdminPedidos from "../views/admin/AdminPedidos.vue";
import RegistroClientePreferente from "../views/RegistroClientePreferente.vue";
import ClientePreferenteDashboard from "../views/ClientePreferenteDashboard.vue";

const requiresAuth = { requiresAuth: true };
const requiresAdmin = { requiresAuth: true, requiresAdmin: true };

const routes = [
  { path: "/", name: "/", redirect: "/welcom" },
  { path: "/welcom", name: "LandingMLM", component: Welcom },
  {
    path: "/i/:sponsorCode",
    name: "Invite",
    redirect: (to) => ({
      path: "/signup",
      query: { sponsor: to.params.sponsorCode },
    }),
  },
  {
    path: "/referido/:sponsorCode",
    name: "Referido",
    redirect: (to) => ({
      path: "/signup",
      query: { sponsor: to.params.sponsorCode },
    }),
  },
  {
    path: "/ref-pref/:sponsorCode",
    name: "ReferidoPreferente",
    redirect: (to) => ({
      path: "/registro-cliente-preferente",
      query: { sponsor: to.params.sponsorCode },
    }),
  },
  { path: "/dashboard-default", name: "Dashboard", component: Dashboard, meta: requiresAuth },
  { path: "/tables", name: "Tables", component: Tables, meta: requiresAuth },
  { path: "/cardcampanas", name: "CardCampanas", component: CardCampanas, meta: requiresAuth },
  { path: "/cardarbol", name: "CardArbol", component: CardArbol, meta: requiresAuth },
  { path: "/cardreferidos", name: "CardReferidos", component: CardReferidos, meta: requiresAuth },
  { path: "/productos", name: "Productos", component: Productos, meta: requiresAuth },
  { path: "/compras", name: "Compras", redirect: "/compras-realizadas" },
  {
    path: "/compras-realizadas",
    name: "ComprasRealizadas",
    component: ComprasRealizadas,
    meta: requiresAuth,
  },
  { path: "/billetera", name: "Billetera", component: Billetera, meta: requiresAuth },
  { path: "/billing", name: "Billing", component: Billing, meta: requiresAuth },
  { path: "/suscripcion-pago", name: "SuscripcionPago", component: SuscripcionPago, meta: requiresAuth },
  { path: "/virtual-reality", name: "Virtual Reality", component: VirtualReality, meta: requiresAuth },
  { path: "/rtl-page", name: "RTL", component: RTL, meta: requiresAuth },
  { path: "/profile", name: "Profile", component: Profile, meta: requiresAuth },
  { path: "/signin", name: "Signin", component: Signin, meta: { guest: true } },
  { path: "/signup", name: "Signup", component: Signup, meta: { guest: true } },
  {
    path: "/registro-cliente-preferente",
    name: "RegistroClientePreferente",
    component: RegistroClientePreferente,
    meta: { guest: true },
  },
  {
    path: "/cliente-preferente",
    name: "ClientePreferente",
    component: ClientePreferenteDashboard,
    meta: requiresAuth,
  },
  { path: "/verificar-correo", name: "VerificarCorreo", component: VerificarCorreo, meta: { guest: true } },
  { path: "/activacion-binaria", name: "ActivacionBinaria", component: ActivacionBinaria, meta: requiresAuth },
  { path: "/cuenta", name: "Cuenta", component: Cuenta, meta: requiresAuth },
  { path: "/mi-landing", name: "LandingPersonal", component: LandingPersonal, meta: requiresAuth },
  { path: "/comisiones", name: "Comisiones", component: Comisiones, meta: requiresAuth },
  { path: "/linkreferidos", name: "LinkReferidos", component: LinkReferidos, meta: requiresAuth },
  { path: "/admin/dashboard", name: "AdminDashboard", component: AdminDashboard, meta: requiresAdmin },
  { path: "/admin/retiros", name: "AdminRetiros", component: AdminRetiros, meta: requiresAdmin },
  { path: "/admin/reconciliacion", name: "AdminReconciliacion", component: AdminReconciliacion, meta: requiresAdmin },
  { path: "/admin/productos", name: "AdminProductos", component: AdminProductos, meta: requiresAdmin },
  { path: "/admin/paquetes", name: "AdminPaquetes", component: AdminPaquetes, meta: requiresAdmin },
  { path: "/admin/pedidos", name: "AdminPedidos", component: AdminPedidos, meta: requiresAdmin },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  if (to.meta.requiresAuth && !token) {
    next({ name: "Signin", query: { redirect: to.fullPath } });
    return;
  }
  if (to.meta.requiresAdmin) {
    let user = null;
    try {
      user = JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      user = null;
    }
    if (!user?.can_access_admin_panel) {
      next({ name: "Dashboard" });
      return;
    }
  }

  if (to.meta.requiresAuth && token) {
    let user = null;
    try {
      user = JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      user = null;
    }
    if (user?.is_preferred_customer && !user?.can_access_admin_panel) {
      const preferenteOk =
        to.path.startsWith("/cliente-preferente") ||
        to.path.startsWith("/compras-realizadas") ||
        to.path === "/cuenta" ||
        to.path.startsWith("/cuenta/") ||
        to.path === "/profile" ||
        to.path.startsWith("/profile/");
      if (!preferenteOk && !to.path.startsWith("/admin")) {
        next({ path: "/cliente-preferente" });
        return;
      }
    }
    if (user && !user.can_access_admin_panel) {
      if (user.needs_activation_subscription) {
        if (to.name !== "SuscripcionPago") {
          next({ name: "SuscripcionPago" });
          return;
        }
      } else if (user.needs_binary_placement) {
        if (to.name !== "ActivacionBinaria") {
          next({ name: "ActivacionBinaria" });
          return;
        }
      }
    }
  }

  if (to.meta.guest && token) {
    const sponsorQuery =
      to.query?.sponsor ||
      to.query?.ref ||
      to.query?.codigo;
    if ((to.name === "Signup" || to.name === "RegistroClientePreferente") && sponsorQuery) {
      next();
      return;
    }
    let user = null;
    try {
      user = JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      user = null;
    }
    if (user?.is_preferred_customer) {
      if (to.name !== "VerificarCorreo") {
        next({ path: "/cliente-preferente" });
        return;
      }
      next();
      return;
    }
    if (user?.needs_activation_subscription) {
      next({ name: "SuscripcionPago" });
      return;
    }
    if (user?.needs_binary_placement) {
      next({ name: "ActivacionBinaria" });
      return;
    }
    if (to.name !== "VerificarCorreo") {
      next({ name: "Dashboard" });
      return;
    }
    next();
    return;
  }
  next();
});

export default router;
