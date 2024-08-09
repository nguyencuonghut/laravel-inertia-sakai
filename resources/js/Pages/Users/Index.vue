<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Thêm" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" @click="confirmDeleteSelected" :disabled="!selectedUsers || !selectedUsers.length" />
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
                <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
                <Column field="status" header="Status" sortable style="min-width: 12rem">
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
                    <label for="name" class="block font-bold mb-3">Tên</label>
                    <InputText id="name" v-model="user.name" required="true" autofocus :invalid="submitted && !user.name" fluid />
                    <small v-if="submitted && !user.name" class="text-red-500">Bạn phải điền tên.</small>
                </div>
                <div>
                    <label for="email" class="block font-bold mb-3">Email</label>
                    <InputText id="email" v-model.trim="user.email" required="true" autofocus :invalid="submitted && !user.email" fluid />
                    <small v-if="submitted && !user.email" class="text-red-500">Bạn phải điền email.</small>
                    {{ user.email }}
                </div>
                <div v-if="isAddUser">
                    <label for="password" class="block font-bold mb-3">Mật khẩu</label>
                    <Password id="password" v-model.trim="user.password" required="true" autofocus :invalid="submitted && !user.password" fluid />
                    <small v-if="submitted && !user.password" class="text-red-500">Bạn phải điền mật khẩu.</small>
                </div>
                <div v-if="isAddUser">
                    <label for="password_confirmation" class="block font-bold mb-3">Xác nhận mật khẩu</label>
                    <Password id="password_confirmation" v-model.trim="user.password_confirmation" required="true" autofocus :invalid="submitted && !user.password_confirmation" fluid />
                    <small v-if="submitted && !user.password_confirmation" class="text-red-500">Bạn phải xác nhận mật khẩu.</small>
                </div>
                <div>
                    <span class="block font-bold mb-4">Trạng thái</span>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="status_on" v-model="user.status" value="On" name="category"/>
                            <label for="status_on">ON</label>
                        </div>
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="status_off" v-model="user.status" value="Off" name="category"/>
                            <label for="status_off">OFF</label>
                        </div>
                    </div>
                    <small v-if="submitted && !user.status" class="text-red-500">Bạn phải chọn trạng thái.</small>
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
                <span v-if="user"
                    >Bạn chắc chắn muốn xóa <b>{{ user.name }}</b
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
                <span v-if="user">Bạn chắc chắn muốn xóa những người đã chọn?</span>
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

const toast = useToast();
const dt = ref();
const page = usePage();;
const users = computed(() => page.props.users);
const userDialog = ref(false);
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const user = useForm({
    id: '',
    name: '',
    email: '',
    status: '',
    password: '',
    password_confirmation: '',
});
const selectedUsers = ref();
const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});
const submitted = ref(false);
const isAddUser = ref(false);

const openNew = () => {
    user.reset();
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
        if(user?.name?.trim()
            && user?.email
            && user?.password
            && user?.password_confirmation) {
            // Creat new User
            user.post('users', user, {
                onFinish: () => user.reset(),
            });

            isAddUser.value = false;
            toast.add({severity:'success', summary: 'Successful', detail: 'Tạo mới thành công', life: 3000});

            userDialog.value = false;
            user.reset();
        }
    } else {
        // Edit this User
        if(user?.name?.trim() && user?.email) {
            // Edit this User
            user.put(`users/${user.id}`, user, {
                onFinish: () => user.reset(),
            });
            toast.add({severity:'success', summary: 'Successful', detail: 'Cập nhật thành công', life: 3000});

            userDialog.value = false;
            user.reset();
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
    user.delete(`users/${user.id}`, {
            onFinish: () => user.reset(),
        });
    deleteUserDialog.value = false;
    toast.add({severity:'success', summary: 'Successful', detail: 'Xóa người dùng thành công', life: 3000});
};

const setUser = (usr) => {
    user.id = usr.id;
    user.name = usr.name;
    user.email = usr.email;
    user.status = usr.status;
}

const findIndexById = (id) => {
    let index = -1;
    for (let i = 0; i < users.value.length; i++) {
        if (users.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};
const createId = () => {
    let id = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ( var i = 0; i < 5; i++ ) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return id;
}
const exportCSV = () => {
    dt.value.exportCSV();
};
const confirmDeleteSelected = () => {
    deleteUsersDialog.value = true;
};
const deleteSelectedUsers = () => {
    users.value = users.value.filter(val => !selectedUsers.value.includes(val));
    deleteUsersDialog.value = false;
    selectedUsers.value = null;
    toast.add({severity:'success', summary: 'Successful', detail: 'Users Deleted', life: 3000});
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
