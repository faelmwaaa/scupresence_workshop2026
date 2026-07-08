<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';
import UnitDropdown from '@/Components/UnitDropdown.vue';

const props = defineProps({
    user: Object,
    organizations: Array,
});

const selectedOrgId = ref(props.organizations[0]?.id || null);
const selectedOrg = computed(() => props.organizations.find(o => o.id === selectedOrgId.value));
const isPengurus = computed(() => selectedOrg.value?.pivot?.is_pengurus ?? false);
const isKetuaOrWakil = computed(() => {
    const jabatan = (selectedOrg.value?.pivot?.jabatan || '').toLowerCase().trim();
    return ['ketua', 'wakil', 'wakil ketua'].includes(jabatan);
});
const schedules = ref([]);
const showAddEvent = ref(false);

const eventForm = useForm({
    organization_id: selectedOrgId.value,
    title: '',
    event_date: '',
    start_time: '',
    end_time: '',
    location: '',
    description: '',
    is_recurring: false,
    repeat_until: '',
});

const isPending = props.user?.account_status === 'pending';

const fetchSchedules = async (orgId) => {
    if (!orgId) return;
    selectedOrgId.value = orgId;
    eventForm.organization_id = orgId;
    try {
        const res = await fetch(`/pengurus/schedules/${orgId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        const data = await res.json();
        schedules.value = data.upcoming || [];
    } catch (e) { schedules.value = []; }
};

const submitEvent = () => {
    eventForm.post(route('pengurus.schedules.store'), {
        onSuccess: () => { showAddEvent.value = false; eventForm.reset(); fetchSchedules(selectedOrgId.value); }
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
const formatTime = (t) => t?.substring(0, 5);

// ── Toggle open/close ──
const toggleOpen = async (schedule) => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(route('pengurus.schedules.toggleOpen', schedule.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (!res.ok) {
            const err = await res.json();
            alert(err.error || 'Server error');
            return;
        }
        const data = await res.json();
        schedule.is_open = data.is_open;
    } catch (e) { console.error('Toggle failed:', e); }
};

// ── Presence validation modal ──
const showValidation = ref(false);
const validationSchedule = ref(null);
const validationPresences = ref([]);
const loadingValidation = ref(false);
const selectedPresence = ref(null);

const openValidation = async (schedule) => {
    validationSchedule.value = schedule;
    selectedPresence.value = null;
    loadingValidation.value = true;
    showValidation.value = true;
    try {
        const res = await fetch(route('pengurus.presences.index', schedule.id), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        validationPresences.value = data.presences || [];
    } catch (e) { validationPresences.value = []; }
    loadingValidation.value = false;
};

const setPresenceStatus = async (presence, status) => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(route('presence.validate', presence.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ status }),
        });
        if (res.ok) {
            presence.status = status;
        }
    } catch (e) { console.error(e); }
};

const statusBadge = {
    menunggu:    { label: 'Menunggu', cls: 'bg-blue-100 text-blue-700' },
    hadir:       { label: 'Hadir', cls: 'bg-green-100 text-green-700' },
    terlambat:   { label: 'Terlambat', cls: 'bg-yellow-100 text-yellow-700' },
    tidak_hadir: { label: 'Tidak Hadir', cls: 'bg-red-100 text-red-700' },
};

if (selectedOrgId.value) fetchSchedules(selectedOrgId.value);
</script>

<template>
    <Head title="Home - Pengurus" />
    <div class="page-container bg-gray-50 pb-24">
        <!-- Header -->
        <div class="px-4 py-6 bg-white shadow-sm sticky top-0 z-40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Selamat datang 👋</p>
                    <h1 class="text-xl font-bold text-gray-900 truncate max-w-[200px]">{{ user.name }}</h1>
                </div>
                <img
                    :src="user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=5B2163&color=fff&size=64'"
                    referrerpolicy="no-referrer"
                    class="w-11 h-11 rounded-full object-cover border-2 border-purple-100 shadow-sm flex-shrink-0"
                />
            </div>

            <!-- Unit selector -->
            <div v-if="organizations.length > 0" class="flex items-center gap-2 mt-2">
                <UnitDropdown :organizations="organizations" v-model="selectedOrgId" @change="fetchSchedules" :showRole="true" />
                <Link href="/onboarding/mahasiswa" class="w-[46px] h-[46px] flex-shrink-0 flex items-center justify-center bg-white border-2 border-dashed border-brand-purple text-brand-purple rounded-xl hover:bg-brand-purple hover:text-white transition-colors" title="Tambah Unit Lain">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </Link>
            </div>
        </div>

        <!-- Pending state -->
        <div v-if="isPending" class="px-4 mt-6">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
                <div class="text-5xl mb-3">⏳</div>
                <h3 class="font-bold text-amber-800 mb-1 text-lg">Menunggu Persetujuan</h3>
                <p class="text-sm text-amber-600 leading-relaxed">
                    Pengurus sedang memproses pendaftaranmu. Kamu bisa menambahkan unit lain sambil menunggu.
                </p>
                <Link href="/onboarding/mahasiswa" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-medium hover:bg-amber-700 transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Unit Lain
                </Link>
            </div>
        </div>

        <div v-else class="px-4 mt-6">
            <!-- Add Event Button -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Agenda & Jadwal</h2>
                <button v-if="isPengurus" @click="showAddEvent = true" class="flex items-center gap-1.5 bg-[#5B2163] text-white px-3 py-1.5 rounded-xl text-sm font-semibold hover:bg-[#4a1b50] transition-colors active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Event
                </button>
            </div>

            <div v-if="schedules.length === 0" class="text-center py-12 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm">Belum ada jadwal. Buat event baru!</p>
            </div>

            <div v-for="s in schedules" :key="s.id" class="card mb-3">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-gray-900 flex-1 pr-3">{{ s.title }}</h3>
                    <button v-if="isPengurus" @click="toggleOpen(s)"
                        :class="['flex-shrink-0 text-sm font-bold px-3.5 py-1.5 rounded-xl border transition-all active:scale-95 shadow-sm flex items-center gap-1.5', s.is_open ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100']"
                        :title="s.is_open ? 'Klik untuk tutup presensi' : 'Klik untuk buka presensi'"
                    >
                        <span class="text-xs">{{ s.is_open ? '🟢' : '🔴' }}</span>
                        {{ s.is_open ? 'Dibuka' : 'Ditutup' }}
                    </button>
                    <span v-else :class="['flex-shrink-0 text-[10px] uppercase tracking-wider font-bold px-2 py-1 rounded-md border', s.is_open ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-500 border-gray-200']">
                        {{ s.is_open ? 'Presensi Buka' : 'Presensi Tutup' }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">📅 {{ formatDate(s.event_date) }}</p>
                <p class="text-xs text-gray-500">🕐 {{ formatTime(s.start_time) }} – {{ formatTime(s.end_time) }}</p>
                <p class="text-xs text-gray-500">📍 {{ s.location }}</p>
                <p v-if="s.description" class="text-xs text-gray-400 mt-2 line-clamp-2">{{ s.description }}</p>
                <!-- Validasi Presensi Button -->
                <div class="flex gap-2 mt-3">
                    <button v-if="isKetuaOrWakil" @click="openValidation(s)"
                        class="flex-1 text-center text-xs font-semibold text-brand-purple border border-brand-purple/30 rounded-xl py-2 hover:bg-brand-purple/5 transition-all active:scale-95"
                    >
                        👥 Validasi Presensi
                    </button>
                    <Link :href="route('anggota.schedule.show', s.id)"
                        class="flex-1 text-center text-xs font-semibold text-white bg-brand-purple rounded-xl py-2 hover:bg-brand-purple-dark transition-all active:scale-95 flex items-center justify-center gap-1"
                    >
                        📝 Isi Presensi
                    </Link>
                </div>
            </div>
        </div>
        <!-- Validation Modal -->
        <Transition name="sheet">
        <div v-if="showValidation" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end justify-center" @click.self="showValidation = false">
            <div class="modal-panel bg-white w-full max-w-lg rounded-t-3xl max-h-[85vh] overflow-y-auto">
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>
                <div class="px-6 pt-2 pb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Validasi Presensi</h3>
                        <button @click="showValidation = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p v-if="validationSchedule" class="text-sm text-gray-500 mb-4">{{ validationSchedule.title }}</p>

                    <!-- List View -->
                    <div v-if="!selectedPresence">
                        <div v-if="loadingValidation" class="text-center py-8 text-gray-400">
                            <div class="w-8 h-8 border-2 border-brand-purple border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                            <p class="text-sm">Memuat data...</p>
                        </div>
                        <div v-else-if="validationPresences.length === 0" class="text-center py-8 text-gray-400">
                            <div class="text-3xl mb-2">📭</div>
                            <p class="text-sm">Belum ada anggota yang absen</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="p in validationPresences" :key="p.id"
                                @click="selectedPresence = p"
                                class="flex items-center gap-3 p-3 rounded-2xl border border-gray-100 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
                                <img :src="p.user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(p.user.name) + '&background=5B2163&color=fff&size=64'"
                                    class="w-12 h-12 rounded-full object-cover flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ p.user.name }}</p>
                                    <p class="text-xs text-gray-400">{{ p.user.nim }} · {{ p.created_at }}</p>
                                </div>
                                <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0', statusBadge[p.status]?.cls]">
                                    {{ statusBadge[p.status]?.label }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Detail View -->
                    <div v-else class="flex flex-col h-full max-h-[75vh]">
                        <!-- Header -->
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 flex-shrink-0">
                            <button @click="selectedPresence = null" class="p-2 -ml-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <img :src="selectedPresence.user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedPresence.user.name) + '&background=5B2163&color=fff&size=64'"
                                class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 truncate text-sm">{{ selectedPresence.user.name }}</p>
                                <p class="text-xs text-gray-500">{{ selectedPresence.user.nim }} · {{ selectedPresence.created_at }}</p>
                            </div>
                            <span :class="['text-xs font-bold px-2.5 py-1 rounded-md flex-shrink-0', statusBadge[selectedPresence.status]?.cls]">
                                {{ statusBadge[selectedPresence.status]?.label }}
                            </span>
                        </div>

                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-y-auto py-4 space-y-6">
                            <!-- Foto Kehadiran -->
                            <div v-if="selectedPresence.foto_path" class="space-y-3">
                                <div class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                    <svg class="w-4 h-4 text-brand-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Foto Kehadiran
                                </div>
                                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50">
                                    <img :src="selectedPresence.foto_path" class="w-full object-cover" />
                                </div>
                            </div>

                            <!-- Lokasi GPS -->
                            <div v-if="selectedPresence.latitude && selectedPresence.longitude" class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                        <svg class="w-4 h-4 text-brand-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Lokasi GPS
                                    </div>
                                </div>
                                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50 h-56 relative group">
                                    <iframe width="100%" height="100%" frameborder="0" style="border:0"
                                        :src="`https://maps.google.com/maps?q=${selectedPresence.latitude},${selectedPresence.longitude}&hl=id&z=15&output=embed`">
                                    </iframe>
                                    <div class="absolute inset-0 pointer-events-none rounded-2xl shadow-[inset_0_0_0_1px_rgba(0,0,0,0.05)]"></div>
                                </div>
                                <p class="text-[11px] font-mono text-gray-400 mt-1">Koordinat: {{ selectedPresence.latitude }}, {{ selectedPresence.longitude }}</p>
                            </div>
                        </div>

                        <!-- Validation Buttons -->
                        <div v-if="selectedPresence.status === 'menunggu'" class="pt-4 border-t border-gray-100 flex gap-3 mt-2 flex-shrink-0">
                            <button @click="setPresenceStatus(selectedPresence, 'hadir')"
                                class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition-all active:scale-95 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-xs font-bold">Hadir</span>
                            </button>
                            <button @click="setPresenceStatus(selectedPresence, 'terlambat')"
                                class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 transition-all active:scale-95 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-bold">Terlambat</span>
                            </button>
                            <button @click="setPresenceStatus(selectedPresence, 'tidak_hadir')"
                                class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-all active:scale-95 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-xs font-bold">Tidak Hadir</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Transition>

        <!-- Add Event Modal -->
        <Transition name="sheet">
        <div v-if="showAddEvent" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end justify-center" @click.self="showAddEvent = false">
            <div class="modal-panel bg-white w-full max-w-lg rounded-t-3xl max-h-[90vh] overflow-y-auto">
                <!-- Drag handle -->
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>

                <div class="px-6 pt-2 pb-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-5">Tambah Event Baru</h3>
                    <div class="space-y-4">
                        <!-- Nama Event -->
                        <div class="field-group">
                            <label class="field-label">Nama Event</label>
                            <input v-model="eventForm.title" type="text" class="input-field" placeholder="Nama kegiatan" />
                        </div>

                        <!-- Tanggal -->
                        <div class="field-group">
                            <label class="field-label">Tanggal</label>
                            <div class="datetime-wrapper">
                                <span class="datetime-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input v-model="eventForm.event_date" type="date" class="datetime-input" />
                            </div>
                        </div>

                        <!-- Jam -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="field-group">
                                <label class="field-label">Jam Mulai</label>
                                <div class="datetime-wrapper">
                                    <span class="datetime-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input v-model="eventForm.start_time" type="time" class="datetime-input" />
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Jam Selesai</label>
                                <div class="datetime-wrapper">
                                    <span class="datetime-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input v-model="eventForm.end_time" type="time" class="datetime-input" />
                                </div>
                            </div>
                        </div>

                        <!-- Tempat -->
                        <div class="field-group">
                            <label class="field-label">Tempat</label>
                            <input v-model="eventForm.location" type="text" class="input-field" placeholder="Lokasi kegiatan" />
                        </div>

                        <!-- Deskripsi -->
                        <div class="field-group">
                            <label class="field-label">Deskripsi</label>
                            <textarea v-model="eventForm.description" rows="3" class="input-field resize-none" placeholder="Deskripsi kegiatan (opsional)"></textarea>
                        </div>

                        <!-- Rutin -->
                        <div class="flex items-center mt-2 mb-2">
                            <input v-model="eventForm.is_recurring" type="checkbox" id="is_recurring" class="w-4 h-4 text-brand-purple border-gray-300 rounded focus:ring-brand-purple">
                            <label for="is_recurring" class="ml-2 text-sm font-medium text-gray-700">Jadikan Kegiatan Rutin Mingguan</label>
                        </div>
                        
                        <!-- Ulangi Sampai -->
                        <Transition name="fade">
                            <div v-if="eventForm.is_recurring" class="field-group mb-4">
                                <label class="field-label">Ulangi Sampai Tanggal</label>
                                <div class="datetime-wrapper">
                                    <span class="datetime-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <input v-model="eventForm.repeat_until" type="date" class="datetime-input" :min="eventForm.event_date" />
                                </div>
                            </div>
                        </Transition>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-1">
                            <button @click="showAddEvent = false"
                                class="flex-1 py-3.5 rounded-2xl font-semibold text-sm text-gray-600 transition-all active:scale-95"
                                style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                Batal
                            </button>
                            <button @click="submitEvent" :disabled="eventForm.processing"
                                class="flex-1 py-3.5 rounded-2xl font-semibold text-sm text-white transition-all active:scale-95 disabled:opacity-50"
                                style="background: linear-gradient(135deg, #5B2163, #8b3a9e); box-shadow: 0 4px 14px rgba(91,33,99,0.35);">
                                {{ eventForm.processing ? 'Menyimpan...' : 'Simpan Event' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Transition>

        <FlashToast />
        <BottomNav role="pengurus" />
    </div>
</template>

<style scoped>
/* ── Bottom sheet transition ── */
.sheet-enter-active { transition: opacity 0.3s ease; }
.sheet-leave-active { transition: opacity 0.25s ease; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-enter-active .modal-panel { transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.sheet-leave-active .modal-panel { transition: transform 0.25s cubic-bezier(0.4, 0, 1, 1); }
.sheet-enter-from .modal-panel, .sheet-leave-to .modal-panel { transform: translateY(100%); }

/* ── Field group: label + wrapper together for :focus-within label morph ── */
.field-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}
.field-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: color 0.25s ease, font-weight 0.2s ease;
}
.field-group:focus-within .field-label {
    color: #5B2163;
    font-weight: 600;
}

/* ── Wrapper: positions icon + handles morph on focus ── */
.datetime-wrapper {
    position: relative;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.datetime-wrapper:focus-within {
    transform: scale(1.025);
}

/* ── Icon: rotates + turns purple on focus ── */
.datetime-icon {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0.875rem;
    display: flex;
    align-items: center;
    pointer-events: none;
    z-index: 1;
    color: #9ca3af;
    transition:
        color 0.25s ease,
        transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.datetime-wrapper:focus-within .datetime-icon {
    color: #5B2163;
    transform: scale(1.25) rotate(-12deg);
}

/* ── Input: morphs with spring bounce + glow on focus ── */
.datetime-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border-radius: 0.875rem;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    font-size: 0.875rem;
    color: #111827;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    transition:
        border-color 0.25s ease,
        background-color 0.25s ease,
        box-shadow 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.datetime-input:focus {
    border-color: #5B2163;
    background: #fff;
    box-shadow:
        0 0 0 3.5px rgba(91, 33, 99, 0.14),
        0 6px 20px rgba(91, 33, 99, 0.12);
}

/* ── Morph keyframe: field bounces in on focus ── */
@keyframes morphField {
    0%   { box-shadow: 0 0 0 0px rgba(91,33,99,0); }
    40%  { box-shadow: 0 0 0 5px rgba(91,33,99,0.18), 0 8px 24px rgba(91,33,99,0.15); }
    100% { box-shadow: 0 0 0 3.5px rgba(91,33,99,0.14), 0 6px 20px rgba(91,33,99,0.12); }
}
.datetime-input:focus {
    animation: morphField 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* ── Regular input-field morph (Nama Event, Tempat, Deskripsi) ── */
.field-group .input-field {
    transition:
        border-color 0.25s ease,
        background-color 0.25s ease,
        box-shadow 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.field-group .input-field:focus {
    border-color: #5B2163 !important;
    background: #fff !important;
    box-shadow:
        0 0 0 3.5px rgba(91, 33, 99, 0.14),
        0 6px 20px rgba(91, 33, 99, 0.12) !important;
    animation: morphField 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* ── Hide native picker icon so our SVG icon shows cleanly ── */
.datetime-input::-webkit-calendar-picker-indicator {
    opacity: 0;
    position: absolute;
    right: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}
</style>
