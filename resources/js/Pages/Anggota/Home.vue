<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';
import UnitDropdown from '@/Components/UnitDropdown.vue';

const props = defineProps({
    user: Object,
    organizations: Array,       // Active orgs
    allOrganizations: Array,    // All orgs including pending
});

const selectedOrgId = ref(props.organizations[0]?.id || null);
const schedules = ref([]);
const showJoinModal = ref(false);
const showOrgDropdown = ref(false);
const allOrgs = ref([]);
const joinForm = useForm({ organization_id: '', jabatan: '' });

const selectedOrgName = computed(() => {
    if (!joinForm.organization_id) return null;
    return allOrgs.value.find(o => o.id === joinForm.organization_id)?.name || null;
});

const selectedOrg = computed(() => props.organizations.find(o => o.id === selectedOrgId.value));
const pendingOrgs = computed(() => props.allOrganizations?.filter(o => o.pivot?.membership_status === 'pending') || []);
const isPending = computed(() => props.organizations.length === 0);

// Fetch schedules using the anggota-accessible endpoint
const fetchSchedules = async (orgId) => {
    if (!orgId) return;
    selectedOrgId.value = orgId;
    schedules.value = [];
    try {
        const res = await fetch(`/anggota/org-schedules/${orgId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        schedules.value = data.upcoming || [];
    } catch (e) {
        schedules.value = [];
    }
};

// Fetch all organizations for the join modal
const openJoinModal = async () => {
    showJoinModal.value = true;
    if (allOrgs.value.length === 0) {
        try {
            const res = await fetch('/api/organizations', {
                headers: { 'Accept': 'application/json' }
            });
            allOrgs.value = await res.json();
        } catch {
            allOrgs.value = [];
        }
    }
};

const submitJoin = () => {
    joinForm.post(route('anggota.join-unit'), {
        onSuccess: () => { showJoinModal.value = false; joinForm.reset(); }
    });
};

const formatDate = (date) => {
    try {
        return new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    } catch { return date; }
};
const formatTime = (time) => time?.substring(0, 5);

// Auto-load schedules on mount
onMounted(() => {
    if (selectedOrgId.value) fetchSchedules(selectedOrgId.value);
});
</script>

<template>
    <Head title="Home - Anggota" />
    <div class="page-container bg-gray-50 pb-24">

        <!-- Header -->
        <div class="bg-white px-4 pt-12 pb-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Selamat datang 👋</p>
                    <h1 class="text-xl font-bold text-gray-900">{{ user.name }}</h1>
                </div>
                <img
                    :src="user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=5B2163&color=fff&size=64'"
                    class="w-11 h-11 rounded-full object-cover border-2 border-purple-100 shadow-sm"
                />
            </div>

            <!-- Unit selector -->
            <div v-if="organizations.length > 0" class="flex items-center gap-2 mt-2">
                <UnitDropdown :organizations="organizations" v-model="selectedOrgId" @change="fetchSchedules($event)" />
                <button @click="openJoinModal" class="w-[46px] h-[46px] flex-shrink-0 flex items-center justify-center bg-white border-2 border-dashed border-brand-purple text-brand-purple rounded-xl hover:bg-brand-purple hover:text-white transition-colors" title="Tambah Unit Lain">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        <!-- PENDING STATE: No active units -->
        <div v-if="isPending" class="px-4 mt-6">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
                <div class="text-5xl mb-3">⏳</div>
                <h3 class="font-bold text-amber-800 mb-1 text-lg">Menunggu Persetujuan</h3>
                <p class="text-sm text-amber-600 leading-relaxed">
                    Pengurus sedang memproses pendaftaranmu. Kamu bisa bergabung ke unit lain sambil menunggu.
                </p>
                <button @click="openJoinModal" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-medium hover:bg-amber-700 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Unit Lain
                </button>
            </div>

            <!-- Show pending units -->
            <div v-if="pendingOrgs.length > 0" class="mt-4 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Menunggu di:</p>
                <div v-for="org in pendingOrgs" :key="org.id" class="card flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ org.name }}</p>
                        <span class="badge-pending">Pending</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTIVE STATE: Show schedules -->
        <div v-else class="px-4 mt-6">
            <div v-if="!selectedOrgId" class="text-center py-12 text-gray-400">
                <p class="text-sm">Pilih unit untuk melihat jadwal</p>
            </div>

            <div v-else>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-gray-900">📅 Jadwal Mendatang</h2>
                    <span v-if="selectedOrg" class="text-xs text-gray-400 font-medium">{{ selectedOrg.name }}</span>
                </div>

                <!-- No schedules -->
                <div v-if="schedules.length === 0" class="text-center py-14">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Belum ada jadwal mendatang</p>
                    <p class="text-xs text-gray-300 mt-1">Pengurus belum menambahkan event</p>
                </div>

                <!-- Schedule cards -->
                <Link
                    v-for="schedule in schedules" :key="schedule.id"
                    :href="route('anggota.schedule.show', schedule.id)"
                    class="card mb-3 flex items-start gap-3 hover:shadow-md transition-all duration-200 active:scale-98 group"
                >
                    <!-- Date badge -->
                    <div class="flex-shrink-0 bg-brand-purple rounded-xl px-2.5 py-2 text-center min-w-[48px]">
                        <p class="text-white text-xs font-medium leading-none">
                            {{ new Date(schedule.event_date).toLocaleDateString('id-ID', { month: 'short' }) }}
                        </p>
                        <p class="text-white text-xl font-bold leading-tight">
                            {{ new Date(schedule.event_date).getDate() }}
                        </p>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate group-hover:text-brand-purple transition-colors">{{ schedule.title }}</h3>
                        <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                            <span class="flex items-center gap-1 text-xs text-gray-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ formatTime(schedule.start_time) }} – {{ formatTime(schedule.end_time) }}
                            </span>
                            <span class="flex items-center gap-1 text-xs text-gray-500 truncate">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ schedule.location }}
                            </span>
                        </div>
                    </div>

                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1 group-hover:text-brand-purple transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>
        </div>

        <!-- Join Unit Bottom Sheet Modal -->
        <Transition name="sheet">
        <div v-if="showJoinModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end justify-center" @click.self="showJoinModal = false">
            <div class="modal-panel bg-white w-full max-w-lg rounded-t-3xl max-h-[90vh] overflow-y-auto shadow-2xl">

                <!-- Drag handle -->
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>

                <div class="px-6 pt-3 pb-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-5">Bergabung ke Unit</h3>

                    <div class="space-y-4 mb-6">
                        <!-- Custom Org Picker -->
                        <div class="field-group">
                            <label class="field-label">Pilih ORMAWA / UKM</label>

                            <!-- Trigger button -->
                            <button type="button" @click="showOrgDropdown = !showOrgDropdown"
                                class="org-trigger"
                                :class="{ 'is-open': showOrgDropdown, 'is-selected': selectedOrgName }">
                                <span class="org-trigger-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </span>
                                <span class="flex-1 text-left" :class="selectedOrgName ? 'text-gray-900 font-medium' : 'text-gray-400'">
                                    {{ selectedOrgName || 'Pilih unit yang ingin diikuti' }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': showOrgDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Inline dropdown list -->
                            <Transition name="org-list-anim">
                            <div v-if="showOrgDropdown" class="org-list">
                                <!-- ORMAWA group -->
                                <p class="org-group-label">ORMAWA</p>
                                <button type="button"
                                    v-for="org in allOrgs.filter(o => o.type === 'ormawa')" :key="org.id"
                                    @click="joinForm.organization_id = org.id; showOrgDropdown = false"
                                    class="org-option"
                                    :class="{ 'is-active': joinForm.organization_id === org.id }">
                                    <span>{{ org.name }}</span>
                                    <Transition name="check-anim">
                                        <svg v-if="joinForm.organization_id === org.id"
                                            class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </Transition>
                                </button>

                                <!-- UKM group -->
                                <p class="org-group-label mt-2">UKM</p>
                                <button type="button"
                                    v-for="org in allOrgs.filter(o => o.type === 'ukm')" :key="org.id"
                                    @click="joinForm.organization_id = org.id; showOrgDropdown = false"
                                    class="org-option"
                                    :class="{ 'is-active': joinForm.organization_id === org.id }">
                                    <span>{{ org.name }}</span>
                                    <Transition name="check-anim">
                                        <svg v-if="joinForm.organization_id === org.id"
                                            class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path class="check-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </Transition>
                                </button>
                            </div>
                            </Transition>

                            <p v-if="joinForm.errors.organization_id" class="text-red-500 text-xs">{{ joinForm.errors.organization_id }}</p>
                        </div>

                        <!-- Jabatan -->
                        <div class="field-group">
                            <label class="field-label">Jabatan <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                            <select v-model="joinForm.jabatan" class="anggota-input appearance-none">
                                <option value="" disabled selected>Pilih Jabatan</option>
                                <option value="Anggota">Anggota</option>
                                <option value="Sekretaris">Sekretaris</option>
                                <option value="Bendahara">Bendahara</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button @click="showJoinModal = false; showOrgDropdown = false"
                            class="flex-1 py-3.5 rounded-2xl font-semibold text-sm text-gray-600 transition-all active:scale-95"
                            style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            Batal
                        </button>
                        <button
                            @click="submitJoin"
                            :disabled="joinForm.processing || !joinForm.organization_id"
                            class="flex-1 py-3.5 rounded-2xl font-semibold text-sm text-white transition-all active:scale-95 disabled:opacity-50"
                            style="background: linear-gradient(135deg, #5B2163, #8b3a9e); box-shadow: 0 4px 14px rgba(91,33,99,0.35);">
                            {{ joinForm.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Transition>

        <FlashToast />
        <BottomNav role="anggota" />
    </div>
</template>

<style scoped>
/* ── Bottom sheet enter/leave ── */
/* Backdrop fades */
.sheet-enter-active {
    transition: opacity 0.3s ease;
}
.sheet-leave-active {
    transition: opacity 0.25s ease;
}
.sheet-enter-from,
.sheet-leave-to {
    opacity: 0;
}
/* Panel slides up from below */
.sheet-enter-active .modal-panel {
    transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.sheet-leave-active .modal-panel {
    transition: transform 0.25s cubic-bezier(0.4, 0, 1, 1);
}
.sheet-enter-from .modal-panel {
    transform: translateY(100%);
}
.sheet-leave-to .modal-panel {
    transform: translateY(100%);
}

/* ── Field group ── */
.field-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}
.field-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: color 0.25s ease;
}
.field-group:focus-within .field-label {
    color: #5B2163;
    font-weight: 600;
}

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

/* ── Jabatan input ── */
.anggota-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.875rem;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    font-size: 0.875rem;
    color: #111827;
    outline: none;
    transition:
        border-color 0.25s ease,
        background-color 0.25s ease,
        box-shadow 0.25s ease;
}
.anggota-input:focus {
    border-color: #5B2163;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(91,33,99,0.13);
}
</style>
