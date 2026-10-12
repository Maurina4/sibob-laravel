<section class="space-y-4">
    <p class="text-xs text-[#62756A] leading-relaxed max-w-xl">
        Setelah akun Anda dihapus, semua data dan hak akses akun akan dihapus secara permanen. Pastikan Anda telah mengonfirmasi dengan koordinator atau administrator BMN BBPOM di Banjarmasin sebelum melanjutkan.
    </p>

    <div class="pt-2">
        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#B9382E] hover:bg-[#9C2E25] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Hapus Akun Ini</span>
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-center gap-3 pb-3 border-b border-[#E3ECE6]">
                <div class="w-8 h-8 rounded-lg bg-[#FDF0EE] text-[#9C322C] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-[#132219]">
                    Konfirmasi Penghapusan Akun
                </h3>
            </div>

            <p class="mt-4 text-xs text-[#62756A] leading-relaxed">
                Apakah Anda yakin ingin menghapus akun ini secara permanen? Silakan masukkan kata sandi akun Anda untuk memverifikasi tindakan ini.
            </p>

            <div class="mt-4">
                <label for="delete_password" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                    Kata Sandi Konfirmasi
                </label>

                <input
                    id="delete_password"
                    name="password"
                    type="password"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                    placeholder="Masukkan kata sandi akun"
                />

                @if($errors->userDeletion->get('password'))
                    <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-[#E3ECE6]">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="px-4 py-2 rounded-lg border border-[#E3ECE6] hover:bg-[#F7FAF8] text-xs font-semibold text-[#132219] transition"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-[#B9382E] hover:bg-[#9C2E25] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
                >
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
