<script lang="ts" setup>
import { ref, computed } from 'vue';
import { Head } from "@inertiajs/vue3";
import { 
    InboxIcon,
    Plus,
} from 'lucide-vue-next';
import AppLayout from "@/Layouts/AppLayout.vue";
import CreationSlideover from '@/components/glucose-ranges/slideover/CreationSlideover.vue';
import DeleteModal from '@/components/glucose-ranges/modal/DeleteModal.vue';
import EditionSlideover from '@/components/glucose-ranges/slideover/EditionSlideover.vue';
import Notification from "@/components/Base/Notification/Notification.vue";
import Vue3Datatable from '@bhplugin/vue3-datatable';
import type { GlucoseRange, Notification as NotificationType } from "@/types";

const props = defineProps<{
    glucoseRanges: GlucoseRange[];
}>();
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
        title: 'ID', 
        isUnique: true,
        width: '80px'
    },
    { 
        field: 'fasting_range', 
        title: 'Rango Glucosa en Ayuno (mg/dL)',
        slotMode: true,
        filter: false,
        sort: false
    },
    { 
        field: 'non_fasting_range', 
        title: 'Rango Glucosa Normal (mg/dL)',
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

const rows = computed(() => props.glucoseRanges || []);
const notificationIsOpen = ref(false);
const notification = ref<NotificationType>({ messageNotification: '', typeNotification: '' });
const showCreationSlideover = ref(false);
const showEditionSlideover = ref(false);
const showDeleteModal = ref(false);
const selectedRange = ref<GlucoseRange | null>(null);

const toggleCreationSlideover = () => {
    showCreationSlideover.value = !showCreationSlideover.value;
};

const showNotification = (message: string, type: string) => {
    notification.value = { messageNotification: message, typeNotification: type };
    notificationIsOpen.value = true;
};

const toggleEditionSlideover = (range: GlucoseRange | null) => {
    selectedRange.value = range;
    showEditionSlideover.value = !showEditionSlideover.value;
};

const updateGlucoseRanges = (range: GlucoseRange) => {
    const index = props.glucoseRanges.findIndex(r => r.id === range.id);
    if (index !== -1) {
        props.glucoseRanges[index] = range;
    }
};

const toggleDeleteModal = (range: GlucoseRange | null) => {
    selectedRange.value = range;
    showDeleteModal.value = !showDeleteModal.value;
};

const removeGlucoseRanges = (range: GlucoseRange) => {
    const index = props.glucoseRanges.findIndex(r => r.id === range.id);
    if (index !== -1) {
        props.glucoseRanges.splice(index, 1);
    }
};
</script>

<template>
    <Head title="Parámetros" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li>
                    <span>Parámetros</span>
                </li>
            </ul>
            <div class="panel mt-6 pb-0">
                <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Rangos de glucosa disponibles</h5>
                <div class="flex justify-end">
                    <button 
                        type="button" 
                        class="btn btn-primary"
                        @click="toggleCreationSlideover"
                        >
                        <Plus
                            class="mr-1"
                            :size="16"
                        />
                        Crear nuevo rango
                    </button>
                </div>
                
                <div class="datatable mt-6">
                    <div
                        v-if="rows.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500"
                    >
                        <InboxIcon :size="36" class="mb-4 opacity-40" />
                        <p class="text-base font-medium">No hay datos disponibles.</p>
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
                        <template #fasting_range="data">
                            <div class="flex items-center gap-1">
                                <span class="font-semibold text-warning">{{ data.value.min_fasting_value }}</span>
                                <span class="text-gray-500">-</span>
                                <span class="font-semibold text-warning">{{ data.value.max_fasting_value }}</span>
                            </div>
                        </template>

                        <template #non_fasting_range="data">
                            <div class="flex items-center gap-1">
                                <span class="font-semibold text-primary">{{ data.value.min_non_fasting_value }}</span>
                                <span class="text-gray-500">-</span>
                                <span class="font-semibold text-primary">{{ data.value.max_non_fasting_value }}</span>
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
            @toggleCreationSlideover="toggleCreationSlideover"
            @showNotification="showNotification"
        />

        <EditionSlideover
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
        />

        <Notification
            :isOpen="notificationIsOpen"
            :notification="notification" 
            @close="notificationIsOpen = false"
        />
        
    </AppLayout>
</template>


