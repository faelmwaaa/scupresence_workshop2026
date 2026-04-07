<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Receive organizations from the Controller
defineProps({
    organizations: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'anggota', // Default to Anggota
    organization_id: '',
    nim: '',
    phone: '',
    jabatan: '',
});

const submit = () => {
    // If they change roles, clear the unused data so it doesn't cause errors
    if (form.role === 'anggota') {
        form.phone = '';
        form.jabatan = '';
    } else {
        form.nim = '';
    }
    
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4">
                <InputLabel value="Mendaftar Sebagai:" />
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" v-model="form.role" value="anggota" class="text-indigo-600 border-gray-300 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">Anggota Biasa</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" v-model="form.role" value="bph" class="text-indigo-600 border-gray-300 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2 text-sm text-gray-600">Pengurus (BPH/Pelatih)</span>
                    </label>
                </div>
            </div>

            <div class="mt-4" v-if="form.role === 'anggota'">
                <InputLabel for="nim" value="NIM" />
                <TextInput 
                    id="nim" 
                    type="text" 
                    class="mt-1 block w-full" 
                    v-model="form.nim" 
                    required 
                />
                <InputError class="mt-2" :message="form.errors.nim" />
            </div>

            <div class="mt-4" v-if="form.role === 'bph'">
                <InputLabel for="phone" value="Nomor WhatsApp" />
                <TextInput 
                    id="phone" 
                    type="text" 
                    class="mt-1 block w-full" 
                    v-model="form.phone" 
                    required 
                />
                <InputError class="mt-2" :message="form.errors.phone" />

                <InputLabel for="jabatan" value="Jabatan (Misal: Ketua Umum)" class="mt-4" />
                <TextInput 
                    id="jabatan" 
                    type="text" 
                    class="mt-1 block w-full" 
                    v-model="form.jabatan" 
                    required 
                />
                <InputError class="mt-2" :message="form.errors.jabatan" />
                
                <p class="text-xs text-red-500 mt-2">*Akun BPH akan berstatus Pending dan harus disetujui oleh Admin Universitas.</p>
            </div>

            <div class="mt-4">
                <InputLabel for="organization_id" value="Pilih Asal Unit / UKM" />
                <select 
                    id="organization_id" 
                    v-model="form.organization_id" 
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    required
                >
                    <option value="" disabled>-- Pilih Unit --</option>
                    <option v-for="org in organizations" :key="org.id" :value="org.id">
                        {{ org.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.organization_id" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>