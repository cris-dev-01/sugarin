<script lang="ts" setup>
import { ref, computed } from 'vue';
import { Head, usePage } from "@inertiajs/vue3";
import { 
    CircleSlash,
    InboxIcon,
    Plus,
} from 'lucide-vue-next';
import AppLayout from "@/Layouts/AppLayout.vue";
import CreationSlideover from '@/components/patients/slideover/CreationSlideover.vue';
import DeleteModal from '@/components/patients/modal/DeleteModal.vue';
import EditionSlideover from '@/components/patients/slideover/EditionSlideover.vue';
import Notification from "@/components/Base/Notification/Notification.vue";
import Vue3Datatable from '@bhplugin/vue3-datatable';
import type { GlucoseRange, User, Notification as NotificationType, PageProps } from "@/types";

const props = defineProps<{
    patients: User[];
    glucoseRanges: GlucoseRange[];
}>();
const page = usePage<PageProps>();
const paginationLang = ref({
    paginationInfo: "Mostrando {0} a {1} de {2} registros",
    noDataContent: "No hay datos disponibles"
});
const columnFilterLang = ref({
    no_filter: 'Sin filtro',
    contain: 'Contiene',
    not_contain: 'No contiene',
    equal: 'Igual a',
    not_equal: 'Diferente de',
    start_with: 'Comienza con',
    end_with: 'Termina con',
    greater_than: 'Mayor que',
    greater_than_equal: 'Mayor o igual a',
    less_than: 'Menor que',
    less_than_equal: 'Menor o igual a',
    is_null: 'Es nulo',
    is_not_null: 'No es nulo'
});
const cols = ref([
    { 
        field: 'id', 
        title: '#', 
        isUnique: true,
        width: '80px'
    },
    { 
        field: 'document', 
        title: 'Rut',
        slotMode: true,
        filter: false,
        sort: false
    },
    { 
        field: 'name', 
        title: 'Nombres',
        slotMode: true,
        filter: false,
        sort: false
    },
    { 
        field: 'email', 
        title: 'E-mail contacto',
        slotMode: true,
        filter: false,
        sort: false
    },
    { 
        field: 'illness_found_at', 
        title: 'Información debut',
        slotMode: true,
        filter: false,
        sort: false
    },
    { 
        field: 'actions', 
        title: 'Acciones',
        slotMode: true,
        filter: false,
        sort: false,
        width: '120px'
    },
]) || [];

const rows = computed(() =>
    (props.patients || []).map((patient, index) => ({ ...patient, rowNumber: index + 1 }))
);
const notificationIsOpen = ref(false);
const notification = ref<NotificationType>({ messageNotification: '', typeNotification: '' });
const showCreationSlideover = ref(false);
const showEditionSlideover = ref(false);
const showDeleteModal = ref(false);
const selectedPatient = ref<User | null>(null);

const checkPermission = (permission: string) => {
    return page.props.auth.user && page.props.auth.user.permissions.includes(permission);
};

const toggleCreationSlideover = () => {
    showCreationSlideover.value = !showCreationSlideover.value;
};

const showNotification = (message: string, type: string) => {
    notification.value = { messageNotification: message, typeNotification: type };
    notificationIsOpen.value = true;
};

const toggleEditionSlideover = (patient: User | null) => {
    selectedPatient.value = patient;
    showEditionSlideover.value = !showEditionSlideover.value;
};

const updatePatients = (patient: User) => {
    const index = props.patients.findIndex(p => p.id === patient.id);
    if (index !== -1) {
        props.patients[index] = patient;
    }
};

const toggleDeleteModal = (patient: User | null) => {
    selectedPatient.value = patient;
    showDeleteModal.value = !showDeleteModal.value;
};

const removePatients = (patient: User) => {
    const index = props.patients.findIndex(p => p.id === patient.id);
    if (index !== -1) {
        props.patients.splice(index, 1);
    }
};
</script>

