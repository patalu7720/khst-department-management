import { createRouter, createWebHistory } from "vue-router";
import Layout from "../components/layouts/AppLayout.vue";
import Login from "../pages/auth/Login.vue";
import ChangePassword from "../pages/auth/ChangePassword.vue";
import NotFound_404 from '../pages/error/404.vue'
import Chip from "../pages/chip/Chip.vue";

const routes = [
    {
        path: "/login",
        name: "Login",
        component: Login,
    },
    {
        path: "/",
        component: Layout,
        children: [
            {
                path: "/chip",
                name: "Chip",
                component: Chip,
            },
            {
                path: "/change-password",
                name: "Thay đổi mật khẩu",
                component: ChangePassword,
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        name: "404",
        component: NotFound_404,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("token");
    if (to.path !== "/login" && !token) {
        next("/login");
    } else {
        next();
    }
});

export default router;
