<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    organization: Object,
    members: Array,
    upcomingSchedules: Array,
    pastSchedules: Array,
});

const activeTab = ref('anggota');
const roleLabel = { admin: 'Admin', pengurus: 'Pengurus', pelatih: 'Pelatih UKM', anggota: 'Anggota' };
const roleColor = { admin: 'bg-gray-100 text-gray-700', pengurus: 'bg-purple-100 text-purple-700', pelatih: 'bg-blue-100 text-blue-700', anggota: 'bg-green-100 text-green-700' };
const statusLabel = { pending: 'Pending', active: 'Aktif' };
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
const formatTime = (t) => t?.substring(0, 5);

const removeAnggota = async (member) => {
    if (!confirm(`Apakah Anda yakin ingin mengeluarkan ${member.name} dari unit ini?`)) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(route('admin.units.members.remove', { organization: props.organization.id, user: member.id }), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        if (res.ok) {
            alert('Anggota berhasil dikeluarkan.');
            window.location.reload();
        }
    } catch (e) { console.error('Remove failed:', e); }
};
</script>

<template>
    <Head :title="organization.name" />
    <div class="min-h-screen bg-gray-50">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center gap-4">
                <Link :href="route('admin.home')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <p class="font-bold">{{ organization.name }}</p>
                    <p class="text-xs text-gray-400 uppercase">{{ organization.type }}</p>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-6">
            <!-- Tabs -->
            <div class="flex gap-3 mb-6 overflow-x-auto hide-scrollbar pb-1">
                <button @click="activeTab = 'anggota'" :class="['flex-shrink-0 px-5 py-2.5 rounded-xl font-medium text-sm transition-all', activeTab === 'anggota' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200']">
                    👥 Daftar Anggota
                </button>
                <button @click="activeTab = 'event'" :class="['flex-shrink-0 px-5 py-2.5 rounded-xl font-medium text-sm transition-all', activeTab === 'event' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200']">
                    📅 Event
                </button>
                <button @click="activeTab = 'history'" :class="['flex-shrink-0 px-5 py-2.5 rounded-xl font-medium text-sm transition-all', activeTab === 'history' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200']">
                    📂 Unit History
                </button>
            </div>

            <!-- Daftar Anggota -->
            <div v-if="activeTab === 'anggota'">
                <div v-if="members.length === 0" class="text-center py-12 text-gray-400"><p>Belum ada anggota</p></div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div v-for="(member, i) in members" :key="member.id" :class="['flex items-center gap-4 px-5 py-4', i > 0 ? 'border-t border-gray-100' : '']">
                        <img :src="member.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.name) + '&background=5B2163&color=fff&size=64'"
                            class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ member.name }}</p>
                            <p class="text-xs text-gray-500">{{ member.nim || member.email }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', roleColor[member.role]]">
                                {{ roleLabel[member.role] }}
                            </span>
                            <span v-if="member.jabatan" class="text-xs text-gray-400">{{ member.jabatan }}</span>
                        </div>
                        <button @click="removeAnggota(member)" class="ml-2 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0" title="Keluarkan Anggota">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Event -->
            <div v-if="activeTab === 'event'">
                <div v-if="upcomingSchedules.length === 0" class="text-center py-12 text-gray-400"><p>Tidak ada event mendatang</p></div>
                <div class="space-y-3">
                    <div v-for="s in upcomingSchedules" :key="s.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h3 class="font-semibold text-gray-900">{{ s.title }}</h3>
                        <div class="mt-2 space-y-1 text-sm text-gray-600">
                            <p>📅 {{ formatDate(s.event_date) }}</p>
                            <p>🕐 {{ formatTime(s.start_time) }} – {{ formatTime(s.end_time) }}</p>
                            <p>📍 {{ s.location }}</p>
                            <p v-if="s.description" class="text-gray-400 text-xs mt-2">{{ s.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unit History -->
            <div v-if="activeTab === 'history'">
                <div v-if="pastSchedules.length === 0" class="text-center py-12 text-gray-400"><p>Belum ada riwayat event</p></div>
                <div class="space-y-3">
                    <div v-for="s in pastSchedules" :key="s.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 opacity-75">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-700">{{ s.title }}</h3>
                                <p class="text-xs text-gray-500 mt-1">📅 {{ formatDate(s.event_date) }}</p>
                                <p class="text-xs text-gray-500">📍 {{ s.location }}</p>
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
