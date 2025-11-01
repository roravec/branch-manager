import axios from "axios";
import { useUserStore } from "@/stores/user";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE,
});

api.interceptors.request.use((config) => {
  const userStore = useUserStore();
  const accessToken = userStore.access_token;
  if (accessToken) {
    config.headers["Authorization"] = `Bearer ${accessToken}`;
  }

  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const userStore = useUserStore();
    const originalRequest = error.config;
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      const refreshToken = userStore.refresh_token;
      if (refreshToken) {
        try {
          const res = await axios.post(import.meta.env.VITE_API_BASE + "/authrefresh", null, {
            headers: { "X-Refresh-Token": refreshToken },
          });
          console.log(res);
          userStore.setAccessToken(res.data.access_token);
          userStore.setRefreshToken(res.data.refresh_token);
          originalRequest.headers["Authorization"] = `Bearer ${res.data.access_token}`;
          return api(originalRequest);
        } catch (err) {
          sessionStorage.removeItem("access_token");
          sessionStorage.removeItem("refresh_token");
          window.location.reload();
        }
      }
    }

    if (error.response?.status === 403) {
      console.warn("Access forbidden: ", error);
    }
    return Promise.reject(error);
  }
);

export default api;
