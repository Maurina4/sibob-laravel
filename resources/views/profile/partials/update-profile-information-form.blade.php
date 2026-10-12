<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                Nama Lengkap
            </label>
            <input
                id="name"
                name="name"
                type="text"
                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
            />
            @if($errors->get('name'))
                <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->first('name') }}</p>
            @endif
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                Alamat Email Kedinasan
            </label>
            <input
                id="email"
                name="email"
                type="email"
                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
            />
            @if($errors->get('email'))
                <p class="text-xs text-[#9C322C] mt-1 font-medium">{{ $errors->first('email') }}</p>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 rounded-lg bg-[#FCF5E8] border border-[#F3E2C4] text-xs text-[#8E5F13]">
                    <p>
                        Alamat email belum diverifikasi.
                        <button form="send-verification" class="underline font-semibold hover:text-[#6A470E] focus:outline-none">
                            Klik di sini untuk mengirim ulang tautan verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-[#1E5E38]">
                            Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Perubahan</span>
            </button>

            @if (session('status') === 'profile-updated')
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
                    <span>Profil berhasil diperbarui.</span>
                </div>
            @endif
        </div>
    </form>
</section>