<template>
    <Head title="Pacientes" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li>
                    <span>Pacientes</span>
                </li>
            </ul>
            <div class="panel mt-6 pb-0">
                <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Pacientes registrados</h5>
                <div class="flex justify-end">
                    <button 
                        v-if="checkPermission('create-patients')"
                        type="button" 
                        class="btn btn-primary"
                        @click="toggleCreationSlideover"
                        >
                        <Plus
                            class="mr-1"
                            :size="16"
                        />
                        Registrar nuevo paciente
                    </button>
                    <div 
                        v-else 
                        v-tippy="'No tienes permiso para registrar pacientes'" 
                        class="inline-block"
                    >
                        <button 
                            type="button" 
                            class="btn btn-primary"
                            disabled
                            >
                            <Plus
                                class="mr-1"
                                :size="16"
                            />
                            Registrar nuevo paciente
                        </button>
                    </div>
                </div>
                
                <div class="datatable mt-6">
                    <div
                        v-if="rows.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500"
                    >
                        <InboxIcon :size="36" class="mb-4 opacity-40" />
                        <p class="text-base font-medium">No hay pacientes registrados.</p>
                    </div>

                    <vue3-datatable
                        v-else
                        :rows="rows"
                        :columns="cols"
                        :totalRows="rows?.length"
                        :paginationInfo="paginationLang.paginationInfo"
                        :noDataContent="paginationLang.noDataContent"
                        :columnFilterLang="columnFilterLang"
                        skin="whitespace-nowrap bh-table-hover"
                        firstArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                        lastArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg> '
                        previousArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                        nextArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                    >
                        <template #id="data">
                            <div class="flex items-center gap-1">
                                <span class="font-semibold">{{ data.value.rowNumber }}</span>
                            </div>
                        </template>
                        <template #document="data">
                            <div class="flex items-center gap-1">
                                <span class="font-semibold">{{ data.value.patient.formatted_document }}</span>
                            </div>
                        </template>

                        <template #name="data">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-full bg-gray-400 flex items-center justify-center text-white">
                                    {{ data.value.name.charAt(0).toUpperCase() }}
                                </div>
                                <span class="font-semibold">{{ data.value.name }}</span>
                            </div>
                        </template>

                        <template #email="data">
                            <div class="flex items-center gap-1">
                                <a class="text-primary hover:underline" :href="`mailto:${data.value.email}`">{{ data.value.email }}</a>
                            </div>
                        </template>

                        <template #illness_found_at="data">
                            <div class="flex items-center gap-1">
                                <span class="badge badge-outline-danger">
                                    <b>{{ data.value.patient.initial_max_glucose_value }}</b>
                                </span>
                                descubierto el {{ new Date(data.value.patient.illness_found_at).toLocaleDateString() }}
                            </div>
                        </template>

                        <template #actions="data">
                            <div class="flex gap-2">
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-primary"
                                    @click="toggleEditionSlideover(data.value)"
                                >
                                    Editar
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-danger"
                                    @click="toggleDeleteModal(data.value)"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </template>
                    </vue3-datatable>
                </div>
            </div>
        </div>

        <CreationSlideover
            :isOpen="showCreationSlideover"
            :glucoseRanges="props.glucoseRanges"
            @toggleCreationSlideover="toggleCreationSlideover"
            @showNotification="showNotification"
        />

        <!-- <EditionSlideover
            :isOpen="showEditionSlideover"
            :range="selectedRange"
            @toggleEditionSlideover="toggleEditionSlideover"
            @updateGlucoseRanges="updateGlucoseRanges"
            @showNotification="showNotification"
        />

        <DeleteModal
            v-if="selectedRange"
            :show="showDeleteModal"
            :range="selectedRange"
            @showNotification="showNotification"
            @removeGlucoseRanges="removeGlucoseRanges"
            @toggleDeleteModal="toggleDeleteModal"
        /> -->

        <Notification
            :isOpen="notificationIsOpen"
            :notification="notification" 
            @close="notificationIsOpen = false"
        />
        
    </AppLayout>
</template>


