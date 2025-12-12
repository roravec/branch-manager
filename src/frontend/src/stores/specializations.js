import { defineStore } from "pinia";
import api from "@/api.js";

export const useSpecializationsStore = defineStore("specializations", {
  state: () => ({
    specializations: [],
    branchSpecs: {}
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
      try {
        const res = await api.get("/branchspecs");  // Endpoint pre špecializácie


        // Uložíme špecializácie do stavu
        this.specializations = res.data;

        // Ak chcete, môžete pridať ďalšiu logiku na priradenie špecializácií k pobočkám.
      } catch (err) {
        console.error("Chyba pri načítaní špecializácií:", err);
        this.error = err.message || "Nepodarilo sa načítať špecializácie";
      }
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
        await api.post(`/edit/branchHasSpec/${branchID}`, formData);
      } else {
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
    async preloadBranchSpecsForBranches(branches) {
      try {
        // najprv načítame zoznam všetkých špecializácií (ak ešte nie sú)
        if (!this.specializations.length) {
          await this.loadSpecializations();
        }

        // pre každú pobočku zavoláme /branchHasSpec/{id}
        for (const branch of branches) {
          if (!branch || !branch.id) continue;

          // ak už pre danú pobočku špec máme, neskáč znova
          if (this.branchSpecs[branch.id]) continue;

          const res = await api.get(`/branchHasSpec/${branch.id}`);
          if (!res.data || res.data.length === 0) {
            this.branchSpecs[branch.id] = null;
            continue;
          }

          const specId = res.data[0].branchSpecializationId;
          const spec = this.allSpecializations.find((s) => s.id == specId);
          this.branchSpecs[branch.id] = spec ? { id: spec.id, name: spec.name } : null;
        }
      } catch (err) {
        console.error("preloadBranchSpecsForBranches error:", err);
      }
    }

  },
});
