<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#132219]">Daftar Akun Aparatur</h2>
        <p class="mt-1 text-xs text-[#62756A]">
            Registrasi akun pegawai untuk sistem inventaris BMN BBPOM di Banjarbaru.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- NAMA LENGKAP --}}
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="cth. Ahmad Fauzi, S.Farm."
            />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        {{-- EMAIL --}}
        <div>
            <x-input-label for="email" value="Alamat Email Kedinasan" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                placeholder="nama@pom.go.id"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        {{-- PASSWORD --}}
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        {{-- PASSWORD CONFIRMATION --}}
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2 flex items-center justify-between">
            <a
                class="text-xs text-[#62756A] hover:text-[#163828] font-medium transition"
                href="{{ route('login') }}"
            >
                Sudah memiliki akun? Masuk
            </a>

            <x-primary-button>
                Daftar Sekarang
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
