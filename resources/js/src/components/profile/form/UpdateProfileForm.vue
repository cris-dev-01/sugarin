<script setup lang="ts">
import { computed } from 'vue';
import useVuelidate from '@vuelidate/core';
import { useForm } from '@inertiajs/vue3';
import { email, helpers, maxLength, required } from '@vuelidate/validators';
import { Info, LoaderCircle, Mail, UserRound, UserRoundCheck } from 'lucide-vue-next';
import { useZiggyRoute } from '@/composables/useRoute';
import type { User } from '@/types';

const props = defineProps<{
    user: User;
}>();

const emit = defineEmits<{
    (e: 'showNotification', message: string, type: string): void;
}>();

const route = useZiggyRoute();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

const rules = computed(() => ({
    name: {
        required: helpers.withMessage('Los nombres son requeridos.', required),
        maxLength: helpers.withMessage('El campo no debe superar 255 caracteres.', maxLength(255)),
    },
    email: {
        required: helpers.withMessage('El correo electrónico es requerido.', required),
        email: helpers.withMessage('El correo electrónico no es válido.', email),
        maxLength: helpers.withMessage('El campo no debe superar 255 caracteres.', maxLength(255)),
    },
}));
const v$ = useVuelidate(rules, form);

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();

        form.put(route('profile.update'), {
            onSuccess: () => {
                emit('showNotification', 'Datos actualizados correctamente.', 'success');
            },
            onError: () => {
                emit('showNotification', 'No fue posible actualizar tus datos, verifica la información.', 'error');
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="handleForm">
        <div>
            <div class="flex mb-1">
                <div
                    class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]"
                >
                    <UserRound :size="20" class="dark:text-white" />
                </div>
                <input
                    id="name"
                    name="name"
                    type="text"
                    placeholder="Nombres"
                    v-model="v$.name.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{ 'border-danger': (v$.name.$invalid && v$.name.$dirty) || (form.errors as any).name }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.name.$invalid && v$.name.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.name.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).name">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).name }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div
                    class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]"
                >
                    <Mail :size="20" class="dark:text-white" />
                </div>
                <input
                    id="email"
                    name="email"
                    type="email"
                    placeholder="Correo electrónico"
                    v-model="v$.email.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{ 'border-danger': (v$.email.$invalid && v$.email.$dirty) || (form.errors as any).email }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.email.$invalid && v$.email.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.email.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).email">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).email }}
                </span>
            </small>
        </div>

        <div class="flex justify-end border-t dark:border-gray-700 mt-7 pt-4">
            <button type="submit" class="btn btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                <UserRoundCheck v-if="!form.processing" class="mr-1" :size="16" />
                <LoaderCircle v-else class="animate-spin" :size="16" />
                Guardar cambios
            </button>
        </div>
    </form>
</template>
