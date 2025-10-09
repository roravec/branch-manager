<script setup>
import { ref, onMounted } from 'vue'
import { LMap, LTileLayer, LGeoJson, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import districtsData from '../assets/slovakiaDistrictsGeo.json'
import BranchPopup from './BranchPopup/BranchPopup.vue'
import branchDataTest from './branchTests.json'

const mapRef = ref(null);
const center = ref([48.669, 19.699]);
const districts = ref(districtsData);
const branches = ref(branchDataTest);

const onMapReady = () => {
  if (!mapRef.value) return;
  const map = mapRef.value.leafletObject
  map.zoomControl.setPosition('bottomright')
}
</script>

<template>
  <l-map ref="mapRef" :zoomControl="false" :zoom="8" :center="center" :minZoom="8" :maxZoom="18" style="height: 100vh; width: 100vw;"
    @ready="onMapReady">
    <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />

    <l-marker v-for="branch in branches" :key="branch.id" :lat-lng="branch.gps">
      <l-popup :options="{ closeButton: false, minWidth: 250, maxWidth: 400, maxHeight: 400 }">
        <BranchPopup :branch="branch" />
      </l-popup>
    </l-marker>
  </l-map>
</template>