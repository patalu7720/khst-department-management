<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <Card class="w-full max-w-sm">
            <template #title>
                <div class="text-center text-2xl font-bold text-gray-700">Đăng nhập</div>
            </template>
            <template #content>
                <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Tài khoản</label>
                        <InputText v-model="username" type="text" class="w-full" />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                        <Password v-model="password" toggleMask feedback="false" fluid />
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <Checkbox v-model="remember" :binary="true" inputId="remember" />
                            <label for="remember" class="ml-2">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>
                    <Button label="Đăng nhập" type="submit" class="w-full" :loading="loading" />
                </form>
            </template>
        </Card>
        <Toast />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'primevue/usetoast'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Toast from 'primevue/toast'

const username = ref('')
const password = ref('')
const remember = ref(false)
const loading = ref(false)
const router = useRouter()
const toast = useToast()

const handleLogin = async () => {
    loading.value = true
    try {
        const response = await axios.post('/login', {
            username: username.value,
            password: password.value,
            remember: remember.value
        })
        const { token, user } = response.data
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        toast.add({ severity: 'success', summary: 'Đăng nhập thành công', life: 2000 })
        router.push('/chip')
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Đăng nhập thất bại', detail: err?.response?.data?.message || 'Sai thông tin đăng nhập', life: 3000 })
    } finally {
        loading.value = false
    }
}
</script>

<style scoped></style>
