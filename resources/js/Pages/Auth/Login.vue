<script setup>
import { Head, Link } from '@inertiajs/vue3';

// We will pass the intended role to the backend when they click the button
const googleLoginUrl = (role) => route('auth.google', { role: role });
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex w-full font-sans">
        
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-purple-50 to-purple-100 relative items-center justify-center overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <div class="w-64 h-64 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl absolute top-10 left-10"></div>
                <div class="w-64 h-64 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl absolute bottom-10 right-10"></div>
            </div>
            
            <div class="z-10 text-center px-12">
                <h1 class="text-5xl font-extrabold text-purple-900 mb-6 leading-tight">
                    Be The Best <br/> of You at SCU
                </h1>
                <div class="mt-8 bg-white/50 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-xl inline-block">
                    <p class="text-purple-800 font-medium">SCUPresence System</p>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white p-8 sm:p-12">
            
            <div class="w-full max-w-md flex flex-col items-center">
                <div class="mb-8 flex items-center justify-center">
                    <div class="text-3xl font-bold text-purple-900 flex items-center gap-2">
                        <span class="bg-purple-900 text-white w-10 h-10 flex items-center justify-center rounded-lg">S</span>
                        SCUPresence
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Selamat Datang di Student Unika! 👋</h2>
                    <p class="text-sm text-gray-500">Silakan masuk menggunakan akun email student Anda</p>
                </div>

                <div class="w-full space-y-4">
                    
                    <a 
                        href="/auth/google?role=bph" 
                        class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-[#5B2163] hover:bg-[#4a1b50] text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <svg class="w-5 h-5 bg-white rounded-full p-0.5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Login Pengurus / Pelatih
                    </a>

                    <a 
                        href="/auth/google?role=anggota" 
                        class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-[#E15535] hover:bg-[#c94b2f] text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <svg class="w-5 h-5 bg-white rounded-full p-0.5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Login Anggota
                    </a>

                </div>
                
                <div class="mt-8 text-xs text-gray-400 text-center">
                    &copy; 2026 Universitas Katolik Soegijapranata. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</template>