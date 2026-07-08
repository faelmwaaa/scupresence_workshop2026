<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';

const props = defineProps({ pendingRequests: Array });

const selectedRequest = ref(null);
const acceptForm = useForm({});
const declineForm = useForm({});

const accept = (req) => {
    acceptForm.post(route('pengurus.requests.accept', { user: req.id, organization: req.org_id }), {
        onSuccess: () => { selectedRequest.value = null; }
    });
};

const decline = (req) => {
    if (!confirm(`Tolak pendaftaran ${req.name}?`)) return;
    declineForm.post(route('pengurus.requests.decline', { user: req.id, organization: req.org_id }), {
        onSuccess: () => { selectedRequest.value = null; }
    });
};
</script>

<template>
    <Head title="Request List - Pengurus" />
    <div class="page-container bg-gray-50 pb-24">
        <div class="bg-white px-4 pt-12 pb-6 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900">Request Anggota</h1>
            <p class="text-sm text-gray-500 mt-1">{{ pendingRequests.length }} permintaan menunggu</p>
        </div>

        <div class="px-4 mt-6">
            <div v-if="pendingRequests.length === 0" class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm">Tidak ada permintaan pending</p>
            </div>

            <button v-for="req in pendingRequests" :key="req.pivot_id" @click="selectedRequest = req"
                class="card mb-3 w-full text-left hover:shadow-md transition-shadow flex items-center gap-3">
                <img :src="req.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(req.name) + '&background=5B2163&color=fff'"
                    class="w-12 h-12 rounded-full object-cover flex-shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">{{ req.name }}</p>
                    <p class="text-xs text-gray-500">{{ req.nim }}</p>
                    <p class="text-xs text-brand-purple font-medium">{{ req.org_name }}</p>
                </div>
                <span class="badge-pending">Pending</span>
            </button>
        </div>

        <!-- Request Detail Bottom Sheet -->
        <Transition name="sheet">
        <div v-if="selectedRequest"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end justify-center"
            @click.self="selectedRequest = null">
            <div class="modal-panel bg-white w-full max-w-lg rounded-t-3xl max-h-[85vh] overflow-y-auto">

                <!-- Drag handle -->
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>

                <div class="px-6 pt-3 pb-8">
                    <!-- Profile header -->
                    <div class="flex items-center gap-4 mb-5">
                        <img :src="selectedRequest.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedRequest.name) + '&background=5B2163&color=fff&size=128'"
                            class="w-16 h-16 rounded-full object-cover border-2 border-purple-100 shadow-sm flex-shrink-0" />
                        <div>
                            <h3 class="font-bold text-gray-900 text-base leading-tight">{{ selectedRequest.name }}</h3>
                            <p class="text-xs text-brand-purple font-medium mt-0.5">{{ selectedRequest.org_name }}</p>
                        </div>
                    </div>

                    <!-- Detail rows -->
                    <div class="bg-gray-50 rounded-2xl overflow-hidden mb-6">
                        <div class="flex justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-sm text-gray-500">NIM</span>
                            <span class="text-sm font-semibold text-gray-800">{{ selectedRequest.nim }}</span>
                        </div>
                        <div class="flex justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-sm text-gray-500">No. HP</span>
                            <span class="text-sm font-semibold text-gray-800">{{ selectedRequest.phone }}</span>
                        </div>
                        <div class="flex justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Fakultas</span>
                            <span class="text-sm font-semibold text-gray-800 text-right max-w-[60%]">{{ selectedRequest.fakultas }}</span>
                        </div>
                        <div v-if="selectedRequest.jabatan" class="flex justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Jabatan</span>
                            <span class="text-sm font-semibold text-gray-800">{{ selectedRequest.jabatan }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons at Bottom -->
                    <div class="flex gap-3">
                        <button
                            @click="decline(selectedRequest)"
                            :disabled="declineForm.processing"
                            class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-2xl font-semibold text-sm transition-all duration-150 active:scale-95 disabled:opacity-50"
                            style="background: linear-gradient(135deg, #fff1f2, #fecaca); color: #dc2626; box-shadow: 0 4px 14px rgba(220,38,38,0.18);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ declineForm.processing ? 'Memproses...' : 'Tolak' }}
                        </button>
                        <button
                            @click="accept(selectedRequest)"
                            :disabled="acceptForm.processing"
                            class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-2xl font-semibold text-sm text-white transition-all duration-150 active:scale-95 disabled:opacity-50"
                            style="background: linear-gradient(135deg, #5B2163, #8b3a9e); box-shadow: 0 4px 14px rgba(91,33,99,0.35);">
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
</style>
