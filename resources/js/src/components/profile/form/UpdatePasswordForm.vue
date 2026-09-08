<script setup lang="ts">
import { computed, ref } from 'vue';
import useVuelidate from '@vuelidate/core';
import { useForm } from '@inertiajs/vue3';
import { helpers, minLength, required, sameAs } from '@vuelidate/validators';
import { Eye, EyeOff, Info, KeyRound, Lock, LoaderCircle } from 'lucide-vue-next';
import { useZiggyRoute } from '@/composables/useRoute';

const emit = defineEmits<{
    (e: 'showNotification', message: string, type: string): void;
}>();

const route = useZiggyRoute();

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const rules = computed(() => ({
    current_password: {
        required: helpers.withMessage('La clave actual es requerida.', required),
    },
    password: {
        required: helpers.withMessage('La nueva clave es requerida.', required),
        minLength: helpers.withMessage('La clave debe tener al menos 8 caracteres.', minLength(8)),
    },
    password_confirmation: {
        required: helpers.withMessage('Debes confirmar la nueva clave.', required),
        sameAsPassword: helpers.withMessage('Las claves no coinciden.', sameAs(form.password)),
    },
}));
const v$ = useVuelidate(rules, form);

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();

        form.put(route('profile.update-password'), {
            onSuccess: () => {
                form.reset();
                emit('showNotification', 'Clave actualizada correctamente.', 'success');
            },
            onError: () => {
                emit('showNotification', 'No fue posible actualizar tu clave, verifica los datos.', 'error');
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
                    <Lock :size="20" class="dark:text-white" />
                </div>
                <div class="relative flex-1">
                    <input
                        id="current_password"
                        name="current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        placeholder="Clave actual"
                        autocomplete="current-password"
                        v-model="v$.current_password.$model"
                        class="form-input ltr:rounded-l-none rtl:rounded-r-none ltr:pr-10 rtl:pl-10 dark:text-white"
                        :class="{ 'border-danger': (v$.current_password.$invalid && v$.current_password.$dirty) || (form.errors as any).current_password }"
                    />
                    <button
                        type="button"
                        tabindex="-1"
                        class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-3 text-gray-400 hover:text-primary"
                        @click="showCurrentPassword = !showCurrentPassword"
                    >
                        <EyeOff v-if="showCurrentPassword" :size="18" />
                        <Eye v-else :size="18" />
                    </button>
                </div>
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.current_password.$invalid && v$.current_password.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.current_password.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).current_password">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).current_password }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div
                    class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]"
                >
                    <KeyRound :size="20" class="dark:text-white" />
                </div>
                <div class="relative flex-1">
                    <input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Nueva clave"
                        autocomplete="new-password"
                        v-model="v$.password.$model"
                        class="form-input ltr:rounded-l-none rtl:rounded-r-none ltr:pr-10 rtl:pl-10 dark:text-white"
                        :class="{ 'border-danger': (v$.password.$invalid && v$.password.$dirty) || (form.errors as any).password }"
                    />
                    <button
                        type="button"
                        tabindex="-1"
                        class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-3 text-gray-400 hover:text-primary"
                        @click="showPassword = !showPassword"
                    >
                        <EyeOff v-if="showPassword" :size="18" />
                        <Eye v-else :size="18" />
                    </button>
                </div>
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.password.$invalid && v$.password.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.password.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).password">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).password }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div
                    class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]"
                >
                    <KeyRound :size="20" class="dark:text-white" />
                </div>
                <div class="relative flex-1">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        placeholder="Confirma la nueva clave"
                        autocomplete="new-password"
                        v-model="v$.password_confirmation.$model"
                        class="form-input ltr:rounded-l-none rtl:rounded-r-none ltr:pr-10 rtl:pl-10 dark:text-white"
                        :class="{
                            'border-danger':
                                (v$.password_confirmation.$invalid && v$.password_confirmation.$dirty) || (form.errors as any).password_confirmation,
                        }"
                    />
                    <button
                        type="button"
                        tabindex="-1"
                        class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-3 text-gray-400 hover:text-primary"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                    >
                        <EyeOff v-if="showPasswordConfirmation" :size="18" />
                        <Eye v-else :size="18" />
                    </button>
                </div>
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.password_confirmation.$invalid && v$.password_confirmation.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.password_confirmation.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).password_confirmation">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).password_confirmation }}
                </span>
            </small>
        </div>

        <div class="flex justify-end border-t dark:border-gray-700 mt-7 pt-4">
            <button type="submit" class="btn btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                <KeyRound v-if="!form.processing" class="mr-1" :size="16" />
                <LoaderCircle v-else class="animate-spin" :size="16" />
                Actualizar clave
            </button>
        </div>
    </form>
</template>
