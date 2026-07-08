<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: { type: String, required: true }, // 'anggota' | 'pengurus' | 'pelatih'
});

const page = usePage();

const navItems = computed(() => {
    const base = props.role;
    const items = [
        { label: 'Home', routeName: `${base}.home`, icon: 'home' },
        { label: 'Rekap', routeName: `${base}.rekap`, icon: 'chart' },
    ];
    if (base === 'pengurus') {
        items.splice(2, 0, { label: 'Request', routeName: `${base}.requests`, icon: 'bell' });
    }
    items.push({ label: 'Profil', routeName: `${base}.profil`, icon: 'user' });
    return items;
});

const isActive = (routeName) => route().current(routeName);
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-lg z-50 safe-area-bottom">
        <div class="max-w-lg mx-auto flex">
            <Link
                v-for="item in navItems"
                :key="item.routeName"
                :href="route(item.routeName)"
                :class="['bottom-nav-item', isActive(item.routeName) ? 'text-brand-purple' : 'text-gray-400']"
            >
                <!-- Home Icon -->
                <svg v-if="item.icon === 'home'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <!-- Chart Icon -->
                <svg v-if="item.icon === 'chart'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <!-- Bell Icon -->
                <svg v-if="item.icon === 'bell'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <!-- User Icon -->
                <svg v-if="item.icon === 'user'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs">{{ item.label }}</span>
            </Link>
        </div>
    </nav>
</template>
