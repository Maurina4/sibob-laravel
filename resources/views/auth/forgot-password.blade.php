<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#132219]">Lupa Kata Sandi?</h2>
        <p class="mt-1.5 text-xs text-[#62756A] leading-relaxed">
            Masukkan alamat email kedinasan Anda yang terdaftar. Kami akan mengirimkan tautan untuk menyetel ulang kata sandi akun Anda.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email Kedinasan" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                placeholder="nama@pom.go.id"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="pt-2 flex items-center justify-between">
            <a
                class="text-xs text-[#62756A] hover:text-[#163828] font-medium transition"
                href="{{ route('login') }}"
            >
                &larr; Kembali ke login
            </a>

            <x-primary-button>
                Kirim Tautan Reset
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
