<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-[#132219]">Verifikasi Alamat Email</h2>
        <p class="mt-1.5 text-xs text-[#62756A] leading-relaxed">
            Terima kasih telah mendaftar. Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-lg bg-[#EAF5EE] border border-[#D5E5DA] text-xs font-medium text-[#1E5E38]">
            Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <x-primary-button class="w-full sm:w-auto justify-center">
                Kirim Ulang Tautan Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center sm:text-right">
            @csrf
            <button
                type="submit"
                class="text-xs text-[#62756A] hover:text-[#9C322C] font-medium transition"
            >
                Keluar dari Sistem
            </button>
        </form>
    </div>
</x-guest-layout>
