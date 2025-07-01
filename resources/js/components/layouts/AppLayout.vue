<template>
    <div class="flex flex-row min-h-screen">
        <!-- Overlay for mobile -->
        <div v-if="toggle && windowWidth < 768" class="fixed inset-0 z-30" style="background: rgba(0,0,0,0.2);"
            @click="toggle = false"></div>
        <!-- Sidebar -->
        <div v-show="toggle" :class="[
            'p-2 z-40 transition-all duration-300 min-h-screen border-r border-gray-200',
            windowWidth < 768
                ? 'fixed top-0 left-0 min-w-70 bg-white shadow-lg'
                : 'min-w-70 static'
        ]">
            <div class="flex flex-col gap-2">
                <div v-for="(item, idx) in items" :key="idx" class="text-sm">

                    <!-- 🔹 ITEM CÓ SUBMENU (cha) -->
                    <div v-if="item.items"
                        class="flex items-center px-4 py-2 font-semibold cursor-pointer hover:bg-gray-100 rounded text-gray-700"
                        @click="toggleSub(idx)">
                        <i :class="[item.icon, 'mr-2']" />
                        <span>{{ item.label }}</span>
                        <i
                            :class="['pi', openSub === idx ? 'pi-angle-up' : 'pi-angle-down', 'ml-auto text-gray-500']" />
                    </div>

                    <!-- 🔸 ITEM KHÔNG CÓ SUBMENU (1 cấp) -->
                    <router-link v-else :to="item.route"
                        class="flex items-center px-4 py-2 font-semibold rounded hover:bg-gray-100 text-gray-700"
                        :class="{ 'bg-gray-100 text-primary-700': route.path === item.route }" @click="onMenuClick">
                        <i :class="[item.icon, 'mr-2']" />
                        <span>{{ item.label }}</span>
                    </router-link>

                    <!-- 🔻 HIỂN THỊ SUBMENU -->
                    <div v-if="item.items && openSub === idx" class="ml-6 mt-1 space-y-1">
                        <router-link v-for="(sub, subIdx) in item.items" :key="subIdx" :to="sub.route || '#'"
                            class="flex items-center px-3 py-1 rounded hover:bg-gray-100 text-sm" :class="{
                                'bg-gray-100 text-primary-700 font-semibold': route.path === sub.route,
                                'text-red-600 hover:text-red-700': sub.label === 'Đăng xuất'
                            }" @click.prevent="() => {
                                onMenuClick();
                                if (sub.command) sub.command();
                                else if (sub.route) router.push(sub.route);
                            }">
                            <i :class="[sub.icon, 'mr-2']" />
                            <span>{{ sub.label }}</span>
                        </router-link>
                    </div>

                </div>
            </div>
        </div>
        <!-- Main content -->
        <div class="flex flex-col px-6 w-full pb-5">
            <Toast />
            <div class="h-16 py-1 flex items-center gap-4">
                <Button icon="pi pi-align-justify" variant="outlined" severity="secondary" @click="toggle = !toggle"
                    rounded />

                <!-- ✅ BREADCRUMB -->
                <Breadcrumb :model="breadcrumbItems" class="flex-1">
                    <template #item="{ item }">
                        <router-link v-if="item.route" :to="item.route"
                            class="text-gray-600 hover:text-primary-600 font-medium">
                            <i v-if="item.icon" :class="item.icon + ' mr-1'" />
                            {{ item.label }}
                        </router-link>
                        <span v-else class="text-gray-400">
                            <i v-if="item.icon" :class="item.icon + ' mr-1'" />
                            {{ item.label }}
                        </span>
                    </template>
                </Breadcrumb>
            </div>

            <router-view />
        </div>
    </div>
    <div v-if="loadingContent"
        class="fixed top-4 right-6 z-50 flex items-center justify-center bg-white bg-opacity-70 border border-gray-200 rounded-full shadow-lg p-2 transition-all"
        style="box-shadow: 0 4px 24px rgba(0,0,0,0.10);">
        <ProgressSpinner style="width: 36px; height: 36px" strokeWidth="6" fill="transparent" animationDuration=".7s"
            aria-label="Đang tải nội dung" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter } from 'vue-router';
import { useRoute } from 'vue-router';
import Button from 'primevue/button';
import { Toast } from "primevue";
import ProgressSpinner from 'primevue/progressspinner'
import Breadcrumb from 'primevue/breadcrumb'

const user = JSON.parse(localStorage.getItem('user') || '{}');

const route = useRoute();
const router = useRouter();
const toggle = ref(window.innerWidth >= 768);
const windowWidth = ref(window.innerWidth);
const openSub = ref(null);
const breadcrumb = ref(null);
const loadingContent = ref(false)

function handleResize() {
    windowWidth.value = window.innerWidth;
    if (window.innerWidth >= 768) {
        toggle.value = true;
    } else {
        toggle.value = false;
    }
}

function toggleSub(idx) {
    openSub.value = openSub.value === idx ? null : idx;
}

function onMenuClick() {
    if (windowWidth.value < 768) toggle.value = false;
}

onMounted(() => {
    window.addEventListener('resize', handleResize);
    handleResize();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

const breadcrumbItems = computed(() => {
    const segments = route.path.split('/').filter(Boolean)
    const items = [{ icon: 'pi pi-home', route: '/' }]

    let currentPath = ''
    segments.forEach((segment, idx) => {
        currentPath += `/${segment}`
        items.push({
            label: segment.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            route: idx === segments.length - 1 ? null : currentPath
        })
    })

    return items
})

const logout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
}

const items = ref([
    {
        label: 'Chip',
        icon: 'pi pi-home',
        route: '/chip'
    },
    {
        label: user.name,
        icon: 'pi pi-user',
        items: [
            {
                label: 'Thay đổi mật khẩu',
                icon: 'pi pi-key',
                route: '/change-password'
            },
            {
                label: 'Đăng xuất',
                icon: 'pi pi-sign-out',
                command: logout
            }
        ]
    }
    // {
    //     label: 'ATVSLD',
    //     icon: 'pi pi-heart',
    //     items: [
    //         {
    //             label: 'Home',
    //             icon: 'pi pi-home',
    //             route: '/home'
    //         },
    //         {
    //             label: 'Dashboard',
    //             icon: 'pi pi-home',
    //             route: '/dashboard'
    //         },
    //     ]
    // },
    // {
    //     label: user.name,
    //     icon: 'pi pi-user',
    //     items: [
    //         {
    //             label: 'Đổi mật khẩu',
    //             icon: 'pi pi-key',
    //             route: '/atvsld/change-password'
    //         },
    //     ]
    // },
]);

</script>

<style scoped>
:deep(.p-breadcrumb) {
    background: transparent;
    border: none;
    padding: 0;
}

:deep(.p-breadcrumb .p-breadcrumb-list li .p-menuitem-link) {
    color: #4b5563;
    /* text-gray-600 */
}
</style>
