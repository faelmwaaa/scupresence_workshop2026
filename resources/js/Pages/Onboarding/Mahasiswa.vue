<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    user: Object,
    organizations: Array,
});

const avatarPreview = ref(null);
const showFakultasDropdown = ref(false);
const activeUnitDropdown = ref(null);
const activeJabatanDropdown = ref(null);
const activeUnitCategory = ref({}); // tracks 'ormawa' or 'ukm' tab per unit index

const handleClickOutside = (e) => {
    if (!e.target.closest('.custom-dropdown-container')) {
        showFakultasDropdown.value = false;
        activeUnitDropdown.value = null;
        activeJabatanDropdown.value = null;
    }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

const form = useForm({
    name: props.user?.name || '',
    nim: '',
    phone: '',
    fakultas: '',
    profile_picture: null,
    units: [],
});

const fakultasList = [
    'Fakultas Ekonomi dan Bisnis',
    'Fakultas Hukum dan Komunikasi',
    'Fakultas Ilmu Komputer',
    'Fakultas Psikologi',
    'Fakultas Teknik',
    'Fakultas Teknologi Pertanian',
    'Fakultas Arsitektur dan Desain',
    'Sekolah Pascasarjana',
];

const addUnit = () => form.units.push({ organization_id: '', jabatan: '', is_pengurus: false });
const removeUnit = (index) => form.units.splice(index, 1);

const getOrgType = (orgId) => {
    const org = props.organizations.find(o => o.id == orgId);
    return org ? org.type : '';
};

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
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(form.name || 'A') + '&background=5B2163&color=fff&size=128';
});

