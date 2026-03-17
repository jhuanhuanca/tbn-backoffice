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
import CardProductos from "@/views/components/CardProductos.vue";
import Welcom from "../views/welcom.vue";
import ComprasRealizadas from "../views/ComprasRealizadas.vue";
import Billetera from "../views/Billetera.vue";
import Cuenta from "../views/Cuenta.vue";
import LandingPersonal from "../views/LandingPersonal.vue";
import SuscripcionPago from "../views/SuscripcionPago.vue";
import Comisiones from "../views/Comisiones.vue";
import LinkReferidos from "../views/linkReferidos.vue";
const routes = [
  {
    path: "/",
    name: "/",
    redirect: "/welcom",
  },
  {
    path: "/welcom",
    name: "LandingMLM",
    component: Welcom,
  },
  {
    path: "/dashboard-default",
    name: "Dashboard",
    component: Dashboard,
  },
  {
    path: "/tables",
    name: "Tables",
    component: Tables,
  },



  {
    path: "/cardcampanas",
    name: "CardCampanas",
    component: CardCampanas,
  },
  {
    path: "/cardarbol",
    name: "CardArbol",
    component: CardArbol,
  },
  {
    path: "/cardreferidos",
    name: "CardReferidos",
    component: CardReferidos,
  },
  {
    path: "/productos",
    name: "Productos",
    component: CardProductos,
  },

  {
    path: "/compras",
    name: "Compras",
    redirect: "/compras-realizadas",
  },
  {
    path: "/compras-realizadas",
    name: "ComprasRealizadas",
    component: ComprasRealizadas,
  },
  {
    path: "/billetera",
    name: "Billetera",
    component: Billetera,
  },

  {
    path: "/billing",
    name: "Billing",
    component: Billing,
  },
  {
    path: "/suscripcion-pago",
    name: "SuscripcionPago",
    component: SuscripcionPago,
  },
  {
    path: "/virtual-reality",
    name: "Virtual Reality",
    component: VirtualReality,
  },
  {
    path: "/rtl-page",
    name: "RTL",
    component: RTL,
  },
  {
    path: "/profile",
    name: "Profile",
    component: Profile,
  },
  {
    path: "/signin",
    name: "Signin",
    component: Signin,
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup,
  },
  {
    path: "/cuenta",
    name: "Cuenta",
    component: Cuenta,
  },
  {
    path: "/mi-landing",
    name: "LandingPersonal",
    component: LandingPersonal,
  },
  {
    path: "/comisiones",
    name: "Comisiones",
    component: Comisiones,
  },
  {
    path: "/linkreferidos",
    name: "LinkReferidos",
    component: LinkReferidos,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

export default router;
