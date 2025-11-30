import { defineStore } from "pinia";
import { getUser } from "../services/auth";

const useAuthStore = defineStore("auth", {
    state: () => {
        return {
            user: null,
            isReady: false,
        };
    },
    getters: {
        isAuthenticated: (state) => !!state.user,
    },
    actions: {
        login(data) {
            this.user = data;
        },
        logout() {
            this.user = null;
        },
        async fetchCurrentUser() {
            try {
                const response = await getUser();
                if (response.data) {
                    this.login(response.data);
                } else {
                    this.logout();
                }
            } catch (e) {
                if (e.response) {
                    console.log(
                        "Error::fetchCurrentUser",
                        e.response?.data?.message
                    );
                } else {
                    console.log(e);
                }
            } finally {
                this.isReady = true;
            }
        },
    },
});

export default useAuthStore;
