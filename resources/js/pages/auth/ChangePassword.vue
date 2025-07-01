<template>
    <div class="max-w-xl w-full mx-auto mt-16 p-8 bg-white shadow-lg rounded-lg">
        <h2 class="text-lg font-bold mb-4">Đổi mật khẩu</h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block mb-1 font-medium">Mật khẩu hiện tại</label>
                <Password v-model="current_password" :feedback="false" toggleMask fluid />
            </div>
            <div>
                <label class="block mb-1 font-medium">Mật khẩu mới</label>
                <Password v-model="new_password" :feedback="false" toggleMask fluid />
            </div>
            <div>
                <label class="block mb-1 font-medium">Xác nhận mật khẩu mới</label>
                <Password v-model="new_password_confirmation" :feedback="false" toggleMask fluid />
            </div>
            <Button type="submit" label="Đổi mật khẩu" :loading="loading" class="w-full" />
        </form>
        <Toast />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from '@/axios'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const current_password = ref('')
const new_password = ref('')
const new_password_confirmation = ref('')
const loading = ref(false)
const toast = useToast()

const handleSubmit = async () => {
    if (new_password.value !== new_password_confirmation.value) {
        toast.add({ severity: 'warn', summary: 'Mật khẩu không khớp', life: 3000 })
        return
    }

    loading.value = true
    try {
        await axios.post('/change-password', {
            current_password: current_password.value,
            new_password: new_password.value,
            new_password_confirmation: new_password_confirmation.value
        })
        toast.add({ severity: 'success', summary: 'Đổi mật khẩu thành công', life: 3000 })
        current_password.value = ''
        new_password.value = ''
        new_password_confirmation.value = ''
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Thất bại', detail: err?.response?.data?.message || 'Lỗi xảy ra', life: 3000 })
    } finally {
        loading.value = false
    }
}
</script>
