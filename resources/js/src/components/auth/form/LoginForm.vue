<script lang="ts" setup>
import { ref } from 'vue';
import { Eye, EyeOff, LoaderCircle, LockKeyhole, LogIn, Mail } from 'lucide-vue-next';
import useVuelidate from "@vuelidate/core";
import { useForm } from "@inertiajs/vue3";
import { required, email } from "@vuelidate/validators";

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
});
const rules = {
    email: { required, email },
    password: { required },
};
const v$ = useVuelidate(rules, form);

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();
        form.post('/login', {
            onSuccess: () => {
                form.reset("email", "password");
                v$.value.$reset();
                form.clearErrors();
            },
            onFinish: () => {
                form.reset("password");
            },
        });
    }
}
</script>
<template>
    <form
        @submit.prevent="handleForm"
        class="space-y-5 dark:text-white"
    >
        <div>
            <label for="email">Email</label>
            <div class="relative text-white-dark">
                <input
                    id="email"
                    type="email"
                    placeholder=""
                    class="form-input ps-10 placeholder:text-white-dark"
                    v-model="form.email"
                />
                <span class="absolute start-4 top-1/2 -translate-y-1/2">
                    <Mail
                        :size="16"
                    />
                </span>
            </div>
            <small class="mt-2 text-danger">
                <span v-if="v$.email.$invalid && v$.email.$dirty">
                    El correo es requerido y debe ser tener un formato válido.
                </span>
                <span v-else-if="form.errors.email">
                    {{ form.errors.email }}
                </span>
            </small>
        </div>
        <div>
            <label for="password">Clave</label>
            <div class="relative text-white-dark">
                <input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder=""
                    class="form-input ps-10 pe-10 placeholder:text-white-dark"
                    v-model="form.password"
                />
                <span class="absolute start-4 top-1/2 -translate-y-1/2">
                    <LockKeyhole
                        :size="16"
                    />
               </span>
                <button
                    type="button"
                    tabindex="-1"
                    class="absolute end-4 top-1/2 -translate-y-1/2 hover:text-primary"
                    @click="showPassword = !showPassword"
                >
                    <EyeOff v-if="showPassword" :size="16" />
                    <Eye v-else :size="16" />
                </button>
            </div>
            <small class="mt-2 text-danger">
                <span v-if="v$.password.$invalid && v$.password.$dirty">
                    La clave es requerida.
                </span>
                <span v-else-if="form.errors.password">
                    {{ form.errors.password }}
                </span>
            </small>
        </div>

        <button type="submit" class="btn bg-success/70 text-white !mt-6 flex gap-2 w-full border-0 uppercase shadow-[0_10px_20px_-10px_rgba(67,97,238,0.44)]">
            <LogIn
                v-if="!form.processing"
                :size="16"
            />
            <LoaderCircle
                v-else
                class="animate-spin"
                :size="16"
            />
            {{ form.processing ? 'Accediendo...' : 'Iniciar sesión' }}
        </button>
    </form>
</template>
