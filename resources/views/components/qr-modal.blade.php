{{-- MODAL QR CODE ASET BMN --}}
<div id="qr-asset-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- BACKDROP --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" onclick="closeQrModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#E3ECE6]">
            
            {{-- HEADER MODAL --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E3ECE6] bg-[#F7FAF8]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#EAF2ED] text-[#163828] flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                            <path d="M7 7h.01M17 7h.01M7 17h.01M14 14h3v3h-3zM20 14v3M14 20h3M20 20h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[#132219]" id="qr-modal-title">QR Code Aset BMN</h3>
                        <p class="text-[11px] text-[#62756A]">BBPOM di Banjarbaru</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="closeQrModal()"
                    class="text-[#8D9D95] hover:text-[#132219] hover:bg-[#EAF2ED] p-1.5 rounded-lg transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- BODY MODAL --}}
            <div class="p-6 space-y-4">
                {{-- KARTU LABEL CETAK / PREVIEW --}}
                <div id="qr-label-card" class="bg-[#F9FCFA] border-2 border-[#163828] rounded-xl p-4 text-left">
                    <div class="text-[10px] font-bold text-[#163828] uppercase tracking-wider">
                        Badan Pengawas Obat dan Makanan
                    </div>
                    <div class="text-[9px] text-[#62756A] mb-3">
                        BBPOM di Banjarbaru — Label Inventaris BMN
                    </div>

                    {{-- QR CODE RENDER TARGET --}}
                    <div class="flex justify-center my-3">
                        <div id="modal-qrcode-container" class="p-2 bg-white rounded-lg border border-[#D5E5DA] shadow-xs flex items-center justify-center min-h-[160px] min-w-[160px]"></div>
                    </div>

                    {{-- INFORMASI ASET --}}
                    <div class="space-y-1 mt-2">
                        <div>
                            <span id="qr-item-code" class="inline-block font-mono text-xs font-bold text-[#163828] bg-[#EAF2ED] px-2.5 py-0.5 rounded border border-[#D5E5DA] break-all">
                                -
                            </span>
                        </div>
                        <div id="qr-item-name" class="text-xs font-bold text-[#132219] break-words">
                            -
                        </div>
                        <div class="text-[11px] text-[#62756A] flex flex-wrap items-center gap-x-3 gap-y-1 pt-1.5 border-t border-dashed border-[#D5E5DA] mt-2">
                            <span>Lokasi: <strong id="qr-item-lokasi" class="text-[#132219] font-semibold">-</strong></span>
                            <span>Penanggung Jawab: <strong id="qr-item-pj" class="text-[#132219] font-semibold">-</strong></span>
                        </div>
                    </div>
                </div>

                {{-- INFO SCAN --}}
                <div class="text-[11px] text-[#62756A] text-center bg-[#F2F6F3] p-2 rounded-lg">
                    Arahkan kamera smartphone atau scanner untuk membaca detail inventaris aset ini.
                </div>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="px-6 py-3.5 bg-[#F7FAF8] border-t border-[#E3ECE6] flex items-center justify-between gap-2">
                <button
                    type="button"
                    onclick="downloadQrCode()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-[#D5E5DA] text-[#163828] hover:bg-[#EAF2ED] rounded-lg text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh PNG</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="closeQrModal()"
                        class="px-3 py-2 text-xs font-semibold text-[#62756A] hover:text-[#132219] rounded-lg transition cursor-pointer"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        onclick="printQrLabel()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Label</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
let qrInstance = null;
let currentQrData = null;

function openQrModal(data) {
    currentQrData = data;
    const modal = document.getElementById('qr-asset-modal');
    if (!modal) return;

    if (data.title) {
        document.getElementById('qr-modal-title').textContent = data.title;
    }
    document.getElementById('qr-item-code').textContent = data.code || '-';
    document.getElementById('qr-item-name').textContent = data.name || '-';
    document.getElementById('qr-item-lokasi').textContent = data.lokasi || '-';
    document.getElementById('qr-item-pj').textContent = data.pj || '-';

    const container = document.getElementById('modal-qrcode-container');
    container.innerHTML = '';

    const qrPayload = [
        'BBPOM BANJARBARU - INVENTARIS BMN',
        'Kode: ' + (data.code || '-'),
        'Nama: ' + (data.name || '-'),
        'Lokasi: ' + (data.lokasi || '-'),
        'Penanggung Jawab: ' + (data.pj || '-')
    ].join('\n');

    if (typeof QRCode !== 'undefined') {
        qrInstance = new QRCode(container, {
            text: qrPayload,
            width: 160,
            height: 160,
            colorDark: "#163828",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    } else {
        const img = document.createElement('img');
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(qrPayload);
        img.alt = 'QR Code';
        img.className = 'w-40 h-40';
        container.appendChild(img);
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeQrModal() {
    const modal = document.getElementById('qr-asset-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function downloadQrCode() {
    const code = (currentQrData && currentQrData.code) ? currentQrData.code.replace(/[^a-zA-Z0-9_-]/g, '_') : 'qrcode';
    const canvas = document.querySelector('#modal-qrcode-container canvas');
    if (canvas) {
        const link = document.createElement('a');
        link.download = 'QR_' + code + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        return;
    }
    const img = document.querySelector('#modal-qrcode-container img');
    if (img && img.src) {
        const link = document.createElement('a');
        link.download = 'QR_' + code + '.png';
        link.href = img.src;
        link.target = '_blank';
        link.click();
    }
}

function printQrLabel() {
    const printArea = document.getElementById('qr-label-card');
    if (!printArea) return;

    let qrImageTag = '';
    const canvas = document.querySelector('#modal-qrcode-container canvas');
    const img = document.querySelector('#modal-qrcode-container img');

    if (canvas) {
        try {
            const dataUrl = canvas.toDataURL('image/png');
            qrImageTag = `<img src="${dataUrl}" alt="QR Code" style="width: 140px; height: 140px; display: block; margin: 0 auto; object-fit: contain;" />`;
        } catch (e) {
            console.error('Gagal mengambil data QR canvas:', e);
        }
    }

    if (!qrImageTag && img && img.src) {
        qrImageTag = `<img src="${img.src}" alt="QR Code" style="width: 140px; height: 140px; display: block; margin: 0 auto; object-fit: contain;" />`;
    }

    if (!qrImageTag) {
        qrImageTag = document.getElementById('modal-qrcode-container').innerHTML;
    }

    const printWindow = window.open('', '_blank', 'width=520,height=560');
    if (!printWindow) {
        alert('Pop-up jendela cetak diblokir oleh browser. Harap izinkan pop-up untuk mencetak label.');
        return;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Cetak Label QR BMN - BBPOM di Banjarbaru</title>
            <style>
                @page { size: auto; margin: 5mm; }
                * { box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 10px;
                    background: #fff;
                }
                .label-box {
                    border: 2px solid #163828;
                    border-radius: 8px;
                    padding: 14px 16px;
                    width: 320px;
                    max-width: 100%;
                    text-align: left;
                    background: #fff;
                }
                .header-instansi {
                    font-size: 10px;
                    font-weight: 800;
                    color: #163828;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    line-height: 1.2;
                }
                .header-sub {
                    font-size: 8.5px;
                    color: #555;
                    margin-top: 2px;
                    margin-bottom: 8px;
                    line-height: 1.2;
                }
                .qr-wrapper {
                    margin: 6px auto 10px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .qr-wrapper img, .qr-wrapper canvas {
                    width: 140px !important;
                    height: 140px !important;
                    display: block;
                    margin: 0 auto;
                }
                .code-badge {
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                    font-size: 11px;
                    font-weight: bold;
                    background: #eaf2ed;
                    color: #163828;
                    padding: 2px 7px;
                    border-radius: 4px;
                    display: inline-block;
                    border: 1px solid #c2dbcd;
                    margin-bottom: 4px;
                    word-break: break-all;
                }
                .item-name {
                    font-size: 11px;
                    font-weight: bold;
                    color: #111;
                    line-height: 1.3;
                    margin-bottom: 6px;
                    word-break: break-word;
                }
                .meta-row {
                    font-size: 9px;
                    color: #444;
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    gap: 4px 10px;
                    margin-top: 6px;
                    border-top: 1px dashed #ccc;
                    padding-top: 6px;
                    line-height: 1.3;
                }
                .meta-row span {
                    word-break: break-word;
                }
                @media print {
                    body {
                        min-height: auto;
                        padding: 0;
                    }
                    .label-box {
                        page-break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <div class="label-box">
                <div class="header-instansi">Badan Pengawas Obat dan Makanan</div>
                <div class="header-sub">BBPOM di Banjarbaru — Label Inventaris BMN</div>
                <div class="qr-wrapper">
                    ${qrImageTag}
                </div>
                <div>
                    <div class="code-badge">${document.getElementById('qr-item-code').textContent}</div>
                    <div class="item-name">${document.getElementById('qr-item-name').textContent}</div>
                    <div class="meta-row">
                        <span>Lokasi: <strong>${document.getElementById('qr-item-lokasi').textContent}</strong></span>
                        <span>Penanggung Jawab: <strong>${document.getElementById('qr-item-pj').textContent}</strong></span>
                    </div>
                </div>
            </div>
            <script>
                function doPrint() {
                    window.focus();
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                }
                if (document.readyState === 'complete') {
                    setTimeout(doPrint, 250);
                } else {
                    window.addEventListener('load', function() {
                        setTimeout(doPrint, 250);
                    });
                }
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        try {
            printWindow.focus();
        } catch (e) {}
    }, 200);
}
</script>
