<script lang="ts" setup>
import { ref } from 'vue';
import { createWorker, PSM } from 'tesseract.js';
import { ImagePlus, Loader2 } from 'lucide-vue-next';
import type { User } from '@/types';

const props = defineProps<{
    patient: User;
}>();

const emit = defineEmits<{
    (e: 'extracted', value: number | null): void;
}>();

const imagePreview = ref<string | null>(null);
const isProcessing = ref(false);
const progressMessage = ref('');
const errorMessage = ref('');

/**
 * Preprocesa la imagen para mejorar la lectura OCR de displays LCD:
 * - Escala 2x para dar más resolución a Tesseract
 * - Convierte a escala de grises
 * - Binariza con umbral adaptativo (inverso para LCD claro-sobre-oscuro)
 */
async function preprocessForOcr(file: File): Promise<Blob> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            const scale = 2;
            const canvas = document.createElement('canvas');
            canvas.width = img.width * scale;
            canvas.height = img.height * scale;
            const ctx = canvas.getContext('2d')!;

            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;

            // Calcular el valor gris promedio para usar como umbral adaptativo
            let sum = 0;
            for (let i = 0; i < data.length; i += 4) {
                sum += 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            }
            const avg = sum / (data.length / 4);
            const threshold = avg * 0.85; // los dígitos LCD suelen ser ~15% más oscuros que el fondo

            for (let i = 0; i < data.length; i += 4) {
                const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                // Binarizar: píxeles oscuros (dígitos) → negro, fondo claro → blanco
                const binary = gray < threshold ? 0 : 255;
                data[i] = data[i + 1] = data[i + 2] = binary;
                data[i + 3] = 255;
            }

            ctx.putImageData(imageData, 0, 0);
            canvas.toBlob((blob) => {
                URL.revokeObjectURL(url);
                resolve(blob!);
            }, 'image/png');
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('No se pudo cargar la imagen'));
        };

        img.src = url;
    });
}

/**
 * Extrae el valor de glucosa más probable de los candidatos numéricos:
 * - Filtra por rango glucémico válido (20–600 mg/dL)
 * - Si hay múltiples candidatos, prefiere el de 2–3 dígitos (más típico del glucómetro)
 * - Descarta valores de fecha/hora (típicamente fuera del rango 20-600)
 */
function selectGlucoseValue(text: string): number | null {
    const candidates = (text.match(/\d+/g) ?? [])
        .map((n: string) => parseInt(n, 10))
        .filter((n: number) => n >= 20 && n <= 600);

    if (candidates.length === 0) return null;
    if (candidates.length === 1) return candidates[0];

    // Preferir valores de 2-3 dígitos (rango típico de glucemia)
    const twoOrThreeDigit = candidates.filter(n => n >= 20 && n <= 600);
    return twoOrThreeDigit[0] ?? candidates[0];
}

async function onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    errorMessage.value = '';
    imagePreview.value = URL.createObjectURL(file);
    isProcessing.value = true;
    progressMessage.value = 'Procesando imagen…';

    try {
        const processedBlob = await preprocessForOcr(file);

        progressMessage.value = 'Inicializando lector OCR…';

        const worker = await createWorker('eng', 1, {
            logger: (m: { status: string; progress: number }) => {
                if (m.status === 'loading tesseract core') {
                    progressMessage.value = 'Cargando motor OCR…';
                } else if (m.status === 'initializing api') {
                    progressMessage.value = 'Inicializando…';
                } else if (m.status === 'recognizing text') {
                    progressMessage.value = `Analizando imagen… ${Math.round((m.progress || 0) * 100)}%`;
                }
            },
        });

        // Solo reconocer dígitos, modo sparse para múltiples números dispersos en pantalla
        await worker.setParameters({
            tessedit_char_whitelist: '0123456789',
            tessedit_pageseg_mode: PSM.SPARSE_TEXT,
            user_defined_dpi: '300',
        });

        const { data: { text } } = await worker.recognize(processedBlob);
        await worker.terminate();

        const extracted = selectGlucoseValue(text);
        emit('extracted', extracted);
    } catch {
        errorMessage.value = 'Error al procesar la imagen. Intenta nuevamente.';
        emit('extracted', null);
    } finally {
        isProcessing.value = false;
        progressMessage.value = '';
    }
}
</script>

<template>
    <div class="max-w-lg mx-auto">
        <h2 class="text-xl font-semibold mb-4 dark:text-white-light">Capturar lectura</h2>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center rounded-full bg-primary/80 p-1 font-semibold text-white ltr:pr-3 rtl:pl-3">
                <img
                    class="block h-8 w-8 rounded-full border-2 border-white/50 object-cover ltr:mr-1 rtl:ml-1"
                    src="/assets/images/profile-34.jpeg"
                    alt=""
                />
                Paciente: <span class="pl-1 font-light">{{ patient.name }}</span>
            </div>
        </div>

        <label
            class="flex flex-col items-center justify-center w-full h-48 rounded-xl border-2 border-dashed border-gray-300 dark:border-[#17263c] cursor-pointer hover:border-primary transition-colors bg-gray-50 dark:bg-[#1b2e4b] relative overflow-hidden"
        >
            <img
                v-if="imagePreview"
                :src="imagePreview"
                class="absolute inset-0 w-full h-full object-contain"
                alt="Previsualización"
            />
            <div v-else class="flex flex-col items-center text-gray-400">
                <ImagePlus :size="40" class="mb-2" />
                <span class="text-sm">Toca para seleccionar o capturar una foto</span>
            </div>
            <input
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileSelected"
            />
        </label>

        <div v-if="isProcessing" class="mt-4 flex items-center gap-3 text-primary">
            <Loader2 :size="20" class="animate-spin" />
            <span class="text-sm">{{ progressMessage }}</span>
        </div>

        <p v-if="errorMessage" class="mt-3 text-sm text-danger">{{ errorMessage }}</p>

        <p v-if="imagePreview && !isProcessing && !errorMessage" class="mt-3 text-sm text-success">
            Imagen procesada. Revisa el valor en el siguiente paso.
        </p>
    </div>
</template>
