<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BottomNav from '@/Components/BottomNav.vue';

const props = defineProps({ user: Object });
const preview = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { preview.value = ev.target.result; };
    reader.readAsDataURL(file);
    const fd = new FormData();
    fd.append('profile_picture', file);
    router.post(route('profile.picture'), fd, { forceFormData: true });
};
const logout = () => router.post(route('logout'));
</script>

<template>
    <Head title="Profil - Pelatih UKM" />
    <div class="page-container bg-gray-50 pb-24">
        <div class="bg-gradient-to-b from-[#5B2163] to-purple-700 px-4 pt-14 pb-20 text-white text-center">
            <div class="relative inline-block">
                <img :src="preview || user.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=fff&color=5B2163&size=128'"
                    class="w-24 h-24 rounded-full object-cover border-4 border-white/30 shadow-xl" />
                <label class="absolute bottom-0 right-0 bg-white text-brand-purple rounded-full p-1.5 cursor-pointer shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                </label>
            </div>
            <h2 class="text-xl font-bold mt-3">{{ user.name }}</h2>
            <p class="text-purple-200 text-sm">Pelatih UKM</p>
            <span :class="['mt-2 inline-block px-3 py-1 rounded-full text-xs font-medium', user.account_status === 'active' ? 'bg-green-400/20 text-green-100' : 'bg-yellow-400/20 text-yellow-100']">
                {{ user.account_status === 'active' ? '✅ Aktif' : '⏳ Menunggu Verifikasi' }}
            </span>
        </div>
        <div class="px-4 -mt-8 space-y-4">
            <div class="card">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-3">Informasi Akun</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium text-gray-800">{{ user.email }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">No. HP</span><span class="font-medium text-gray-800">{{ user.phone || '-' }}</span></div>
                </div>
            </div>
            <button @click="logout" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-xl font-medium hover:bg-red-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar dari Akun
            </button>
        </div>
        <BottomNav role="pelatih" />
    </div>
</template>
