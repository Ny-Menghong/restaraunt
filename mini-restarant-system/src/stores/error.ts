import { defineStore } from "pinia";

interface MyError {
    status: number;
    message: string;
}
export const useErrorStore = defineStore("errors", {
    state: () => ({
        myerror: null as MyError | null,
    }),
    getters: {
        // getters here
    },
    actions: {
        setError(myerror: MyError) {
            this.myerror = myerror;
        },
        clearError() {
            this.myerror = null;
        },
    },
});