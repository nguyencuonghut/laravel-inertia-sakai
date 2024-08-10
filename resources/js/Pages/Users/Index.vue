<template>
    <Head>
        <title>Tất cả người dùng</title>
    </Head>

    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Thêm" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Xóa" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedUsers || !selectedUsers.length" />
                </template>

                <template #end>
                    <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2" auto />
                    <Button label="Export" icon="pi pi-upload" severity="help" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedUsers"
                :value="users"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Tất cả người dùng</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="Id" sortable style="min-width: 16rem"></Column>
                <Column field="name" header="Tên" sortable style="min-width: 16rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
                <Column field="status" header="Trạng thái" sortable style="min-width: 12rem">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusLabel(slotProps.data.status)" />
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteUser(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="userDialog" :style="{ width: '450px' }" header="Chi tiết người dùng" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3 required-field">Tên</label>
                    <InputText id="name" v-model="form.name" autofocus :invalid="submitted && !form.name" fluid />
                    <small v-if="submitted && !form.name" class="text-red-500">Bạn phải điền tên.</small>
                    <small v-if="submitted && errors.name" class="text-red-500">{{ errors.name }}</small>
                </div>
                <div>
                    <label for="email" class="block font-bold mb-3 required-field">Email</label>
                    <InputText id="email" v-model.trim="form.email" autofocus :invalid="submitted && !form.email" fluid />
                    <small v-if="submitted && !form.email" class="text-red-500">Bạn phải điền email.</small>
                    <small v-if="submitted && errors.email" class="text-red-500">{{ errors.email }}</small>
                </div>
                <div v-if="isAddUser">
                    <label for="password" class="block font-bold mb-3 required-field">Mật khẩu</label>
                    <Password id="password" v-model.trim="form.password" autofocus :invalid="submitted && !form.password" fluid />
                    <small v-if="submitted && !form.password" class="text-red-500">Bạn phải điền mật khẩu.</small>
                    <small v-if="submitted && errors.password" class="text-red-500">{{ errors.password }}</small>
                </div>
                <div v-if="isAddUser">
                    <label for="password_confirmation" class="block font-bold mb-3 required-field">Xác nhận mật khẩu</label>
                    <Password id="password_confirmation" v-model.trim="form.password_confirmation" autofocus :invalid="submitted && !form.password_confirmation" fluid />
                    <small v-if="submitted && !form.password_confirmation" class="text-red-500">Bạn phải xác nhận mật khẩu.</small>
                </div>
                <div>
                    <span class="block font-bold mb-4 required-field">Trạng thái</span>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="status_on" v-model="form.status" value="On" name="category"/>
                            <label for="status_on">ON</label>
                        </div>
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="status_off" v-model="form.status" value="Off" name="category"/>
                            <label for="status_off">OFF</label>
                        </div>
                    </div>
                    <small v-if="submitted && !form.status" class="text-red-500">Bạn phải chọn trạng thái.</small>
                    <small v-if="submitted && errors.status" class="text-red-500">{{ errors.status }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Hủy" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Lưu" icon="pi pi-check" @click="saveUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUserDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="form"
                    >Bạn chắc chắn muốn xóa <b>{{ form.name }}</b
                    >?</span
                >
            </div>
            <template #footer>
                <Button label="Không" icon="pi pi-times" text @click="deleteUserDialog = false" />
                <Button label="Có" icon="pi pi-check" @click="deleteUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUsersDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="form">Bạn chắc chắn muốn xóa những người đã chọn?</span>
            </div>
            <template #footer>
                <Button label="Không" icon="pi pi-times" text @click="deleteUsersDialog = false" />
                <Button label="Có" icon="pi pi-check" text @click="deleteSelectedUsers" />
            </template>
        </Dialog>
	</div>
</template>

<script setup>
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useToast } from 'primevue/usetoast';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3';

const toast = useToast();
const dt = ref();
const page = usePage();
const users = computed(() => page.props.users);
defineProps({
    errors: {
        type: Object,
    },
});
const userDialog = ref(false);
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const form = useForm({
    id: null,
    name: null,
    email: null,
    status: null,
    password: null,
    password_confirmation: null,
});
const selectedUsers = ref();
const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});
const submitted = ref(false);
const isAddUser = ref(false);

const openNew = () => {
    form.reset();
    submitted.value = false;
    userDialog.value = true;
    isAddUser.value = true;
};
const hideDialog = () => {
    userDialog.value = false;
    submitted.value = false;
};
const saveUser = () => {
    submitted.value = true;

    // Add new User
    if (isAddUser.value) {
        // Check condition of input
        if(form?.name?.trim()
            && form?.email
            && form?.password
            && form?.password_confirmation) {
            // Creat new User
            form.post('users', {
                onSuccess: () => {
                    form.reset();
                    userDialog.value = false;
                },
            });
        }
    } else {
        // Edit this User
        if(form?.name?.trim() && form?.email) {
            // Edit this User
            form.put(`users/${form.id}`, {
                onSuccess: () => {
                    form.reset();
                    userDialog.value = false;
                    toast.add({severity:'success', summary: 'Successful', detail: 'Cập nhật thành công', life: 3000});
                },
            });
        }

    }
};
const editUser = (usr) => {
    setUser(usr);
    userDialog.value = true;
    isAddUser.value = false;
};
const confirmDeleteUser = (usr) => {
    setUser(usr);
    deleteUserDialog.value = true;
};
const deleteUser = () => {
    form.delete(`users/${form.id}`, {
            onFinish: () => form.reset(),
        });
    deleteUserDialog.value = false;
    toast.add({severity:'success', summary: 'Successful', detail: 'Xóa người dùng thành công', life: 3000});
};

const setUser = (usr) => {
    form.id = usr.id;
    form.name = usr.name;
    form.email = usr.email;
    form.status = usr.status;
}

const exportCSV = () => {
    dt.value.exportCSV();
};
const confirmDeleteSelected = () => {
    deleteUsersDialog.value = true;
};

const deleteSelectedUsers = () => {
    router.post('users/bulkDelete', {users : selectedUsers.value });
    deleteUsersDialog.value = false;
    selectedUsers.value = null;
    toast.add({severity:'success', summary: 'Successful', detail: 'Xóa người dùng thành công', life: 3000});
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'On':
            return 'success';

        case 'Off':
            return 'danger';

        default:
            return null;
    }
};

</script>
