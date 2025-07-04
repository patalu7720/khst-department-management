<template>
    <Card class="mb-4">
        <template #content>
            <div class="grid grid-cols-2 md:grid-cols-4 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Material</label>
                    <InputText v-model="filters.material" @keydown.enter="applySearch" size="small"
                        placeholder="Nhập material" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Batch</label>
                    <InputText v-model="filters.batch" @keydown.enter="applySearch" size="small"
                        placeholder="Nhập batch" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Người tạo</label>
                    <InputText v-model="filters.created_user" @keydown.enter="applySearch" size="small"
                        placeholder="Nhập người tạo" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ngày tạo</label>
                    <DatePicker v-model="filters.created_at_range" size="small" selectionMode="range" showIcon
                        class="w-full" placeholder="Từ ngày - đến ngày" dateFormat="dd/mm/yy" :manualInput="false" />
                </div>
            </div>
            <div class="flex justify-end mt-4 gap-2">
                <Button label="Tìm kiếm" size="small" icon="pi pi-search" @click="applySearch" />
                <Button label="Đặt lại" size="small" icon="pi pi-times" severity="secondary" @click="resetSearch" />
            </div>
        </template>
    </Card>

    <div class="flex mb-4">
        <Button label="Thêm" size="small" icon="pi pi-plus" @click="showInsertDialog" />
    </div>

    <!-- Bảng dữ liệu -->
    <DataTable ref="dt" :value="chips" size="small" paginator :rows="15" dataKey="id" :loading="loading"
        class="p-datatable-sm" scrollable stripedRows showGridlines tableStyle="min-width: 50rem">
        <template #header>
            <div class="text-end">
                <Button icon="pi pi-external-link" size="small" label="Xuất file" @click="exportCSV($event)"
                    severity="success" raised />
            </div>
        </template>
        <Column field="material" header="Material" style="min-width: 12rem">
            <template #body="{ data }">
                {{ data.material }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" size="small" type="text" @input="filterCallback()"
                    placeholder="Tìm material" fluid />
            </template>
        </Column>
        <Column field="batch" header="Batch" style="min-width: 12rem">
            <template #body="{ data }">
                {{ data.batch }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" size="small" type="text" @input="filterCallback()"
                    placeholder="Tìm batch" fluid />
            </template>
        </Column>
        <Column field="quantity" header="Số lượng" style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatNumber(slotProps.data.quantity) }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" size="small" type="text" @input="filterCallback()"
                    placeholder="Tìm số lượng" fluid />
            </template>
        </Column>
        <Column field="created_user" header="Người tạo" style="min-width: 12rem">
            <template #body="{ data }">
                {{ data.created_user }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" size="small" type="text" @input="filterCallback()"
                    placeholder="Tìm người tạo" fluid />
            </template>
        </Column>
        <Column :exportable="false" style="width: 5rem" bodyClass="text-center" frozen alignFrozen="right" header="#">
            <template #body="slotProps">
                <div class="flex flex-row gap-2" v-if="slotProps.data.created_user === user.username">
                    <Button size="small" icon="pi pi-pencil" variant="outlined" @click="editChip(slotProps.data)" />
                    <Button size="small" icon="pi pi-trash" severity="danger" @click="confirmDelete(slotProps.data)" />
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Dialog thêm/sửa -->
    <Dialog v-model:visible="showDialog" modal header="Thông tin Chip" :style="{ width: '400px' }">
        <Form v-slot="$form" :initialValues :resolver @submit="_save" class="flex flex-col gap-4 w-full">
            <div>
                <label class="block mb-1 text-sm font-medium">Material</label>
                <InputText name="material" v-model="initialValues.material" fluid placeholder="Nhập material" />
                <Message v-if="$form.material?.invalid" severity="error" size="small" variant="simple">{{
                    $form.material.error?.message }}</Message>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Batch</label>
                <InputText name="batch" v-model="initialValues.batch" fluid placeholder="Nhập batch" />
                <Message v-if="$form.batch?.invalid" severity="error" size="small" variant="simple">{{
                    $form.batch.error?.message }}</Message>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium">Quantity</label>
                <InputNumber name="quantity" v-model="initialValues.quantity" locale="en-US" :minFractionDigits="0"
                    :maxFractionDigits="2" suffix=" KG" fluid placeholder="Nhập số lượng" />
                <Message v-if="$form.quantity?.invalid" severity="error" size="small" variant="simple">{{
                    $form.quantity.error?.message }}</Message>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <Button label="Hủy" severity="secondary" @click="showDialog = false" />
                <Button label="Lưu" type="submit" :loading="loadingSubmit" icon="pi pi-check" />
            </div>
        </Form>
    </Dialog>

    <!-- Dialog xóa -->
    <Dialog v-model:visible="showDeleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="itemDelete">Bạn có muốn xóa batch <b>{{ itemDelete.batch }}</b> ?</span>
        </div>
        <template #footer>
            <Button label="Hủy" icon="pi pi-times" text @click="showDeleteDialog = false" />
            <Button label="Đồng ý" :loading="loadingSubmit" icon="pi pi-check" @click="_delete" />
        </template>
    </Dialog>
    <Toast />
</template>

<script setup>
import dayjs from 'dayjs'
import { ref, onMounted } from 'vue'
import axios from '@/axios'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Toast from 'primevue/toast'
import Card from 'primevue/card'
import DatePicker from 'primevue/datepicker'
import { useToast } from 'primevue/usetoast'
import Message from 'primevue/message'
import { Form } from '@primevue/forms';

const chips = ref([])
const loading = ref(false)
const showDialog = ref(false)
const showDeleteDialog = ref(false);
const itemDelete = ref(null);
const toast = useToast()
const loadingSubmit = ref(false);
const dt = ref();

const user = JSON.parse(localStorage.getItem('user') || '{}')

const filters = ref({
    material: '',
    batch: '',
    created_user: '',
    created_at_range: [
        new Date(dayjs().startOf('month').format('YYYY-MM-DD')),
        new Date(dayjs().endOf('day').format('YYYY-MM-DD'))
    ]
})

const initialValues = ref({
    id: null,
    material: '',
    batch: '',
    quantity: null,
});

const fetchChips = async () => {
    loading.value = true

    try {
        const params = {
            material: filters.value.material,
            batch: filters.value.batch,
            created_user: filters.value.created_user,
        }

        if (filters.value.created_at_range?.length === 2) {
            params.from_date = dayjs(filters.value.created_at_range[0]).format('YYYY-MM-DD')
            if (filters.value.created_at_range[1] === null)
                params.to_date = dayjs(filters.value.created_at_range[0]).format('YYYY-MM-DD')
            else
                params.to_date = dayjs(filters.value.created_at_range[1]).format('YYYY-MM-DD')
        }

        const res = await axios.get('/chips', { params })
        chips.value = res.data
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Lỗi tải dữ liệu', life: 3000 })
    } finally {
        loading.value = false
    }
}

function applySearch() {
    fetchChips()
}

function resetSearch() {
    filters.value = {
        material: '',
        batch: '',
        created_user: '',
        created_at_range: [
            new Date(dayjs().startOf('month').format('YYYY-MM-DD')),
            new Date(dayjs().endOf('day').format('YYYY-MM-DD'))
        ]
    }
    applySearch()
}

function showInsertDialog() {
    initialValues.value.id = null
    initialValues.value.material = ''
    initialValues.value.batch = ''
    initialValues.value.quantity = null
    showDialog.value = true
}

const resolver = ({ values }) => {
    const errors = {};

    if (!values.material.trim()) {
        errors.material = [{ message: 'Chưa nhập material' }];
    }

    if (!values.batch.trim()) {
        errors.batch = [{ message: 'Chưa nhập batch' }];
    }

    if (!values.quantity) {
        errors.quantity = [{ message: 'Chưa nhập số lượng' }];
    }

    return {
        errors
    };
};


const _save = async ({ valid }) => {
    if (valid) {
        loadingSubmit.value = true
        try {
            let res;
            if (initialValues.value.id) {
                res = await axios.put(`/chips/${initialValues.value.id}`, initialValues.value)
                const index = chips.value.findIndex(c => c.id === res.data.id);
                if (index !== -1) chips.value[index] = res.data;
                toast.add({ severity: 'success', summary: 'Cập nhật thành công', life: 2000 })
            } else {
                console.log(initialValues.value.quantity)
                res = await axios.post('/chips', initialValues.value)
                chips.value.unshift(res.data); // ✅ Thêm trực tiếp vào danh sách
                toast.add({ severity: 'success', summary: 'Thêm mới thành công', life: 2000 })
            }
            showDialog.value = false
        } catch (err) {
            toast.add({ severity: 'error', summary: 'Thất bại', detail: err?.response?.data?.message || 'Lỗi xảy ra', life: 3000 })
        } finally {
            loadingSubmit.value = false
        }
    }
}

const editChip = (chip) => {
    initialValues.value = { ...chip }
    showDialog.value = true
}

const confirmDelete = (item) => {
    itemDelete.value = item;
    showDeleteDialog.value = true;
};

const _delete = async () => {
    if (!itemDelete.value) return;
    try {
        loadingSubmit.value = true
        await axios.delete(
            `/chips/${itemDelete.value.id}`
        );

        // Xoá trực tiếp trên frontend
        const index = chips.value.findIndex(c => c.id === itemDelete.value.id);
        if (index !== -1) {
            chips.value.splice(index, 1);
        }

        showDeleteDialog.value = false;
        itemDelete.value = {};
        toast.add({
            severity: "success",
            summary: "Successful",
            detail: "Xóa thành công",
            life: 3000,
        });
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Thông báo",
            detail: "Xóa thất bại",
            life: 3000,
        });
    } finally {
        loadingSubmit.value = false;
    }
};

const formatNumber = (value) => {
    if (value == null) return ''
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(value)
}

const exportCSV = () => {
    dt.value.exportCSV();
};

onMounted(fetchChips)
</script>
