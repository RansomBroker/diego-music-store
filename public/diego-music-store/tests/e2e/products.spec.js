import { test, expect } from '@playwright/test';

test.describe('Backoffice Product CRUD E2E Test', () => {

  // Login terlebih dahulu sebelum setiap pengujian
  test.beforeEach(async ({ page }) => {
    await page.goto('/backoffice/login', { waitUntil: 'networkidle' });
    
    // Login menggunakan kredensial owner
    const usernameInput = page.locator('input[id="form.email"], input[id="data.email"], input[name="data.email"]').first();
    const passwordInput = page.locator('input[type="password"]').first();
    const submitButton = page.locator('button[type="submit"]').first();

    await usernameInput.fill('owner');
    await passwordInput.fill('password');
    await submitButton.click();

    // Tunggu hingga berhasil masuk ke backoffice
    await page.waitForURL(url => url.pathname.startsWith('/backoffice') && !url.pathname.includes('/login'), {
      timeout: 15000,
      waitUntil: 'networkidle',
    });
  });

  test('harus dapat melakukan alur CRUD produk secara lengkap (tanpa variasi)', async ({ page }) => {
    const productName = `Gitar E2E ${Date.now()}`;
    const updatedProductName = `${productName} (Updated)`;

    // 1. Navigasi ke Halaman Produk
    await page.goto('/backoffice/products', { waitUntil: 'networkidle' });
    await expect(page).toHaveURL(/\/backoffice\/products/);

    // ==========================================
    // 2. CREATE (Tambah Produk Baru Tanpa Variasi)
    // ==========================================
    // Klik tombol New Produk / Tambah di header
    const createButton = page.getByRole('button', { name: /new produk|tambah produk|buat/i }).first();
    await expect(createButton).toBeVisible();
    await createButton.click();

    // Tunggu Heading Modal "Create Produk" terlihat secara langsung
    const createHeading = page.getByRole('heading', { name: /create produk/i });
    await expect(createHeading).toBeVisible({ timeout: 10000 });

    // --- TAB 1: INFORMASI UMUM ---
    const modalDialog = page.getByRole('dialog');

    // 2.1 Isi Nama Produk
    const nameInput = modalDialog.getByRole('textbox', { name: /nama produk/i }).or(modalDialog.locator('input[name="data.name"]')).first();
    await expect(nameInput).toBeVisible();
    await nameInput.fill(productName);

    // 2.2 Pilih Tipe Produk: Barang Fisik ('physical') pada modal
    const typeSelect = modalDialog.getByRole('combobox', { name: /tipe produk/i }).first();
    await expect(typeSelect).toBeVisible();
    await typeSelect.selectOption({ label: 'Barang Fisik' });
    await page.waitForTimeout(500); // Tunggu responsivitas Livewire untuk memunculkan Tab Varian

    // 2.3 Pilih Satuan Produk (searchable select dropdown pada modal)
    const unitButton = modalDialog.getByRole('button', { name: /select an option|satuan/i }).first();
    await expect(unitButton).toBeVisible();
    await unitButton.click();
    await page.waitForTimeout(400);

    const unitOption = modalDialog.getByRole('listbox').getByRole('option').first();
    await expect(unitOption).toBeVisible({ timeout: 5000 });
    await unitOption.click();
    await page.waitForTimeout(300);

    // --- SWITCH TO TAB 2: VARIAN / SPESIFIKASI ---
    const variantTab = modalDialog.getByRole('tab', { name: /varian|spesifikasi/i });
    await expect(variantTab).toBeVisible({ timeout: 5000 });
    await variantTab.click();
    await page.waitForTimeout(400);

    // Isi Harga Jual Dasar & Harga Beli Dasar pada modal (spinbutton untuk input numeric)
    const priceInput = modalDialog.getByRole('spinbutton', { name: /harga jual/i }).or(modalDialog.locator('input[name="data.price"]')).first();
    await expect(priceInput).toBeVisible();
    await priceInput.fill('1500000');

    const costPriceInput = modalDialog.getByRole('spinbutton', { name: /harga beli/i }).or(modalDialog.locator('input[name="data.cost_price"]')).first();
    await expect(costPriceInput).toBeVisible();
    await costPriceInput.fill('1000000');

    // Submit Form dalam Modal Create
    const modalSubmitBtn = modalDialog.getByRole('button', { name: 'Create', exact: true });
    await expect(modalSubmitBtn).toBeVisible();
    await modalSubmitBtn.click();

    // Tunggu modal tertutup & notifikasi / tabel terupdate
    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    // ==========================================
    // 3. READ & SEARCH (Cari Produk di Tabel)
    // ==========================================
    const tableSearchInput = page.locator('input[wire\\:model*="tableSearch"]').or(page.getByRole('main').getByRole('searchbox', { name: 'Search', exact: true })).first();
    await expect(tableSearchInput).toBeVisible();
    await tableSearchInput.fill(productName);
    await page.waitForTimeout(1200);

    // Verifikasi produk muncul di tabel
    const tableRow = page.getByRole('row', { name: productName }).first();
    await expect(tableRow).toBeVisible({ timeout: 10000 });

    // ==========================================
    // 4. UPDATE (Edit Produk)
    // ==========================================
    // Klik tombol Edit pada baris produk tersebut
    const editButton = tableRow.getByRole('button', { name: 'Edit', exact: true }).first();
    await expect(editButton).toBeVisible();
    await editButton.click();

    // Tunggu Heading Modal Edit (Edit SKU-...) muncul
    const editHeading = page.getByRole('dialog').getByRole('heading', { name: /edit/i });
    await expect(editHeading).toBeVisible({ timeout: 10000 });

    const editDialog = page.getByRole('dialog');

    // Ubah Nama Produk pada Tab Informasi Umum
    const infoTab = editDialog.getByRole('tab', { name: /informasi umum/i }).first();
    if (await infoTab.isVisible()) {
      await infoTab.click();
      await page.waitForTimeout(300);
    }

    const editNameInput = editDialog.getByRole('textbox', { name: /nama produk/i }).or(editDialog.locator('input[name="data.name"]')).first();
    await editNameInput.fill(updatedProductName);

    // Simpan Perubahan
    const updateSubmitBtn = editDialog.getByRole('button', { name: /save|simpan|save changes/i }).first();
    await updateSubmitBtn.click();

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    // Verifikasi nama terbarui muncul di tabel
    if (await tableSearchInput.isVisible()) {
      await tableSearchInput.fill(updatedProductName);
      await page.waitForTimeout(1200);
    }

    const updatedRow = page.getByRole('row', { name: updatedProductName }).first();
    await expect(updatedRow).toBeVisible({ timeout: 10000 });

    // ==========================================
    // 5. DELETE (Hapus Produk)
    // ==========================================
    // Klik tombol Delete pada baris produk yang diperbarui
    const deleteButton = updatedRow.getByRole('button', { name: 'Delete', exact: true }).first();
    await expect(deleteButton).toBeVisible();
    await deleteButton.click();

    // Konfirmasi Hapus pada modal konfirmasi Filament 3
    const confirmBtn = page.getByRole('dialog').getByRole('button', { name: /confirm|delete|hapus|ya/i }).last();
    await expect(confirmBtn).toBeVisible({ timeout: 5000 });
    await confirmBtn.click();

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    // Verifikasi produk sudah terhapus dari tabel
    await expect(page.getByRole('table')).not.toContainText(updatedProductName);

    // Jeda 1 detik di akhir test untuk snapshot visual di Playwright UI
    await page.waitForTimeout(1000);
  });

  test('harus dapat melakukan alur CRUD produk dengan 1 variasi', async ({ page }) => {
    const productName = `Gitar Varian E2E ${Date.now()}`;
    const updatedProductName = `${productName} (Updated)`;

    // 1. Navigasi ke Halaman Produk
    await page.goto('/backoffice/products', { waitUntil: 'networkidle' });
    await expect(page).toHaveURL(/\/backoffice\/products/);

    // ==========================================
    // 2. CREATE (Tambah Produk Baru Dengan 1 Variasi)
    // ==========================================
    const createButton = page.getByRole('button', { name: /new produk|tambah produk|buat/i }).first();
    await expect(createButton).toBeVisible();
    await createButton.click();

    const createHeading = page.getByRole('heading', { name: /create produk/i });
    await expect(createHeading).toBeVisible({ timeout: 10000 });

    const modalDialog = page.getByRole('dialog');

    // 2.1 Tab 1: Informasi Umum
    const nameInput = modalDialog.getByRole('textbox', { name: /nama produk/i }).or(modalDialog.locator('input[name="data.name"]')).first();
    await expect(nameInput).toBeVisible();
    await nameInput.fill(productName);

    const typeSelect = modalDialog.getByRole('combobox', { name: /tipe produk/i }).first();
    await expect(typeSelect).toBeVisible();
    await typeSelect.selectOption({ label: 'Barang Fisik' });
    await page.waitForTimeout(500);

    const unitButton = modalDialog.getByRole('button', { name: /select an option|satuan/i }).first();
    await expect(unitButton).toBeVisible();
    await unitButton.click();
    await page.waitForTimeout(400);

    const unitOption = modalDialog.getByRole('listbox').getByRole('option').first();
    await expect(unitOption).toBeVisible({ timeout: 5000 });
    await unitOption.click();
    await page.waitForTimeout(300);

    // 2.2 Tab 2: Varian / Spesifikasi
    const variantTab = modalDialog.getByRole('tab', { name: /varian|spesifikasi/i });
    await expect(variantTab).toBeVisible({ timeout: 5000 });
    await variantTab.click();
    await page.waitForTimeout(400);

    // Toggle status "Produk ini memiliki beberapa varian"
    const hasVariantsToggle = modalDialog.getByRole('switch', { name: /memiliki beberapa varian/i }).or(modalDialog.locator('input[wire\\:model*="has_variants"]').or(modalDialog.getByRole('switch'))).first();
    await expect(hasVariantsToggle).toBeVisible();
    await hasVariantsToggle.click();
    await page.waitForTimeout(600);

    // Isi Varian 1 pertama yang sudah otomatis tersedia (Nama Varian, Harga Jual, Harga Beli)
    const variantNameInput = modalDialog.getByPlaceholder('Nama Varian').or(modalDialog.locator('input[name*="variants"][name*="name"]')).first();
    await expect(variantNameInput).toBeVisible({ timeout: 5000 });
    await variantNameInput.fill('Merah');

    const variantPriceInput = modalDialog.getByPlaceholder('Harga Jual').or(modalDialog.locator('input[name*="variants"][name*="price"]')).first();
    await expect(variantPriceInput).toBeVisible();
    await variantPriceInput.fill('1750000');

    const variantCostPriceInput = modalDialog.getByPlaceholder('Harga Beli').or(modalDialog.locator('input[name*="variants"][name*="cost_price"]')).first();
    await expect(variantCostPriceInput).toBeVisible();
    await variantCostPriceInput.fill('1200000');

    // Submit Form Create
    const modalSubmitBtn = modalDialog.getByRole('button', { name: 'Create', exact: true });
    await expect(modalSubmitBtn).toBeVisible();
    await modalSubmitBtn.click();

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    // ==========================================
    // 3. READ & SEARCH (Cari Produk Varian di Tabel)
    // ==========================================
    const tableSearchInput = page.locator('input[wire\\:model*="tableSearch"]').or(page.getByRole('main').getByRole('searchbox', { name: 'Search', exact: true })).first();
    await expect(tableSearchInput).toBeVisible();
    await tableSearchInput.fill(productName);
    await page.waitForTimeout(1200);

    const tableRow = page.getByRole('row', { name: new RegExp(productName) }).first();
    await expect(tableRow).toBeVisible({ timeout: 10000 });

    // ==========================================
    // 4. UPDATE (Edit Produk Varian)
    // ==========================================
    const editButton = tableRow.getByRole('button', { name: 'Edit', exact: true }).first();
    await expect(editButton).toBeVisible();
    await editButton.click();

    const editHeading = page.getByRole('dialog').getByRole('heading', { name: /edit/i });
    await expect(editHeading).toBeVisible({ timeout: 10000 });

    const editDialog = page.getByRole('dialog');

    const infoTab = editDialog.getByRole('tab', { name: /informasi umum/i }).first();
    if (await infoTab.isVisible()) {
      await infoTab.click();
      await page.waitForTimeout(300);
    }

    const editNameInput = editDialog.getByRole('textbox', { name: /nama produk/i }).or(editDialog.locator('input[name="data.name"]')).first();
    await editNameInput.fill(updatedProductName);

    const updateSubmitBtn = editDialog.getByRole('button', { name: /save|simpan|save changes/i }).first();
    await updateSubmitBtn.click();

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    if (await tableSearchInput.isVisible()) {
      await tableSearchInput.fill(updatedProductName);
      await page.waitForTimeout(1200);
    }

    const updatedRow = page.getByRole('row', { name: new RegExp(updatedProductName) }).first();
    await expect(updatedRow).toBeVisible({ timeout: 10000 });

    // ==========================================
    // 5. DELETE (Hapus Produk Varian)
    // ==========================================
    const deleteButton = updatedRow.getByRole('button', { name: 'Delete', exact: true }).first();
    await expect(deleteButton).toBeVisible();
    await deleteButton.click();

    const confirmBtn = page.getByRole('dialog').getByRole('button', { name: /confirm|delete|hapus|ya/i }).last();
    await expect(confirmBtn).toBeVisible({ timeout: 5000 });
    await confirmBtn.click();

    await page.waitForTimeout(2000);
    await page.waitForLoadState('networkidle');

    await expect(page.getByRole('table')).not.toContainText(updatedProductName);

    // Jeda 1 detik di akhir test untuk snapshot visual di Playwright UI
    await page.waitForTimeout(1000);
  });

});
