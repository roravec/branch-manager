import { defineStore } from "pinia";

export const useUserStore = defineStore("user", {
  state: () => ({
    id: null,
    identifier: null,
    name: null,
    rights: 0,
    status: null,
    type: null,
    last_seen: null,
    created_at: null,
    access_token: null,
    refresh_token: null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.access_token,
    isAdmin: (state) => state.rights > 3 && state.rights < 11,
    isManager: (state) => state.rights == 2 || state.rights == 3,
  },

  actions: {
    setUser(data) {
      this.id = data.id;
      this.identifier = data.identifier;
      this.name = data.name;
      this.rights = data.rights;
      this.status = data.status;
      this.type = data.type;
      this.last_seen = data.last_seen;
      this.created_at = data.created_at;

      sessionStorage.setItem(
        "user",
        JSON.stringify({
          id: this.id,
          identifier: this.identifier,
          name: this.name,
          rights: this.rights,
          status: this.status,
          type: this.type,
          last_seen: this.last_seen,
          created_at: this.created_at,
          access_token: this.access_token,
          refresh_token: this.refresh_token,
        })
      );
    },

    setAccessToken(access_token) {
      this.access_token = access_token;
      sessionStorage.setItem("access_token", access_token);
    },

    setRefreshToken(refresh_token) {
      this.refresh_token = refresh_token;
      sessionStorage.setItem("refresh_token", refresh_token);
    },

    restore() {
      const stored = sessionStorage.getItem("user");
      if (stored) {
        const data = JSON.parse(stored);
        Object.assign(this, data);
      }

      const accessToken = sessionStorage.getItem("access_token");
      const refreshToken = sessionStorage.getItem("refresh_token");
      if (accessToken) this.access_token = accessToken;
      if (refreshToken) this.refresh_token = refreshToken;
    },

    clear() {
      this.$reset();
      sessionStorage.removeItem("user");
    },

    clearTokens() {
      this.access_token = null;
      this.refresh_token = null;
      sessionStorage.removeItem("access_token");
      sessionStorage.removeItem("refresh_token");
    },
  },
});
