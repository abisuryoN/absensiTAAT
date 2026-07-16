# Custom Dropdown Guide

Panduan lengkap untuk menggunakan custom dropdown di aplikasi Absensi Sekolah.

## Fitur Utama

- 🎨 Desain modern dan konsisten
- 🔍 Opsi pencarian (searchable)
- ⌨️ Keyboard navigation support
- 📱 Responsive design
- ♿ Accessibility compliant
- 🎯 Easy to implement

## Instalasi

Custom dropdown sudah terintegrasi dengan sistem. Pastikan file-file berikut ada:
- `resources/sass/_custom-dropdown.scss`
- `resources/js/custom-dropdown.js`
- Sudah di-import di `resources/sass/app.scss`
- Sudah di-include di `vite.config.js`

## Cara Penggunaan

### 1. Basic Dropdown

```html
<div class="custom-select-wrapper" data-placeholder="Pilih Opsi">
    <select name="field_name" id="field_id" required>
        <option value="">Pilih Opsi</option>
        <option value="option1">Option 1</option>
        <option value="option2">Option 2</option>
        <option value="option3">Option 3</option>
    </select>
</div>
```

### 2. Searchable Dropdown

Tambahkan attribute `data-searchable` untuk fitur pencarian:

```html
<div class="custom-select-wrapper" data-searchable data-placeholder="Cari dan Pilih">
    <select name="field_name" id="field_id">
        <option value="">Pilih Opsi</option>
        <option value="jakarta">Jakarta</option>
        <option value="bandung">Bandung</option>
        <option value="surabaya">Surabaya</option>
        <!-- ... many more options ... -->
    </select>
</div>
```

### 3. Size Variants

Tersedia 3 ukuran: small, default, dan large:

```html
<!-- Small -->
<div class="custom-select-wrapper small">
    <select>...</select>
</div>

<!-- Default (no additional class needed) -->
<div class="custom-select-wrapper">
    <select>...</select>
</div>

<!-- Large -->
<div class="custom-select-wrapper large">
    <select>...</select>
</div>
```

### 4. State Variants

```html
<!-- Error State -->
<div class="custom-select-wrapper error">
    <select>...</select>
</div>

<!-- Success State -->
<div class="custom-select-wrapper success">
    <select>...</select>
</div>
```

### 5. Disabled Dropdown

```html
<div class="custom-select-wrapper">
    <select disabled>
        <option>Cannot select</option>
    </select>
</div>
```

### 6. Custom Date Picker Styling

Untuk input date, gunakan wrapper ini:

```html
<div class="custom-datepicker-wrapper">
    <input type="date" class="form-control" name="date" required>
</div>
```

## Manual Initialization

Jika menambahkan dropdown secara dinamis via JavaScript:

```javascript
// Initialize single dropdown
const wrapper = document.querySelector('.custom-select-wrapper');
const dropdown = new CustomDropdown(wrapper, {
    searchable: true,
    placeholder: 'Custom Placeholder'
});

// Re-initialize all dropdowns
initCustomDropdowns();
```

## JavaScript API

### Methods

```javascript
const dropdown = new CustomDropdown(element, options);

// Open dropdown
dropdown.open();

// Close dropdown
dropdown.close();

// Toggle dropdown
dropdown.toggle();

// Set value programmatically
dropdown.setValue('option1');

// Destroy dropdown (revert to original select)
dropdown.destroy();
```

### Options

```javascript
{
    searchable: false,      // Enable search functionality
    placeholder: 'Pilih'    // Default placeholder text
}
```

### Events

Custom dropdown memicu event 'change' pada elemen select asli:

```javascript
document.getElementById('mySelect').addEventListener('change', function(e) {
    console.log('Selected value:', e.target.value);
});
```

## Migrasi dari Select Biasa

### Before (Native Select)
```html
<select class="form-select" name="type">
    <option value="">Pilih Tipe</option>
    <option value="1">Type 1</option>
    <option value="2">Type 2</option>
</select>
```

### After (Custom Dropdown)
```html
<div class="custom-select-wrapper" data-placeholder="Pilih Tipe">
    <select class="form-select" name="type">
        <option value="">Pilih Tipe</option>
        <option value="1">Type 1</option>
        <option value="2">Type 2</option>
    </select>
</div>
```

**Catatan:** Select asli tetap ada dan berfungsi normal untuk form submission!

## Styling Customization

Untuk menyesuaikan warna atau style, edit file `resources/sass/_custom-dropdown.scss`.

### Primary Color
Ubah `#6366f1` (indigo) ke warna brand Anda.

### Border Radius
Default: `0.5rem`. Sesuaikan di `.custom-select__trigger` dan `.custom-select__dropdown`.

### Font Size
Default: `0.875rem` (14px). Ubah sesuai kebutuhan.

## Best Practices

1. ✅ Selalu gunakan `data-placeholder` untuk dropdown yang memiliki opsi kosong
2. ✅ Gunakan `data-searchable` untuk dropdown dengan banyak opsi (>10)
3. ✅ Tetap sertakan `<option value="">` untuk placeholder
4. ✅ Gunakan `required` attribute bila field wajib diisi
5. ✅ Test keyboard navigation (Tab, Enter, Escape, Arrow keys)

## Browser Support

- ✅ Chrome/Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Mobile browsers

## Troubleshooting

### Dropdown tidak muncul
- Pastikan `custom-dropdown.js` ter-load di halaman
- Check console untuk error JavaScript
- Pastikan struktur HTML benar (wrapper > select)

### Styling tidak sesuai
- Run `npm run build` untuk compile SCSS
- Clear browser cache
- Pastikan `_custom-dropdown.scss` ter-import di `app.scss`

### Dropdown tidak menutup
- Pastikan tidak ada JavaScript error
- Check z-index conflicts dengan elemen lain

### Value tidak ter-submit
- Pastikan element `<select>` tetap ada di dalam wrapper
- Custom dropdown tidak menghapus select asli, hanya hide
- Check name attribute pada select

## Examples

Lihat implementasi di:
- `resources/views/admin/holidays/index.blade.php` - Form hari libur

## Support

Untuk pertanyaan atau issue, silakan buat ticket di repository project.