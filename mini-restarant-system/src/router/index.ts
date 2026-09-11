import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Auth/Login.vue";
import Register from "../views/Auth/Register.vue";
import { useAuthStor } from "../stores/auth.ts";
import MainPage from "../views/MainPage.vue";
import Menu from "../views/pages/user/Menu.vue";
import Dashboard from "../views/pages/admin/Dashboard.vue";
import Tables from "../views/pages/admin/Tables.vue";
import Foods from "../views/pages/admin/Foods.vue";
import Categories from "../views/pages/admin/Categories.vue";
import AddTable from "../views/pages/admin/AddTable.vue";
import Orders from "../views/pages/admin/Orders.vue";
import MakeOrder from "../views/pages/admin/MakeOrder.vue";
import Customers from "../views/pages/admin/Customers.vue";
import Users from "../views/pages/admin/Users.vue";
import POS from "../views/pages/admin/POS.vue";
import Reports from "../views/pages/admin/Reports.vue";
import Payment from "../views/pages/admin/Payment.vue";

const routes = [
    {
        path: "/menu/:qr_token",
        name: "menu",
        component: Menu,
    },
    {
        path: "/",
        component: MainPage,
        meta: { requiresAuth: true },
        children: [
            { path: "", name: "dashboard", component: Dashboard },
            { path: "pos", name: "pos", component: POS },
            { path: "orders", name: "orders", component: Orders },
            { path: "make-order", name: "makeorder", component: MakeOrder },
            { path: "table", name: "table", component: Tables },
            { path: "add-table", name: "add-table", component: AddTable },
            { path: "foods", name: "foods", component: Foods },
            { path: "categories", name: "categories", component: Categories },
            { path: "customers", name: "customers", component: Customers },
            { path: "payments", name: "payments", component: Payment },
            { path: "users", name: "users", component: Users },
            { path: "reports", name: "reports", component: Reports },
        ],
    },
    {
        path: "/login",
        name: "login",
        component: Login,
        meta: {
            guestOnly: true,
        },
    },
    {
        path: "/register",
        name: "register",
        component: Register,
        meta: {
            guestOnly: true,
        },
    },
];
const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});
router.beforeEach((to) => {
    const auth = useAuthStor();
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return {
            name: "login",
        };
    }
    if (to.meta.guestOnly && auth.isAuthenticated) {
        return {
            name: "dashboard",
        };
    }
    return true;
});
export default router;
