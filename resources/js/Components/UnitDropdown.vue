<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    organizations: {
        type: Array,
        required: true
    },
    modelValue: {
        type: Number,
        default: null
    },
    showRole: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const selectedOrg = computed(() => {
    return props.organizations.find(o => o.id === props.modelValue);
});

const selectOrg = (org) => {
    emit('update:modelValue', org.id);
    emit('change', org.id);
    isOpen.value = false;
};

const getRoleText = (org) => {
    if (!props.showRole || !org || !org.pivot) return '';
    return org.pivot.is_pengurus ? ' (Pengurus)' : ' (Anggota)';
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>

<template>
    <div class="relative w-full" ref="dropdownRef">
        <!-- Trigger button -->
        <button type="button" @click="isOpen = !isOpen"
            class="org-trigger"
            :class="{ 'is-open': isOpen, 'is-selected': selectedOrg }">
            <span class="org-trigger-icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </span>
            <span class="flex-1 text-left truncate" :class="selectedOrg ? 'text-gray-900 font-medium' : 'text-gray-400'">
                {{ selectedOrg ? (selectedOrg.name + getRoleText(selectedOrg)) : 'Pilih Unit' }}
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Inline dropdown list -->
        <Transition name="org-list-anim">
            <div v-if="isOpen" class="org-list absolute z-50 w-full mt-2">
                <button type="button"
                    v-for="org in organizations" :key="org.id"
                    @click="selectOrg(org)"
                    class="org-option"
                    :class="{ 'is-active': modelValue === org.id }">
                    <span class="truncate">{{ org.name }}{{ getRoleText(org) }}</span>
                    <Transition name="check-anim">
                        <svg v-if="modelValue === org.id"
                            class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </Transition>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* ── Custom org trigger button ── */
.org-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1rem 0.75rem 0.875rem;
    border-radius: 0.875rem;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    font-size: 0.875rem;
    color: #111827;
    cursor: pointer;
    transition:
        border-color 0.25s ease,
        background-color 0.25s ease,
        box-shadow 0.25s ease;
    text-align: left;
}
.org-trigger:focus,
.org-trigger.is-open {
    border-color: #5B2163;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(91,33,99,0.13);
    outline: none;
}
.org-trigger-icon {
    color: #9ca3af;
    flex-shrink: 0;
    display: flex;
    transition: color 0.25s ease;
}
.org-trigger.is-open .org-trigger-icon,
.org-trigger.is-selected .org-trigger-icon {
    color: #5B2163;
}

/* ── Inline options list ── */
.org-list-anim-enter-active {
    transition: opacity 0.2s ease, transform 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.org-list-anim-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.org-list-anim-enter-from {
    opacity: 0;
    transform: translateY(-12px);
}
.org-list-anim-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.org-list {
    border: 1.5px solid rgba(91,33,99,0.18);
    border-radius: 0.875rem;
    background: #fff;
    overflow: hidden;
    max-height: 260px;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(91,33,99,0.12);
    transform-origin: top center;
}
.org-option {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
    border-bottom: 1px solid #f3f4f6;
}
.org-option:last-child { border-bottom: none; }
.org-option:hover {
    background: rgba(91,33,99,0.06);
    color: #5B2163;
}
.org-option:active {
    transform: scale(0.97);
    background: rgba(91,33,99,0.14);
}
.org-option.is-active {
    color: #5B2163;
    font-weight: 600;
    animation: selectFlash 0.35s ease forwards;
}
@keyframes selectFlash {
    0%   { background: rgba(91,33,99,0.22); }
    100% { background: rgba(91,33,99,0.08); }
}

/* Checkmark icon enter/leave */
.check-icon {
    width: 1rem;
    height: 1rem;
    color: #5B2163;
    flex-shrink: 0;
}
.check-anim-enter-active { transition: opacity 0.2s ease, transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.check-anim-leave-active { transition: opacity 0.12s ease, transform 0.12s ease; }
.check-anim-enter-from   { opacity: 0; transform: scale(0) rotate(-45deg); }
.check-anim-leave-to     { opacity: 0; transform: scale(0) rotate(45deg); }
.check-path {
    stroke-dasharray: 30;
    stroke-dashoffset: 30;
    animation: checkDraw 0.3s ease 0.05s forwards;
}
@keyframes checkDraw {
    to { stroke-dashoffset: 0; }
}
</style>
