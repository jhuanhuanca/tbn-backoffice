export default {
  state: {
    user: null,
    token: null,
  },
  mutations: {
    LOGOUT(state) {
      state.user = null
      state.token = null
    },
  },
  actions: {
    logout({ commit }) {
      commit('LOGOUT')
    },
  },
}