import { createStore } from "vuex";

const XL_BREAKPOINT = 1200;

function getInitialPinned() {
  if (typeof window === "undefined") return false;
  return window.innerWidth >= XL_BREAKPOINT;
}

export default createStore({
  state: {
    hideConfigButton: false,
    isPinned: getInitialPinned(),
    showConfig: false,
    sidebarType: "bg-white",
    isRTL: false,
    mcolor: "",
    darkMode: false,
    isNavFixed: false,
    isAbsolute: false,
    showNavs: true,
    showSidenav: true,
    showNavbar: true,
    showFooter: true,
    showMain: true,
    layout: "default",
  },
  mutations: {
    toggleConfigurator(state) {
      state.showConfig = !state.showConfig;
    },
    setPinned(state, value) {
      state.isPinned = !!value;
    },
    sidebarMinimize(state) {
      state.isPinned = !state.isPinned;
    },
    sidebarType(state, payload) {
      state.sidebarType = payload;
    },
    navbarFixed(state) {
      if (state.isNavFixed === false) {
        state.isNavFixed = true;
      } else {
        state.isNavFixed = false;
      }
    },
  },
  actions: {
    toggleSidebarColor({ commit }, payload) {
      commit("sidebarType", payload);
    },
  },
  getters: {},
});
