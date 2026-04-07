function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem("user") || "null");
  } catch {
    return null;
  }
}

export default {
  namespaced: true,
  state: {
    user: readStoredUser(),
    token: localStorage.getItem("token"),
  },
  mutations: {
    SET_AUTH(state, { user, token }) {
      state.user = user ?? null;
      state.token = token ?? null;
      if (token) {
        localStorage.setItem("token", token);
      } else {
        localStorage.removeItem("token");
      }
      if (user) {
        localStorage.setItem("user", JSON.stringify(user));
      } else {
        localStorage.removeItem("user");
      }
    },
    CLEAR_AUTH(state) {
      state.user = null;
      state.token = null;
      localStorage.removeItem("token");
      localStorage.removeItem("user");
    },
  },
  getters: {
    canAccessAdmin(state) {
      return !!state.user?.can_access_admin_panel;
    },
    mlmRole(state) {
      return state.user?.mlm_role ?? null;
    },
    isSuperAdmin(state) {
      return state.user?.mlm_role === "superadmin";
    },
    isAdminOrSuperAdmin(state) {
      const r = state.user?.mlm_role;
      return r === "admin" || r === "superadmin";
    },
  },
  actions: {
    setAuth({ commit }, payload) {
      commit("SET_AUTH", payload);
    },
    logout({ commit }) {
      commit("CLEAR_AUTH");
    },
  },
};
