<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ organizations: Array });

const activeTab = ref('ormawa');
const filteredOrgs = computed(() => props.organizations.filter(o => o.type === activeTab.value));

const logout = () => router.post(route('logout'));
</script>

<template>
    <Head title="Admin Dashboard" />
    <div class="min-h-screen bg-gray-50">
        <!-- Admin Sidebar/Navbar for desktop, top bar for mobile -->
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="bg-white text-gray-900 w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold">S</span>
                    <div>
                        <p class="font-bold text-sm">SCUPresence</p>
                        <p class="text-xs text-gray-400">Super Admin Panel</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.requests')" class="text-sm text-gray-300 hover:text-white transition-colors">Request List</Link>
                    <button @click="logout" class="text-sm text-red-400 hover:text-red-300 transition-colors">Logout</button>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Daftar Unit</h1>

            <!-- Tab: ORMAWA / UKM -->
            <div class="flex gap-3 mb-6">
                <button @click="activeTab = 'ormawa'" :class="['px-6 py-2.5 rounded-xl font-medium text-sm transition-all', activeTab === 'ormawa' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">
                    🏛️ ORMAWA
                </button>
                <button @click="activeTab = 'ukm'" :class="['px-6 py-2.5 rounded-xl font-medium text-sm transition-all', activeTab === 'ukm' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">
                    🏆 UKM
                </button>
            </div>

            <!-- Unit Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link v-for="org in filteredOrgs" :key="org.id" :href="route('admin.unit.detail', org.id)"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all hover:border-gray-200 group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" :class="activeTab === 'ormawa' ? 'bg-purple-100' : 'bg-blue-100'">
                                <span class="text-lg">{{ activeTab === 'ormawa' ? '🏛️' : '🏆' }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-brand-purple transition-colors">{{ org.name }}</h3>
                            <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">{{ org.type }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-purple transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
