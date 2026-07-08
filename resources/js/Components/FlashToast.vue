<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success'); // 'success' | 'error'

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success) {
            message.value = success;
            type.value = 'success';
            visible.value = true;
        } else if (error) {
            message.value = error;
            type.value = 'error';
            visible.value = true;
        }
        if (visible.value) {
            setTimeout(() => { visible.value = false; }, 3500);
        }
    },
    { immediate: true }
);
</script>

<template>
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="visible"
            :class="[
                'fixed bottom-24 left-1/2 -translate-x-1/2 z-[100] max-w-sm w-[calc(100%-2rem)]',
                'flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl text-sm font-medium',
                type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
            ]"
        >
            <!-- Icon -->
            <span class="text-lg flex-shrink-0">{{ type === 'success' ? '✅' : '❌' }}</span>
            <span class="flex-1">{{ message }}</span>
            <button @click="visible = false" class="opacity-70 hover:opacity-100 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </transition>
</template>
