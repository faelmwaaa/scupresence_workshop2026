<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ pendingUsers: Array });

const selectedUser = ref(null);
const acceptForm = useForm({});
const declineForm = useForm({});

const accept = (user) => {
    acceptForm.post(route('admin.requests.accept', user.id), { onSuccess: () => { selectedUser.value = null; } });
};
const decline = (user) => {
    if (!confirm(`Tolak akun ${user.name}?`)) return;
    declineForm.post(route('admin.requests.decline', user.id), { onSuccess: () => { selectedUser.value = null; } });
};

const roleLabel = { pengurus: 'Pengurus', pelatih: 'Pelatih UKM' };
const roleColor = { pengurus: 'bg-purple-100 text-purple-700', pelatih: 'bg-blue-100 text-blue-700' };
</script>

<template>
    <Head title="Request List - Admin" />
    <div class="min-h-screen bg-gray-50">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center gap-4">
                <Link :href="route('admin.home')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <p class="font-bold">Request List</p>
                    <p class="text-xs text-gray-400">{{ pendingUsers.length }} pendaftaran menunggu</p>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-6">
            <div v-if="pendingUsers.length === 0" class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p>Tidak ada permintaan pending</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button v-for="user in pendingUsers" :key="user.id" @click="selectedUser = user"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left hover:shadow-md transition-all flex items-center gap-4">
                    <img :src="user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=5B2163&color=fff&size=64'"
                        referrerpolicy="no-referrer"
                        class="w-12 h-12 rounded-full object-cover flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-900 truncate">{{ user.name }}</p>
                            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', roleColor[user.role]]">{{ roleLabel[user.role] }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                        <p v-if="user.organizations?.length" class="text-xs text-brand-purple mt-1">
                            {{ user.organizations.map(o => o.name).join(', ') }}
                        </p>
                    </div>
                    <span class="badge-pending">Pending</span>
                </button>
            </div>
        </div>

        <!-- User Detail Modal -->
        <Transition name="sheet">
        <div v-if="selectedUser" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="selectedUser = null">
            <div class="modal-panel bg-white w-full max-w-md rounded-t-3xl sm:rounded-2xl shadow-xl overflow-hidden">

                <!-- Drag handle (mobile) -->
                <div class="flex justify-center pt-3 pb-1 sm:hidden">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>

                <!-- User detail content -->
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-5">
                        <img :src="selectedUser.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedUser.name) + '&background=5B2163&color=fff&size=128'"
                            referrerpolicy="no-referrer"
                            class="w-16 h-16 rounded-full object-cover border-2 border-purple-100 shadow-sm flex-shrink-0" />
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ selectedUser.name }}</h3>
                            <span :class="['text-xs px-2.5 py-1 rounded-full font-semibold mt-1 inline-block', roleColor[selectedUser.role]]">
                                {{ roleLabel[selectedUser.role] }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-0 text-sm divide-y divide-gray-100 rounded-xl bg-gray-50 overflow-hidden mb-5">
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-800 text-right max-w-[60%] truncate">{{ selectedUser.email }}</span>
                        </div>
                        <div v-if="selectedUser.nim" class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">NIM</span>
                            <span class="font-medium text-gray-800">{{ selectedUser.nim }}</span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">No. HP</span>
                            <span class="font-medium text-gray-800">{{ selectedUser.phone || '-' }}</span>
                        </div>
                        <div v-if="selectedUser.fakultas" class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">Fakultas</span>
                            <span class="font-medium text-gray-800 text-right max-w-[60%]">{{ selectedUser.fakultas }}</span>
                        </div>
                        <div v-if="selectedUser.organizations?.length" class="px-4 py-3">
                            <p class="text-gray-500 mb-2">Unit yang Didaftar</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="org in selectedUser.organizations" :key="org.id"
                                    class="text-xs bg-purple-100 text-[#5B2163] px-3 py-1 rounded-full font-medium">
                                    {{ org.name }} 
                                    <span class="opacity-75 font-normal ml-1">({{ org.pivot?.jabatan || (org.pivot?.is_pengurus ? 'Pengurus' : 'Anggota') }})</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons at Bottom -->
                    <div class="flex gap-3">
                        <button
                            @click="decline(selectedUser)"
                            :disabled="declineForm.processing"
                            class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl border-2 border-red-200 text-red-600 font-semibold text-sm hover:bg-red-50 active:bg-red-100 transition-all duration-150 disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                        <button
                            @click="accept(selectedUser)"
                            :disabled="acceptForm.processing"
                            class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl bg-[#5B2163] hover:bg-[#4a1b50] text-white font-semibold text-sm active:scale-95 transition-all duration-150 shadow-md disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ acceptForm.processing ? 'Memproses...' : 'Terima' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Transition>
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
