import { defineStore } from "pinia";

interface OrderItem {
    id: number;
    food_id: number;
    quantity: number;
    price: string;
    subtotal: string;
    food?: { id: number; name: string; image: string; price: string };
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    status: string;
    subtotal: string;
    discount: string;
    total: string;
    items: OrderItem[];
    table?: { id: number; table_number: string };
    payment?: { id: number; status: string; amount: string };
}

export const useOrdersStore = defineStore("orders", {
    state: () => ({
        orders: [] as Order[],
    }),
    getters: {
        totalOrders: (state) => state.orders.length,
        pendingOrders: (state) => state.orders.filter((o) => o.status === "pending"),
    },
    actions: {
        setOrders(orders: Order[]) {
            this.orders = orders;
        },
    },
});
