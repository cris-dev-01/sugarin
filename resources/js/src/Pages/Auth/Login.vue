<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import LoginForm from "@/components/auth/form/LoginForm.vue";
</script>

<template>
    <Head title="Inicio de sesión" />
    <AuthLayout>
        <div>
            <div class="absolute inset-0">
                <img src="/assets/images/auth/bg-gradient.png" alt="image" class="h-full w-full object-cover" />
            </div>

            <div
                class="relative flex min-h-screen items-center justify-center bg-[url(/assets/images/auth/map.png)] bg-cover bg-center bg-no-repeat px-6 py-10 dark:bg-[#060818] sm:px-16"
            >
                <img src="/assets/images/auth/coming-soon-object1.png" alt="image" class="absolute left-0 top-1/2 h-full max-h-[893px] -translate-y-1/2" />
                <img src="/assets/images/auth/coming-soon-object2.png" alt="image" class="absolute left-24 top-0 h-40 md:left-[30%]" />
                <img src="/assets/images/auth/coming-soon-object3.png" alt="image" class="absolute right-0 top-0 h-[300px]" />
                <img src="/assets/images/auth/polygon-object.svg" alt="image" class="absolute bottom-0 end-[28%]" />
                <div
                    class="relative w-full max-w-[870px] rounded-md bg-[linear-gradient(45deg,#fff9f9_0%,rgba(255,255,255,0)_25%,rgba(255,255,255,0)_75%,_#fff9f9_100%)] p-2 dark:bg-[linear-gradient(52.22deg,#0E1726_0%,rgba(14,23,38,0)_18.66%,rgba(14,23,38,0)_51.04%,rgba(14,23,38,0)_80.07%,#0E1726_100%)]"
                >
                    <div class="relative flex flex-col justify-center rounded-md bg-white/60 px-6 py-20 backdrop-blur-lg dark:bg-black/50 lg:min-h-[758px]">
                    
                        <div class="mx-auto w-full max-w-[440px]">
                            <div class="flex justify-center items-center mb-8">
                                <img src="/assets/images/logos/logo-light.png" alt="Sugarin" class="w-20 h-20 dark:hidden" />
                                <img src="/assets/images/logos/logo-dark.png" alt="Sugarin" class="hidden w-20 h-20 dark:block" />
                                <h1 class="text-3xl font-extrabold !leading-snug md:text-4xl">SugarIn</h1>
                            </div>
                            <div class="mb-2">
                                <p class="text-base leading-normal text-white-dark">Ingresa a tu cuenta para continuar</p>
                            </div>
                            
                            <LoginForm
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>