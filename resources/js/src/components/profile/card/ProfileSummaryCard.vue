<script setup lang="ts">
import { ref } from 'vue';
import { Check, Copy } from 'lucide-vue-next';
import useClipboard from 'vue-clipboard3';
import Avatar from '@/components/Base/Avatar/Avatar.vue';
import type { Patient, User } from '@/types';

defineProps<{
    user: User;
    patient: Patient | null;
}>();

const { toClipboard } = useClipboard();
const copied = ref(false);

const copyDocument = async (document: string) => {
    await toClipboard(document);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
};
</script>

<template>
    <div class="panel flex items-center gap-4">
        <Avatar :name="user.name" size="md" />
        <div>
            <div class="flex flex-col md:flex-row md:items-center md:gap-2">
                <h5 class="text-lg font-semibold dark:text-white-light">{{ user.name }}</h5>
                <span class="text-white-dark"> {{ user.email }}</span>
            </div>
            <div v-if="patient" class="flex items-center gap-1.5 text-sm mt-1">
                RUT:
                {{ patient.formatted_document }}
                <button
                    type="button"
                    v-tippy="copied ? 'Copiado' : 'Copiar RUT'"
                    class="text-white-dark hover:text-primary"
                    @click="copyDocument(patient.formatted_document)"
                >
                    <Check v-if="copied" :size="14" class="text-success" />
                    <Copy v-else :size="14" />
                </button>
            </div>
        </div>
    </div>
</template>
