<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';
import UnitDropdown from '@/Components/UnitDropdown.vue';

// Pelatih rekap is same as Pengurus rekap
const props = defineProps({
    organizations: Array,
    selectedOrgId: Number,
    anggotaList: Array,
});

const navigateToOrg = (orgId) => { window.location.href = route('pelatih.rekap') + `?org_id=${orgId}`; };
const selectedAnggota = ref(null);
const anggotaDetail = ref(null);
const showDetail = ref(false);

const viewAnggota = async (anggota) => {
    selectedAnggota.value = anggota;
    try {
        const res = await fetch(route('pelatih.rekap.detail', { anggota: anggota.id, organization: props.selectedOrgId }), { headers: { 'Accept': 'application/json' } });
        anggotaDetail.value = await res.json();
        showDetail.value = true;
    } catch { anggotaDetail.value = null; }
};

const statusLabel = { hadir: 'Hadir', terlambat: 'Terlambat', tidak_hadir: 'Tidak Hadir' };
const statusClass = { hadir: 'badge-hadir', terlambat: 'badge-terlambat', tidak_hadir: 'badge-tidak-hadir' };
</script>

<template>
    <Head title="Rekap - Pelatih UKM" />
    <div class="page-container bg-gray-50 pb-24">
        <div class="bg-white px-4 pt-12 pb-6 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900">Rekap Atlet</h1>
            <div class="mt-4" v-if="organizations.length > 0">
                <UnitDropdown :organizations="organizations" :modelValue="selectedOrgId" @change="navigateToOrg($event)" />
            </div>
        </div>

        <div class="px-4 mt-6">
            <div v-if="!selectedOrgId" class="text-center py-12 text-gray-400"><p>Pilih UKM untuk melihat rekap</p></div>
            <div v-else-if="anggotaList.length === 0" class="text-center py-12 text-gray-400"><p class="text-sm">Belum ada atlet aktif</p></div>
            <div v-else>
                <button v-for="anggota in anggotaList" :key="anggota.id" @click="viewAnggota(anggota)"
                    class="card mb-3 w-full text-left hover:shadow-md transition-shadow flex items-center gap-3">
                    <img :src="anggota.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(anggota.name) + '&background=2563eb&color=fff&size=64'"
                        class="w-12 h-12 rounded-full object-cover flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ anggota.name }}</p>
                        <p class="text-xs text-gray-500">{{ anggota.nim }}</p>
                        <div class="flex gap-3 mt-1.5 text-xs">
                            <span class="text-green-600">✅ {{ anggota.hadir }}</span>
                            <span class="text-yellow-600">⚠️ {{ anggota.terlambat }}</span>
                            <span class="text-red-600">❌ {{ anggota.tidak_hadir }}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Detail Modal -->
        <Transition name="sheet">
        <div v-if="showDetail && anggotaDetail" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end justify-center" @click.self="showDetail = false">
            <div class="modal-panel bg-white w-full max-w-lg rounded-t-3xl p-6 pb-10 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center gap-3 mb-4">
                    <img :src="anggotaDetail.anggota.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(anggotaDetail.anggota.name) + '&background=2563eb&color=fff'"
                        class="w-12 h-12 rounded-full object-cover" />
                    <div>
                        <p class="font-bold text-gray-900">{{ anggotaDetail.anggota.name }}</p>
                        <p class="text-xs text-gray-500">{{ anggotaDetail.anggota.nim }}</p>
                    </div>
                </div>
                <div v-for="p in anggotaDetail.presences" :key="p.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ p.title }}</p>
                        <p class="text-xs text-gray-500">{{ p.event_date }}</p>
                    </div>
                    <span :class="statusClass[p.status]">{{ statusLabel[p.status] }}</span>
                </div>
            </div>
        </div>
        </Transition>

        <BottomNav role="pelatih" />
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
</style>
