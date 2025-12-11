import { defineStore } from "pinia";
import api from "@/api.js";

export const useSpecializationsStore = defineStore("specializations", {
  state: () => ({
    specializations: [],
    branchSpecs: {},
  }),

  getters: {
    allSpecializations: (state) => state.specializations,

    getBranchSpecStr: (state) => (branchId) => {
      const spec = state.branchSpecs[branchId];
      if (!spec) return "Bez špecializácie";
      return spec.name;
    },
  },

  actions: {
    async loadSpecializations() {
      const res = await api.get("/branchspecs");
      this.specializations = res.data;
    },

    async addSpecialization(name, description) {
      const formData = new FormData();
      formData.append("name", name);
      formData.append("description", description);

      const res = await api.post("/branchspec", formData);
      await this.loadSpecializations();
    },

    async updateSpecialization(id, name, description) {
      const formData = new FormData();
      formData.append("name", name);
      formData.append("description", description);

      const res = await api.post(`/edit/branchspec/${id}`, formData);
      await this.loadSpecializations();
    },

    async deleteSpecialization(id) {
      const res = await api.delete(`/branchspec/${id}`);
      await this.loadSpecializations();
    },

    async assignSpecialization(branchID, specID, hasSpec) {
      const formData = new FormData();
      formData.append("branchId", branchID);
      if (hasSpec) {
        formData.append("branchSpecializationId", specID);
      } else {
        formData.append("specId", specID);
      }

      if (hasSpec) {
        console.log("branch edit spec");
        await api.post(`/edit/branchHasSpec/${branchID}`, formData);
      } else {
        console.log("branch new spec");
        await api.post("/branchHasSpec", formData);
      }
      await this.loadSpecializations();
    },

    async loadBranchSpec(branchId) {
      try {
        const res = await api.get(`/branchHasSpec/${branchId}`);

        if (!res.data || res.data.length === 0) {
          this.branchSpecs[branchId] = null;
          return null;
        }

        const specId = res.data[0].branchSpecializationId;
        const spec = this.allSpecializations.find((s) => s.id == specId);

        const formatted = spec ? { id: spec.id, name: spec.name } : null;
        this.branchSpecs[branchId] = formatted;

        return formatted;
      } catch (err) {
        console.error("loadBranchSpec error:", err);
        this.branchSpecs[branchId] = null;
        return null;
      }
    },
  },
});
