<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';
import UnitDropdown from '@/Components/UnitDropdown.vue';

const props = defineProps({
    organizations: Array,
    selectedOrgId: Number,
    stats: Object,
});

const selectedOrg = ref(props.selectedOrgId || props.organizations[0]?.id || null);

const navigateToOrg = (orgId) => {
    window.location.href = route('anggota.rekap') + `?org_id=${orgId}`;
};

const total = computed(() => {
    if (!props.stats) return 0;
    return (props.stats.hadir || 0) + (props.stats.terlambat || 0) + (props.stats.tidakHadir || 0);
});

const hadirPct = computed(() => total.value ? Math.round((props.stats.hadir / total.value) * 100) : 0);
const terlambatPct = computed(() => total.value ? Math.round((props.stats.terlambat / total.value) * 100) : 0);
const tidakHadirPct = computed(() => total.value ? Math.round((props.stats.tidakHadir / total.value) * 100) : 0);

const statusLabel = { hadir: 'Hadir', terlambat: 'Terlambat', tidak_hadir: 'Tidak Hadir' };
const statusClass = { hadir: 'badge-hadir', terlambat: 'badge-terlambat', tidak_hadir: 'badge-tidak-hadir' };

// SVG Donut Chart
const radius = 40;
const circumference = 2 * Math.PI * radius;
const hadirDash = computed(() => (hadirPct.value / 100) * circumference);
const terlambatDash = computed(() => (terlambatPct.value / 100) * circumference);
const tidakHadirDash = computed(() => (tidakHadirPct.value / 100) * circumference);
</script>

<template>
    <Head title="Rekap - Anggota" />
    <div class="page-container bg-gray-50 pb-24">
        <!-- Header -->
        <div class="bg-white px-4 pt-12 pb-6 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900">Rekap Kehadiran</h1>
            <div class="mt-4" v-if="organizations.length > 0">
                <UnitDropdown :organizations="organizations" :modelValue="selectedOrgId" @change="navigateToOrg($event)" />
            </div>
        </div>

        <div class="px-4 mt-6">
            <div v-if="!stats" class="text-center py-12 text-gray-400">
                <p class="text-sm">Pilih unit untuk melihat rekap</p>
            </div>

            <div v-else class="space-y-4">
                <!-- Donut Charts -->
                <div class="grid grid-cols-3 gap-3">
                    <!-- Hadir -->
                    <div class="card text-center">
                        <svg viewBox="0 0 100 100" class="w-20 h-20 mx-auto -rotate-90">
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#f0fdf4" stroke-width="16"/>
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#22c55e" stroke-width="16"
                                :stroke-dasharray="`${hadirDash} ${circumference}`" stroke-linecap="round"/>
                        </svg>
                        <p class="text-2xl font-bold text-green-600 -mt-2">{{ hadirPct }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Hadir</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.hadir }}</p>
                    </div>
                    <!-- Terlambat -->
                    <div class="card text-center">
                        <svg viewBox="0 0 100 100" class="w-20 h-20 mx-auto -rotate-90">
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#fefce8" stroke-width="16"/>
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#eab308" stroke-width="16"
                                :stroke-dasharray="`${terlambatDash} ${circumference}`" stroke-linecap="round"/>
                        </svg>
                        <p class="text-2xl font-bold text-yellow-600 -mt-2">{{ terlambatPct }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Terlambat</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.terlambat }}</p>
                    </div>
                    <!-- Tidak Hadir -->
                    <div class="card text-center">
                        <svg viewBox="0 0 100 100" class="w-20 h-20 mx-auto -rotate-90">
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#fef2f2" stroke-width="16"/>
                            <circle cx="50" cy="50" :r="radius" fill="none" stroke="#ef4444" stroke-width="16"
                                :stroke-dasharray="`${tidakHadirDash} ${circumference}`" stroke-linecap="round"/>
                        </svg>
                        <p class="text-2xl font-bold text-red-600 -mt-2">{{ tidakHadirPct }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Tidak Hadir</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.tidakHadir }}</p>
                    </div>
                </div>

                <div class="card text-center py-3">
                    <p class="text-sm text-gray-500">Total Event Diikuti</p>
                    <p class="text-3xl font-bold text-brand-purple">{{ total }}</p>
                </div>

                <!-- History -->
                <div v-if="stats.history?.length > 0">
                    <h2 class="section-title">Riwayat Kehadiran</h2>
                    <div v-for="item in stats.history" :key="item.schedule_id" class="card mb-2 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ item.title }}</p>
                            <p class="text-xs text-gray-500">{{ item.event_date }}</p>
                        </div>
                        <span :class="statusClass[item.status]">{{ statusLabel[item.status] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <BottomNav role="anggota" />
    </div>
</template>
