# Penanggung Jawab Searchable Dropdown Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah field input dan filter Penanggung Jawab di seluruh form inventaris BMN dan filter bar menjadi searchable dropdown berbasis Alpine.js yang identik dengan Lokasi Ruangan.

**Architecture:** Menggunakan Alpine.js inline reactive data (`x-data`, `filtered` getter, search input, trigger button dengan chevron, floating panel) yang konsisten dengan implementasi searchable dropdown Lokasi Ruangan yang sudah ada di SIBOB, mempertahankan hidden input `name="penanggungjawab"` agar form submission dan validasi Laravel bekerja mulus tanpa perubahan controller.

**Tech Stack:** Laravel 11, Blade, Tailwind CSS, Alpine.js, PHPUnit/Pest.

**Spec:** `docs/superpowers/specs/2026-11-11-penanggungjawab-searchable-dropdown-design.md`

## Global Constraints
- Preserve documentation integrity and existing comments.
- Form `penanggungjawab` parameter format must remain compatible with existing backend validators and database storage (`"Nama (NIP)"`, `"Nama"`, or `"Pengelola BMN"`).
- Synchronization with `penanggungjawab_nip` readonly input must remain functional in `bmn/create` and `bmn/edit`.
- Filter bars on index views must auto-submit when an option is clicked or reset.

## Review Focus
- Input name fallback when pegawai does not have NIP.
- Retaining `old('penanggungjawab')` validation error recovery in form create and edit.
- Default preselection for "Pengelola BMN" in create views.
- Keyboard escape (`@keydown.escape="open = false"`) and outside-click dismiss (`@click.outside="open = false"`).
- Ensuring hidden input has `required` in create/edit forms.

---

### Task 1: Searchable Dropdown Penanggung Jawab pada BMN Umum (Create & Edit)

**Files:**
- Modify: `resources/views/bmn/create.blade.php`
- Modify: `resources/views/bmn/edit.blade.php`
- Test: `tests/Feature/BmnModuleTest.php`

**Interfaces:**
- Consumes: `$pegawais` collection from `BmnController::create` and `BmnController::edit`
- Produces: Hidden `<input id="penanggungjawab" name="penanggungjawab" :value="selected" required>` and auto-populates `#penanggungjawab_nip`

- [ ] **Step 1: Write test asserting searchable penanggungjawab elements in BMN create & edit views**
- [ ] **Step 2: Run test to observe failure**
- [ ] **Step 3: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn/create.blade.php`**
- [ ] **Step 4: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn/edit.blade.php`**
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit**

---

### Task 2: Searchable Dropdown Penanggung Jawab pada BMN TIK (Create & Edit)

**Files:**
- Modify: `resources/views/bmn-tik/create.blade.php`
- Modify: `resources/views/bmn-tik/edit.blade.php`
- Test: `tests/Feature/BmnTikModuleTest.php`

**Interfaces:**
- Consumes: `$pegawais` collection from `BmnTikController::create` and `BmnTikController::edit`
- Produces: Hidden `<input id="penanggungjawab" name="penanggungjawab" :value="selected" required>`

- [ ] **Step 1: Write test asserting searchable penanggungjawab elements in BMN TIK create & edit views**
- [ ] **Step 2: Run test to observe failure**
- [ ] **Step 3: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-tik/create.blade.php`**
- [ ] **Step 4: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-tik/edit.blade.php`**
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit**

---

### Task 3: Searchable Dropdown Penanggung Jawab pada BMN Laboratorium (Create & Edit)

**Files:**
- Modify: `resources/views/bmn-lab/create.blade.php`
- Modify: `resources/views/bmn-lab/edit.blade.php`
- Test: `tests/Feature/BmnLabModuleTest.php`

**Interfaces:**
- Consumes: `$pegawais` collection from `BmnLabController::create` and `BmnLabController::edit`
- Produces: Hidden `<input id="penanggungjawab" name="penanggungjawab" :value="selected" required>`

- [ ] **Step 1: Write test asserting searchable penanggungjawab elements in BMN Lab create & edit views**
- [ ] **Step 2: Run test to observe failure**
- [ ] **Step 3: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-lab/create.blade.php`**
- [ ] **Step 4: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-lab/edit.blade.php`**
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit**

---

### Task 4: Searchable Dropdown Penanggung Jawab pada BMN Alat Angkutan (Create & Edit)

**Files:**
- Modify: `resources/views/bmn-angkutan/create.blade.php`
- Modify: `resources/views/bmn-angkutan/edit.blade.php`
- Test: `tests/Feature/BmnAngkutanModuleTest.php`

**Interfaces:**
- Consumes: `$pegawais` collection from `BmnAngkutanController::create` and `BmnAngkutanController::edit`
- Produces: Hidden `<input id="penanggungjawab" name="penanggungjawab" :value="selected" required>`

- [ ] **Step 1: Write test asserting searchable penanggungjawab elements in BMN Angkutan create & edit views**
- [ ] **Step 2: Run test to observe failure**
- [ ] **Step 3: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-angkutan/create.blade.php`**
- [ ] **Step 4: Implement Alpine.js searchable dropdown for penanggungjawab in `resources/views/bmn-angkutan/edit.blade.php`**
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit**

---

### Task 5: Searchable Dropdown Filter Penanggung Jawab pada Filter Bar (BMN, TIK, Angkutan)

**Files:**
- Modify: `resources/views/bmn/partials/filter-bar.blade.php`
- Modify: `resources/views/bmn-tik/index.blade.php`
- Modify: `resources/views/bmn-angkutan/index.blade.php`
- Test: `tests/Feature/BmnModuleTest.php`

**Interfaces:**
- Consumes: `$penanggungjawabs` / `$penanggungjawabFilters` from controllers
- Produces: Hidden `<input type="hidden" name="penanggungjawab" :value="selected">` that triggers form submit on change

- [ ] **Step 1: Implement Alpine.js searchable dropdown filter in `resources/views/bmn/partials/filter-bar.blade.php`**
- [ ] **Step 2: Implement Alpine.js searchable dropdown filter in `resources/views/bmn-tik/index.blade.php`**
- [ ] **Step 3: Implement Alpine.js searchable dropdown filter in `resources/views/bmn-angkutan/index.blade.php`**
- [ ] **Step 4: Write test verifying searchable filter rendering**
- [ ] **Step 5: Commit**

---

### Task 6: Final Verification & Live Deployment Sync

**Files:**
- Test: Full test suite (`php artisan test`)
- Remote: `git push origin main`
- Live XAMPP: Pull and cache clear

- [ ] **Step 1: Run full test suite to ensure 0 failures/regressions**
- [ ] **Step 2: Push changes to remote git repository**
- [ ] **Step 3: Sync changes to live XAMPP deployment directory and clear caches**
