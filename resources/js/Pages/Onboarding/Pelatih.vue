<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
    organizations: Array, // UKM only
});

const avatarPreview = ref(null);

const activeUnitDropdown = ref(null);

const handleClickOutside = (e) => {
    if (!e.target.closest('.custom-dropdown-container')) {
        activeUnitDropdown.value = null;
    }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

const form = useForm({
    name: props.user?.name || '',
    phone: '',
    profile_picture: null,
    units: [],
});

const addUnit = () => form.units.push({ organization_id: '' });
const removeUnit = (i) => form.units.splice(i, 1);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.profile_picture = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const avatarSrc = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (props.user?.profile_picture) return props.user.profile_picture;
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(form.name || 'P') + '&background=2563eb&color=fff&size=128';
});

const submit = () => form.post(route('onboarding.pelatih.save'), { forceFormData: true });
</script>

<template>
    <Head title="Lengkapi Profil - Pelatih UKM" />
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <div class="max-w-lg mx-auto px-4 py-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 bg-white rounded-2xl px-4 py-2 shadow-sm mb-4">
                    <span class="bg-blue-700 text-white w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold">S</span>
                    <span class="font-bold text-blue-900">SCUPresence</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Lengkapi Profil Pelatih</h1>
                <p class="text-sm text-gray-500 mt-1">Pendaftaran kamu akan diverifikasi oleh Admin</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <!-- Profile Picture -->
                <div class="flex flex-col items-center gap-3">
                    <div class="relative">
                        <img :src="avatarSrc"
                            class="w-24 h-24 rounded-full object-cover border-4 border-blue-100 shadow-md"
                        />
                        <label class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1.5 cursor-pointer shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input v-model="form.name" type="text" class="input-field" placeholder="Nama lengkap" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No. HP Aktif (WhatsApp)</label>
                    <input v-model="form.phone" type="tel" class="input-field" placeholder="08xxxxxxxxxx" />
                </div>

                <!-- UKM Units -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-medium text-gray-700">UKM yang Dilatih</label>
                        <button type="button" @click="addUnit" class="flex items-center gap-1 text-xs font-medium text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah UKM
                        </button>
                    </div>
                    <div v-if="form.units.length === 0" class="text-center py-4 bg-gray-50 rounded-xl text-sm text-gray-400">
                        Tambahkan UKM yang kamu latih
                    </div>
                    <div v-for="(unit, index) in form.units" :key="index" class="bg-gray-50 rounded-xl p-4 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">UKM {{ index + 1 }}</span>
                            <button type="button" @click="removeUnit(index)" class="text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="custom-dropdown-container relative">
                            <button type="button" @click="activeUnitDropdown = activeUnitDropdown === index ? null : index"
                                class="org-trigger"
                                :class="{ 'is-open': activeUnitDropdown === index, 'is-selected': unit.organization_id }">
                                <span class="flex-1 text-left truncate" :class="unit.organization_id ? 'text-gray-900 font-medium' : 'text-gray-400'">
                                    {{ unit.organization_id ? organizations.find(o => o.id === unit.organization_id)?.name : 'Pilih UKM' }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': activeUnitDropdown === index }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <Transition name="org-list-anim">
                                <div v-if="activeUnitDropdown === index" class="org-list absolute z-50 w-full mt-2">
                                    <button type="button"
                                        v-for="org in organizations" :key="org.id"
                                        @click="unit.organization_id = org.id; activeUnitDropdown = null"
                                        class="org-option"
                                        :class="{ 'is-active': unit.organization_id === org.id }">
                                        <span class="truncate">{{ org.name }}</span>
                                        <Transition name="check-anim">
                                            <svg v-if="unit.organization_id === org.id"
                                                class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </Transition>
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-700">
                    ⏳ Akun kamu akan diverifikasi oleh Admin terlebih dahulu.
                </div>

                <button type="button" @click="submit" :disabled="form.processing" class="w-full flex items-center justify-center gap-3 px-4 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-all duration-200 shadow-md active:scale-95">
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Kirim Pendaftaran</span>
                </button>
            </div>
        </div>
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
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.13);
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
    color: #2563eb;
}

/* ── Inline options list ── */
.org-list-anim-enter-active { transition: opacity 0.2s ease, transform 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.org-list-anim-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.org-list-anim-enter-from { opacity: 0; transform: translateY(-12px); }
.org-list-anim-leave-to { opacity: 0; transform: translateY(-6px); }

.org-list {
    border: 1.5px solid rgba(37, 99, 235, 0.18);
    border-radius: 0.875rem;
    background: #fff;
    overflow: hidden;
    max-height: 260px;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.12);
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
    background: rgba(37, 99, 235, 0.06);
    color: #2563eb;
}
.org-option:active {
    transform: scale(0.97);
    background: rgba(37, 99, 235, 0.14);
}
.org-option.is-active {
    color: #2563eb;
    font-weight: 600;
    animation: selectFlash 0.35s ease forwards;
}
@keyframes selectFlash {
    0%   { background: rgba(37, 99, 235, 0.22); }
    100% { background: rgba(37, 99, 235, 0.08); }
}

.check-icon {
    width: 1rem;
    height: 1rem;
    color: #2563eb;
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

.input-field {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.875rem;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    font-size: 0.875rem;
    color: #111827;
    outline: none;
    transition: all 0.25s ease;
}
.input-field:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.13);
}
</style>
