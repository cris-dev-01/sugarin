<script setup lang="ts">
import { watch } from "vue";
import Swal from 'sweetalert2';
import type { Notification, SweetAlertIcon } from "@/types";

const props = defineProps<{
    isOpen: boolean;
	notification: Notification
}>();

const emit = defineEmits<{
    close: []
}>();

watch(
    () => props.isOpen,
    () => {
        if(props.isOpen) {
			const toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				showCloseButton: true,
				customClass: {
					popup: `color-${props.notification.typeNotification}`,
					container: 'swal2-toast-container'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer);
					toast.addEventListener('mouseleave', Swal.resumeTimer);
				},
				didClose: () => {
					emit('close');
				}
			});
			toast.fire({
				title: props.notification.messageNotification,
				icon: props.notification.typeNotification as SweetAlertIcon
			});
		}
    }
);
</script>
