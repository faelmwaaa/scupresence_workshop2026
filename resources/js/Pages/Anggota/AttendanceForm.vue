<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';

const props = defineProps({
    schedule: Object,
    existingPresence: Object,
    canAttend: Boolean,
});

const form = useForm({
    foto: null,
    latitude: null,
    longitude: null,
});

const locationStatus = ref('idle'); // idle | loading | success | error
const locationError = ref('');
const fotoPreview = ref(null);
const timeNow = ref('');
let timer = null;

const updateTime = () => { timeNow.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }); };

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
    getLocation();
});
onUnmounted(() => clearInterval(timer));

const getLocation = () => {
    locationStatus.value = 'loading';
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationError.value = 'Browser tidak mendukung GPS.';
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.latitude = pos.coords.latitude;
            form.longitude = pos.coords.longitude;
            locationStatus.value = 'success';
        },
        () => {
            locationStatus.value = 'error';
            locationError.value = 'Izin lokasi ditolak. Harap aktifkan GPS.';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const onFotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.foto = file;
    const reader = new FileReader();
    reader.onload = (ev) => { fotoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const submit = () => {
    const fd = new FormData();
    if (form.foto) fd.append('foto', form.foto);
    if (form.latitude) fd.append('latitude', form.latitude);
    if (form.longitude) fd.append('longitude', form.longitude);
    router.post(route('anggota.presence.store', props.schedule.id), fd);
};

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const formatTime = (t) => t?.substring(0, 5);

const statusLabel = { menunggu: '⏳ Menunggu Validasi', hadir: '✅ Hadir', terlambat: '⚠️ Terlambat', tidak_hadir: '❌ Tidak Hadir' };
const statusColor = { menunggu: 'bg-blue-100 text-blue-700', hadir: 'bg-green-100 text-green-700', terlambat: 'bg-yellow-100 text-yellow-700', tidak_hadir: 'bg-red-100 text-red-700' };
</script>

<template>
    <Head title="Form Absensi" />
    <div class="page-container bg-gray-50 pb-24">
        <!-- Header -->
        <div class="bg-white px-4 pt-12 pb-4 shadow-sm">
            <button @click="$inertia.visit(route('anggota.home'))" class="flex items-center gap-2 text-brand-purple mb-3 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <h1 class="text-xl font-bold text-gray-900">Form Absensi</h1>
        </div>

        <div class="px-4 mt-4 space-y-4">
            <!-- Event Info Card -->
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-gray-900">{{ schedule.title }}</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ schedule.organization?.name }}</p>
                    </div>
                    <!-- Open/Closed badge -->
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', schedule.is_open ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        {{ schedule.is_open ? '🟢 Presensi Dibuka' : '🔴 Presensi Ditutup' }}
                    </span>
                </div>
                <div class="space-y-1 text-sm text-gray-600">
                    <p>📅 {{ formatDate(schedule.event_date) }}</p>
                    <p>🕐 {{ formatTime(schedule.start_time) }} – {{ formatTime(schedule.end_time) }}</p>
                    <p>📍 {{ schedule.location }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                    <p class="text-2xl font-mono font-bold text-brand-purple">{{ timeNow }}</p>
                    <p class="text-xs text-gray-400">Waktu Sekarang</p>
                </div>
            </div>

            <!-- Already submitted -->
            <div v-if="existingPresence" class="card text-center py-6">
                <div class="text-4xl mb-2">✅</div>
                <p class="font-bold text-gray-900">Absensi sudah dikumpulkan</p>
                <span :class="['mt-2 inline-block px-3 py-1 rounded-full text-sm font-semibold', statusColor[existingPresence.status]]">
                    {{ statusLabel[existingPresence.status] }}
                </span>
                <p v-if="existingPresence.status === 'menunggu'" class="text-xs text-gray-500 mt-2">
                    Pengurus akan memvalidasi kehadiranmu.
                </p>
            </div>

            <!-- Attendance Form -->
            <div v-else-if="canAttend" class="space-y-4">
                <!-- GPS Warning -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-sm text-red-700 font-medium">Harap nyalakan GPS atau akses lokasi untuk bukti kehadiran!</p>
                </div>

                <!-- GPS Status -->
                <div class="card">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div :class="['w-2.5 h-2.5 rounded-full', locationStatus === 'success' ? 'bg-green-500' : locationStatus === 'loading' ? 'bg-yellow-400 animate-pulse' : 'bg-red-500']"></div>
                            <span class="text-sm font-medium text-gray-700">
                                {{ locationStatus === 'success' ? `GPS Aktif (${form.latitude?.toFixed(5)}, ${form.longitude?.toFixed(5)})` : locationStatus === 'loading' ? 'Mengambil lokasi...' : locationError }}
                            </span>
                        </div>
                        <button v-if="locationStatus === 'error'" @click="getLocation" class="text-xs text-brand-purple font-medium">Coba Lagi</button>
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="card space-y-3">
                    <p class="font-medium text-gray-900">📸 Foto Bukti Kehadiran <span class="text-red-500">*</span></p>
                    <label class="block">
                        <div v-if="!fotoPreview" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-brand-purple hover:bg-brand-purple/5 transition-all">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="font-medium text-gray-700">Ambil atau Pilih Foto</p>
                            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, HEIC</p>
                        </div>
                        <div v-else class="relative rounded-xl overflow-hidden group cursor-pointer border-2 border-brand-purple">
                            <img :src="fotoPreview" class="w-full h-48 object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <div class="bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-white font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Ubah Foto
                                </div>
                            </div>
                        </div>
                        <input type="file" accept="image/*,.heic,.heif" capture="environment" class="hidden" @change="onFotoChange" />
                    </label>
                </div>

                <p class="text-xs text-gray-500 text-center px-2">
                    Kehadiranmu akan dikonfirmasi oleh Pengurus setelah absensi dikirim.
                </p>

                <!-- Submit -->
                <button
                    @click="submit"
                    :disabled="form.processing || !form.foto || locationStatus !== 'success'"
                    class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Kirim Absensi' }}
                </button>
            </div>

            <!-- Presensi Ditutup -->
            <div v-else class="card text-center py-8">
                <div class="text-4xl mb-3">{{ !schedule.is_open ? '🔒' : '📅' }}</div>
                <p class="font-bold text-gray-900">
                    {{ !schedule.is_open ? 'Presensi Belum/Sudah Ditutup' : 'Bukan Hari Event Ini' }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ !schedule.is_open
                        ? 'Pengurus belum membuka presensi atau presensi sudah ditutup.'
                        : 'Absensi hanya bisa dilakukan pada hari yang sama dengan event.' }}
                </p>
            </div>
        </div>

        <FlashToast />
        <BottomNav role="anggota" />
    </div>
</template>
