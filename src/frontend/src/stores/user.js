import { defineStore } from "pinia";
import axios from "axios";
import { jwtDecode } from "jwt-decode";

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
    token_expiry: null,
    refreshTimer: null,
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

      try {
        const decoded = jwtDecode(access_token);
        this.token_expiry = decoded.exp * 1000;
        this.scheduleTokenRefresh();
      } catch (e) {
        console.error("Invalid token format:", e);
      }
    },

    setRefreshToken(refresh_token) {
      this.refresh_token = refresh_token;
      sessionStorage.setItem("refresh_token", refresh_token);
    },

    async refreshAccessToken() {
      if (!this.refresh_token) return;

      try {
        const res = await axios.post(import.meta.env.VITE_API_BASE + "/authrefresh", null, {
          headers: { "X-Refresh-Token": this.refresh_token },
        });

        this.setAccessToken(res.data.access_token);
        this.setRefreshToken(res.data.refresh_token);
        console.log("Access token refreshed automatically");
      } catch (err) {
        console.error("Token refresh failed:", err);
      }
    },

    scheduleTokenRefresh() {
      if (this.refreshTimer) clearTimeout(this.refreshTimer);

      const now = Date.now();
      const refreshIn = this.token_expiry - now - 60_000;

      if (refreshIn > 0) {
        this.refreshTimer = setTimeout(() => {
          this.refreshAccessToken();
        }, refreshIn);
      }
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
      clearTimeout(this.refreshTimer);
    },

    clearTokens() {
      this.access_token = null;
      this.refresh_token = null;
      sessionStorage.removeItem("access_token");
      sessionStorage.removeItem("refresh_token");
      clearTimeout(this.refreshTimer);
    },
  },
});
