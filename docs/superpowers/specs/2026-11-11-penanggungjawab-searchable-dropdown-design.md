# Design Spec: Penanggung Jawab Searchable Dropdown di Seluruh Form & Filter BMN

## Intent and The Why
Saat ini, field Lokasi Ruangan di seluruh sistem SIBOB sudah menggunakan komponen *Searchable Dropdown* berbasis Alpine.js yang memungkinkan pengguna mencari kode dan nama ruangan dengan cepat. Namun, field **Penanggung Jawab** di form inventaris BMN dan filter bar masih menggunakan tag `<select>` HTML standar, sehingga pengguna kesulitan mencari pegawai ketika daftar pegawai sangat panjang. Pengguna menginginkan seluruh field Penanggung Jawab dapat dicari (*searchable dropdown*) persis seperti Lokasi Ruangan.

## Scope & Files
1. **Form Input (Create & Edit)**:
   - `resources/views/bmn/create.blade.php`
   - `resources/views/bmn/edit.blade.php`
   - `resources/views/bmn-tik/create.blade.php`
   - `resources/views/bmn-tik/edit.blade.php`
   - `resources/views/bmn-lab/create.blade.php`
   - `resources/views/bmn-lab/edit.blade.php`
   - `resources/views/bmn-angkutan/create.blade.php`
   - `resources/views/bmn-angkutan/edit.blade.php`
2. **Filter Bar Pencarian (Index)**:
   - `resources/views/bmn/partials/filter-bar.blade.php`
   - `resources/views/bmn-tik/index.blade.php`
   - `resources/views/bmn-angkutan/index.blade.php`

## Requirements
1. **Komponen Searchable Dropdown**:
   - Berbasis Alpine.js tanpa dependensi library eksternal baru.
   - Menyediakan input teks pencarian (`x-ref="pjSearchInput"`, `x-model="search"`).
   - Pencarian real-time menyaring data berdasarkan Nama Pegawai maupun NIP (case-insensitive).
   - Opsi default gudang `"Pengelola BMN"` dengan NIP `"-"` dan label `"Pengelola BMN (Default Penampung Gudang)"`.
   - Opsi pegawai dari `$pegawais`: format value `"Nama (NIP)"` atau `"Nama"` jika NIP kosong.
   - Menampilkan badge NIP mono, icon user/pj, indikator centang untuk item terpilih, dan tombol hapus/reset.
   - Hidden input `name="penanggungjawab"` untuk memastikan nilai terkirim saat form disubmit (dengan attribute `required` pada form create/edit).
2. **Sinkronisasi NIP**:
   - Pada `bmn/create.blade.php` dan `bmn/edit.blade.php`, saat penanggung jawab dipilih, input `penanggungjawab_nip` otomatis terisi NIP yang bersangkutan (atau `"-"` untuk Pengelola BMN).
3. **Filter Bar Auto-submit**:
   - Pada filter bar index, saat opsi dipilih atau di-reset/clear, form filter langsung disubmit (`this.$refs.hiddenPjFilter.closest('form').submit()`), sama persis seperti filter lokasi.