const submit = () => {
    form.post(route('onboarding.mahasiswa.save'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Lengkapi Profil - Mahasiswa" />
    <div class="min-h-screen bg-gradient-to-br from-purple-50 to-white">
        <div class="max-w-lg mx-auto px-4 py-8">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 bg-white rounded-2xl px-4 py-2 shadow-sm mb-4 border border-purple-100">
                    <span class="bg-[#5B2163] text-white w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold">S</span>
                    <span class="font-bold text-[#5B2163]">SCUPresence</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Lengkapi Profil Mahasiswa</h1>
                <p class="text-sm text-gray-500 mt-1">Isi data diri kamu untuk bergabung ke unit</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">

                <!-- Profile Picture -->
                <div class="flex flex-col items-center gap-2">
                    <div class="relative">
                        <img :src="avatarSrc" class="w-24 h-24 rounded-full object-cover border-4 border-purple-100 shadow-md" />
                        <label class="absolute bottom-0 right-0 bg-[#5B2163] text-white rounded-full p-1.5 cursor-pointer shadow-lg hover:bg-[#4a1b50] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                        </label>
                    </div>
                    <p class="text-xs text-gray-400">Tap untuk ganti foto profil</p>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="input-field" placeholder="Nama lengkap sesuai KTM" />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- NIM -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIM <span class="text-red-500">*</span></label>
                    <input v-model="form.nim" type="text" class="input-field" placeholder="Nomor Induk Mahasiswa" />
                    <p v-if="form.errors.nim" class="text-red-500 text-xs mt-1">{{ form.errors.nim }}</p>
                </div>

                <!-- No. HP -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Aktif (WhatsApp) <span class="text-red-500">*</span></label>
                    <input v-model="form.phone" type="tel" class="input-field" placeholder="08xxxxxxxxxx" />
                    <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                </div>

                <!-- Fakultas -->
                <div class="custom-dropdown-container relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fakultas <span class="text-red-500">*</span></label>
                    <button type="button" @click="showFakultasDropdown = !showFakultasDropdown"
                        class="org-trigger"
                        :class="{ 'is-open': showFakultasDropdown, 'is-selected': form.fakultas }">
                        <span class="flex-1 text-left truncate" :class="form.fakultas ? 'text-gray-900 font-medium' : 'text-gray-400'">
                            {{ form.fakultas || 'Pilih Fakultas' }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': showFakultasDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <Transition name="org-list-anim">
                        <div v-if="showFakultasDropdown" class="org-list absolute z-50 w-full mt-2">
                            <button type="button"
                                v-for="f in fakultasList" :key="f"
                                @click="form.fakultas = f; showFakultasDropdown = false"
                                class="org-option"
                                :class="{ 'is-active': form.fakultas === f }">
                                <span class="truncate">{{ f }}</span>
                                <Transition name="check-anim">
                                    <svg v-if="form.fakultas === f"
                                        class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </Transition>
                            </button>
                        </div>
                    </Transition>
                    <p v-if="form.errors.fakultas" class="text-red-500 text-xs mt-1">{{ form.errors.fakultas }}</p>
                </div>

                <!-- Units -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-700">Unit yang Diikuti <span class="text-red-500">*</span></label>
                        <button type="button" @click="addUnit"
                            class="flex items-center gap-1 text-xs font-semibold text-[#5B2163] hover:text-[#4a1b50] transition-colors bg-purple-50 px-3 py-1.5 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Tambah Unit
                        </button>
                    </div>

                    <div v-if="form.units.length === 0" class="text-center py-5 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                        <p class="text-sm text-gray-400">Klik "Tambah Unit" untuk mendaftar ke ORMAWA atau UKM</p>
                    </div>

                    <transition-group name="list" tag="div">
                        <div v-for="(unit, index) in form.units" :key="index"
                            class="bg-purple-50/50 border border-purple-100 rounded-xl p-4 mb-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-[#5B2163]">Unit {{ index + 1 }}</span>
                                <button type="button" @click="removeUnit(index)"
                                    class="text-red-400 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="custom-dropdown-container relative">
                                <button type="button" @click="activeUnitDropdown = activeUnitDropdown === index ? null : index; if(!activeUnitCategory[index]) activeUnitCategory[index] = 'ormawa'"
                                    class="org-trigger"
                                    :class="{ 'is-open': activeUnitDropdown === index, 'is-selected': unit.organization_id }">
                                    <span class="flex-1 text-left truncate" :class="unit.organization_id ? 'text-gray-900 font-medium' : 'text-gray-400'">
                                        {{ unit.organization_id ? organizations.find(o => o.id === unit.organization_id)?.name : 'Pilih ORMAWA / UKM' }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': activeUnitDropdown === index }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <Transition name="org-list-anim">
                                    <div v-if="activeUnitDropdown === index" class="org-list absolute z-50 w-full mt-2">
                                        <!-- Category Tabs -->
                                        <div class="flex border-b border-purple-100 bg-purple-50/60">
                                            <button type="button"
                                                @click.stop="activeUnitCategory[index] = 'ormawa'"
                                                class="flex-1 py-2 text-xs font-bold tracking-wide uppercase transition-colors"
                                                :class="activeUnitCategory[index] === 'ormawa' ? 'text-[#5B2163] border-b-2 border-[#5B2163] bg-white' : 'text-gray-400 hover:text-[#5B2163]'">
                                                ORMAWA
                                            </button>
                                            <button type="button"
                                                @click.stop="activeUnitCategory[index] = 'ukm'"
                                                class="flex-1 py-2 text-xs font-bold tracking-wide uppercase transition-colors"
                                                :class="activeUnitCategory[index] === 'ukm' ? 'text-[#5B2163] border-b-2 border-[#5B2163] bg-white' : 'text-gray-400 hover:text-[#5B2163]'">
                                                UKM
                                            </button>
                                        </div>
                                        <!-- Filtered unit list based on tab -->
                                        <button type="button"
                                            v-for="org in organizations.filter(o => o.type === (activeUnitCategory[index] || 'ormawa'))" :key="org.id"
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

                            <!-- Tipe Keanggotaan -->
                            <div v-if="unit.organization_id" class="flex flex-col gap-2 mt-2">
                                <label class="text-xs font-semibold text-gray-600">Bergabung sebagai:</label>
                                <div class="flex gap-2">
                                    <button type="button" @click="unit.is_pengurus = false; unit.jabatan = 'Anggota'"
                                        class="flex-1 py-2 px-3 rounded-lg border text-sm font-medium transition-colors"
                                        :class="!unit.is_pengurus ? 'bg-[#5B2163] text-white border-[#5B2163]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                        Anggota
                                    </button>
                                    <button type="button" @click="unit.is_pengurus = true"
                                        class="flex-1 py-2 px-3 rounded-lg border text-sm font-medium transition-colors"
                                        :class="unit.is_pengurus ? 'bg-[#5B2163] text-white border-[#5B2163]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                                        Pengurus
                                    </button>
                                </div>
                            </div>

                            <!-- Jabatan (khusus Pengurus / ORMAWA) -->
                            <div v-if="unit.organization_id && (unit.is_pengurus || getOrgType(unit.organization_id) === 'ormawa')" class="custom-dropdown-container relative">
                                <button type="button" @click="activeJabatanDropdown = activeJabatanDropdown === index ? null : index"
                                    class="org-trigger"
                                    :class="{ 'is-open': activeJabatanDropdown === index, 'is-selected': unit.jabatan }">
                                    <span class="flex-1 text-left truncate" :class="unit.jabatan ? 'text-gray-900 font-medium' : 'text-gray-400'">
                                        {{ unit.jabatan || (unit.is_pengurus ? 'Pilih Jabatan Pengurus' : 'Pilih Jabatan (opsional)') }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': activeJabatanDropdown === index }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <Transition name="org-list-anim">
                                    <div v-if="activeJabatanDropdown === index" class="org-list absolute z-50 w-full mt-2">
                                        <!-- Different options based on is_pengurus -->
                                        <template v-if="unit.is_pengurus">
                                            <button type="button"
                                                v-for="j in ['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Koordinator Divisi', 'Anggota Divisi']" :key="j"
                                                @click="unit.jabatan = j; activeJabatanDropdown = null"
                                                class="org-option"
                                                :class="{ 'is-active': unit.jabatan === j }">
                                                <span class="truncate">{{ j }}</span>
                                                <Transition name="check-anim">
                                                    <svg v-if="unit.jabatan === j"
                                                        class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </Transition>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button type="button"
                                                v-for="j in ['Anggota']" :key="j"
                                                @click="unit.jabatan = j; activeJabatanDropdown = null"
                                                class="org-option"
                                                :class="{ 'is-active': unit.jabatan === j }">
                                                <span class="truncate">{{ j }}</span>
                                                <Transition name="check-anim">
                                                    <svg v-if="unit.jabatan === j"
                                                        class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </Transition>
                                            </button>
                                        </template>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </transition-group>
                    <p v-if="form.errors.units" class="text-red-500 text-xs mt-1">{{ form.errors.units }}</p>
                </div>

                <!-- Submit -->
                <button type="button" @click="submit" :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-[#5B2163] hover:bg-[#4a1b50] text-white rounded-xl font-semibold transition-all duration-200 shadow-md hover:shadow-lg active:scale-95 disabled:opacity-60">
                    <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ form.processing ? 'Menyimpan...' : 'Simpan & Daftar' }}
                </button>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">&copy; 2026 Universitas Katolik Soegijapranata</p>
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
/* List open/close transition */
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
.org-group-label {
    padding: 0.5rem 1rem 0.25rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #5B2163;
    background: rgba(91,33,99,0.04);
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
/* Press-down scale */
.org-option:active {
    transform: scale(0.97);
    background: rgba(91,33,99,0.14);
}
/* Selected row: flash in on first render */
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
/* Stroke draw animation on the checkmark path */
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
    border-color: #5B2163;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(91,33,99,0.13);
}

.list-enter-active, .list-leave-active { transition: all 0.35s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
