<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#132219]">Konfirmasi Akses Keamanan</h2>
        <p class="mt-1.5 text-xs text-[#62756A] leading-relaxed">
            Ini adalah area sistem yang terlindungi. Silakan konfirmasi kata sandi Anda sebelum melanjutkan tindakan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi Akun" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi Anda"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="pt-2 flex justify-end">
            <x-primary-button class="w-full justify-center">
                Konfirmasi Kata Sandi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
