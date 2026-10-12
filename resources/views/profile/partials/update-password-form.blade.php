<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                Kata Sandi Saat Ini
            </label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                autocomplete="current-password"
            />
            @if($errors->updatePassword->get('current_password'))
                <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                Kata Sandi Baru
            </label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                autocomplete="new-password"
            />
            @if($errors->updatePassword->get('password'))
                <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                Konfirmasi Kata Sandi Baru
            </label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                autocomplete="new-password"
            />
            @if($errors->updatePassword->get('password_confirmation'))
                <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Perbarui Kata Sandi</span>
            </button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-xs text-[#1E5E38] font-medium flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kata sandi berhasil diperbarui.</span>
                </div>
            @endif
        </div>
    </form>
</section>
