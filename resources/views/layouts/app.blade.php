<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIBOB — Sistem Inventaris BMN BPOM')</title>

    {{-- Google Fonts: Plus Jakarta Sans & Instrument Serif --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- CSS + JS melalui Vite --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-[#F7FAF8] text-[#132219] antialiased selection:bg-[#EAF2ED] selection:text-[#163828]">
    <div class="app min-h-screen flex">

        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- AREA UTAMA --}}
        <main id="main-content-scroll" class="main flex-1 flex flex-col min-w-0 bg-[#F7FAF8] overflow-y-auto">

            {{-- HEADER --}}
            @include('partials.header')

            {{-- KONTEN HALAMAN --}}
            <div class="konten p-6 lg:p-8 max-w-7xl w-full mx-auto flex-1">

                {{-- PESAN BERHASIL --}}
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl border border-[#D5E5DA] bg-[#EAF5EE] text-[#1E5E38] text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#1E5E38]/10 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#1E5E38]/60 hover:text-[#1E5E38] text-lg leading-none">&times;</button>
                    </div>
                @endif

                {{-- PESAN ERROR --}}
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#9C322C]/10 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#9C322C]/60 hover:text-[#9C322C] text-lg leading-none">&times;</button>
                    </div>
                @endif

                {{-- ISI HALAMAN --}}
                @yield('content')
                {{ $slot ?? '' }}

            </div>

            {{-- FOOTER KEDINASAN MINIMALIS --}}
            <footer class="py-4 px-6 border-t border-[#E3ECE6] text-xs text-[#62756A] flex flex-col sm:flex-row items-center justify-between gap-2 bg-white/50">
                <span>&copy; {{ date('Y') }} <strong>SIBOB</strong> — Balai Besar Pengawas Obat dan Makanan di Banjarbaru.</span>
                <span class="text-[11px] font-mono tracking-tight text-[#62756A]/80">BMN Management System v2.0</span>
            </footer>

        </main>

    </div>

    {{-- JAVASCRIPT TAMBAHAN --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        (function() {
            const mainContainer = document.getElementById('main-content-scroll');
            if (!mainContainer) return;

            const scrollKey = 'scroll_' + window.location.pathname + window.location.search;
            const savedScroll = sessionStorage.getItem(scrollKey);
            if (savedScroll) {
                mainContainer.scrollTop = parseInt(savedScroll, 10);
            }

            window.addEventListener('beforeunload', function() {
                sessionStorage.setItem(scrollKey, mainContainer.scrollTop);
            });
        })();

        // Enforce strictly numbers on all numeric inputs
        (function() {
            function isNumericInput(el) {
                if (!el || el.tagName !== 'INPUT') return false;
                if (el.type === 'hidden' || el.type === 'radio' || el.type === 'checkbox') return false;

                if (el.dataset.numeric === 'true') return true;
                if (el.getAttribute('inputmode') === 'numeric') return true;
                if (el.type === 'number') return true;

                const name = (el.name || '').toLowerCase();
                const id = (el.id || '').toLowerCase();
                const numericNames = [
                    'kode_barang',
                    'nup',
                    'tahun_perolehan',
                    'nilai_perolehan',
                    'jumlah'
                ];

                return numericNames.some(function(n) {
                    return name === n || name.endsWith('[' + n + ']') || id === n;
                });
            }

            function cleanNumericValue(el) {
                if (!isNumericInput(el)) return;
                const oldVal = el.value;
                let newVal = oldVal;
                if (/[^0-9]/.test(newVal)) {
                    newVal = newVal.replace(/[^0-9]/g, '');
                }
                const name = (el.name || '').toLowerCase();
                const id = (el.id || '').toLowerCase();
                let maxLen = el.getAttribute('maxlength') ? parseInt(el.getAttribute('maxlength'), 10) : null;
                if (name === 'tahun_perolehan' || id === 'tahun_perolehan' || name === 'tahun' || id === 'tahun') {
                    if (!maxLen || maxLen > 4) {
                        maxLen = 4;
                        el.setAttribute('maxlength', '4');
                    }
                }
                if (maxLen && newVal.length > maxLen) {
                    newVal = newVal.slice(0, maxLen);
                }
                if (newVal !== oldVal) {
                    el.value = newVal;
                }
            }

            // 1. Block Non-Digit Keys on Keydown
            document.addEventListener('keydown', function(e) {
                const el = e.target;
                if (!isNumericInput(el)) return;

                const allowed = [
                    'Backspace', 'Delete', 'Tab', 'Enter', 'Escape',
                    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End'
                ];
                if (allowed.indexOf(e.key) !== -1) return;
                if (e.ctrlKey || e.metaKey) return;

                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                const name = (el.name || '').toLowerCase();
                const id = (el.id || '').toLowerCase();
                let maxLen = el.getAttribute('maxlength') ? parseInt(el.getAttribute('maxlength'), 10) : null;
                if (name === 'tahun_perolehan' || id === 'tahun_perolehan' || name === 'tahun' || id === 'tahun') {
                    if (!maxLen || maxLen > 4) {
                        maxLen = 4;
                        el.setAttribute('maxlength', '4');
                    }
                }
                if (maxLen) {
                    const selLen = (el.selectionEnd ?? 0) - (el.selectionStart ?? 0);
                    if (el.value.length >= maxLen && selLen === 0) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }
            }, true);

            // 2. Block Non-Digits on beforeinput (virtual keyboards/IME)
            document.addEventListener('beforeinput', function(e) {
                const el = e.target;
                if (!isNumericInput(el)) return;

                if (e.data && /[^0-9]/.test(e.data)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // 3. Paste Event Handler
            document.addEventListener('paste', function(e) {
                const el = e.target;
                if (!isNumericInput(el)) return;

                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text') || '';
                const cleanDigits = text.replace(/[^0-9]/g, '');
                if (!cleanDigits) return;

                const name = (el.name || '').toLowerCase();
                const id = (el.id || '').toLowerCase();
                let maxLen = el.getAttribute('maxlength') ? parseInt(el.getAttribute('maxlength'), 10) : null;
                if (name === 'tahun_perolehan' || id === 'tahun_perolehan' || name === 'tahun' || id === 'tahun') {
                    if (!maxLen || maxLen > 4) maxLen = 4;
                }

                try {
                    const start = el.selectionStart ?? el.value.length;
                    const end = el.selectionEnd ?? el.value.length;
                    const val = el.value || '';
                    let combined = val.substring(0, start) + cleanDigits + val.substring(end);
                    if (maxLen && combined.length > maxLen) {
                        combined = combined.slice(0, maxLen);
                    }
                    el.value = combined;
                    const newPos = Math.min(combined.length, start + cleanDigits.length);
                    if (el.setSelectionRange) {
                        el.setSelectionRange(newPos, newPos);
                    }
                } catch (err) {
                    let combined = (el.value || '') + cleanDigits;
                    if (maxLen && combined.length > maxLen) {
                        combined = combined.slice(0, maxLen);
                    }
                    el.value = combined;
                }

                el.dispatchEvent(new Event('input', { bubbles: true }));
            }, true);

            // 4. Input & Change Event (safeguard for drag-drop / autofill)
            document.addEventListener('input', function(e) {
                cleanNumericValue(e.target);
            }, true);

            document.addEventListener('change', function(e) {
                cleanNumericValue(e.target);
            }, true);
        })();

        // Enforce max date = today on all date inputs (tidak bisa menginput tanggal maju)
        (function() {
            function getTodayDateString() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function applyMaxDate(el) {
                if (!el || el.tagName !== 'INPUT' || el.type !== 'date') return;
                const today = getTodayDateString();
                el.max = today;
                if (el.value && el.value > today) {
                    el.value = today;
                }
            }

            function initDateInputs() {
                document.querySelectorAll('input[type="date"]').forEach(applyMaxDate);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDateInputs);
            } else {
                initDateInputs();
            }

            document.addEventListener('focusin', function(e) {
                if (e.target && e.target.type === 'date') {
                    applyMaxDate(e.target);
                }
            }, true);

            document.addEventListener('input', function(e) {
                if (e.target && e.target.type === 'date') {
                    applyMaxDate(e.target);
                }
            }, true);

            document.addEventListener('change', function(e) {
                if (e.target && e.target.type === 'date') {
                    applyMaxDate(e.target);
                }
            }, true);
        })();

        // Universal Smart Handler for all "Kembali" / "Batal" navigation buttons
        (function() {
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('a, button');
                if (!btn) return;

                // Check text or class
                const text = (btn.textContent || '').trim().toLowerCase();
                const isKembali = text.startsWith('kembali') ||
                                  text.includes('kembali ke') ||
                                  text === 'kembali' ||
                                  text === 'batal' ||
                                  text === 'batalkan' ||
                                  btn.classList.contains('btn-back');

                if (!isKembali) return;

                // Never intercept form submit, confirm dialogs, or custom JS toggles
                if (btn.type === 'submit') return;
                const onclickAttr = btn.getAttribute('onclick') || '';
                const alpineClick = btn.getAttribute('@click') || btn.getAttribute('x-on:click') || '';
                if (onclickAttr.includes('confirm') || onclickAttr.includes('submit()') || alpineClick.includes('Modal') || alpineClick.includes('open')) return;

                // If document.referrer exists and is on the same host and not the exact same page or login page
                if (document.referrer) {
                    try {
                        const refUrl = new URL(document.referrer);
                        const curUrl = new URL(window.location.href);

                        if (refUrl.host === curUrl.host &&
                            refUrl.pathname !== curUrl.pathname &&
                            !refUrl.pathname.includes('/login') &&
                            !refUrl.pathname.includes('/logout')) {
                            e.preventDefault();
                            window.history.back();
                            return;
                        }
                    } catch (err) {
                        // fallback to default link
                    }
                }
            }, false);
        })();
    </script>
    @stack('scripts')

</body>
</html>