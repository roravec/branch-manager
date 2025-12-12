import "./assets/main.css";

import { createApp } from "vue";
import App from "./App.vue";
import { createPinia } from 'pinia'

//leaflet fix after vite-svg-loader
import L from "leaflet";
import "leaflet/dist/leaflet.css";
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: new URL("leaflet/dist/images/marker-icon-2x.png", import.meta.url).href,
  iconUrl: new URL("leaflet/dist/images/marker-icon.png", import.meta.url).href,
  shadowUrl: new URL("leaflet/dist/images/marker-shadow.png", import.meta.url).href,
});

const app = createApp(App)

const pinia = createPinia()
app.use(pinia) 

app.mount('#app')
