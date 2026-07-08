<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '' });
const submit = () => form.post(route('admin.login'));
</script>

<template>
    <Head title="Admin Login" />
    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <span class="bg-white text-gray-900 w-10 h-10 flex items-center justify-center rounded-xl text-lg font-bold">S</span>
                    <span class="text-white font-bold text-xl">SCUPresence</span>
                </div>
                <p class="text-gray-400 text-sm">Super Admin Access</p>
            </div>

            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-2xl">
                <div v-if="form.errors.email" class="mb-4 bg-red-900/30 border border-red-800 rounded-xl p-3">
                    <p class="text-red-400 text-sm">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" placeholder="admin@scupresence.ac.id" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                        <input v-model="form.password" type="password" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" placeholder="••••••••" />
                    </div>
                    <button @click="submit" :disabled="form.processing" class="w-full bg-white text-gray-900 py-3 rounded-xl font-semibold text-sm hover:bg-gray-100 transition-all active:scale-95 mt-2">
                        {{ form.processing ? 'Masuk...' : 'Masuk sebagai Admin' }}
                    </button>
                </div>
            </div>

            <p class="text-center text-gray-600 text-xs mt-6">Halaman ini tidak ditautkan dari halaman utama.</p>
        </div>
    </div>
</template>
