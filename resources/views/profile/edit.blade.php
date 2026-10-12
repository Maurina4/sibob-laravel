@extends('layouts.app')

@section('title', 'Profil Pengguna — SIBOB')
@section('page-title', 'Profil Pengguna')

@section('content')
<div class="space-y-6">

    {{-- HEADER CARD --}}
    <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#163828]"></span>
                <span class="text-xs font-semibold tracking-wider uppercase text-[#62756A]">Akun Aparatur</span>
            </div>
            <h2 class="mt-2 text-2xl lg:text-3xl font-bold tracking-tight text-[#132219]">
                Pengaturan Profil & Keamanan
            </h2>
            <p class="mt-1 text-sm text-[#62756A]">
                Kelola informasi akun kedinasan, kata sandi, dan kredensial akses sistem SIBOB.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EAF2ED] border border-[#D5E5DA] text-xs font-medium text-[#163828]">
                <span class="w-2 h-2 rounded-full bg-[#1E5E38]"></span>
                <span>Peran: <strong>{{ auth()->user()->role ?? 'Petugas' }}</strong></span>
            </span>
        </div>
    </div>

    {{-- BENTO GRID PROFILE FORMS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- BENTO 1: INFORMASI PROFIL --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 lg:p-8 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-[#E3ECE6]">
                    <div class="w-8 h-8 rounded-lg bg-[#EAF2ED] text-[#163828] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-[#132219]">Informasi Akun</h3>
                        <p class="text-xs text-[#62756A]">Perbarui nama lengkap dan alamat email kedinasan Anda</p>
                    </div>
                </div>

                <div class="mt-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        {{-- BENTO 2: UPDATE PASSWORD --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 lg:p-8 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-[#E3ECE6]">
                    <div class="w-8 h-8 rounded-lg bg-[#FCF5E8] text-[#8E5F13] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-[#132219]">Keamanan Kata Sandi</h3>
                        <p class="text-xs text-[#62756A]">Pastikan akun menggunakan kata sandi yang kuat dan aman</p>
                    </div>
                </div>

                <div class="mt-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

    </div>

    {{-- BENTO 3: HAPUS AKUN (ZONA BERBAHAYA) --}}
    <div class="bg-white border border-[#F8D4D0] rounded-xl p-6 lg:p-8 shadow-2xs">
        <div class="flex items-center gap-3 pb-4 border-b border-[#F8D4D0]">
            <div class="w-8 h-8 rounded-lg bg-[#FDF0EE] text-[#9C322C] flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-[#9C322C]">Penghapusan Akun</h3>
                <p class="text-xs text-[#62756A]">Tindakan permanen untuk menghapus akun dan seluruh preferensi</p>
            </div>
        </div>

        <div class="mt-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>
@endsection
