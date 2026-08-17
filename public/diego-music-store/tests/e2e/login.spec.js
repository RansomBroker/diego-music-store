import { test, expect } from '@playwright/test';

test.describe('Backoffice Login E2E Test', () => {
  test('harus berhasil login ke backoffice dengan username owner dan password yang benar', async ({ page }) => {
    // 1. Buka halaman login backoffice
    await page.goto('/backoffice/login', { waitUntil: 'networkidle' });

    // Pastikan halaman login terbuka
    await expect(page).toHaveURL(/\/backoffice\/login/);

    // 2. Isi form login (Username/Email & Password)
    const usernameInput = page.locator('input[id="form.email"], input[id="data.email"], input[name="data.email"]').first();
    const passwordInput = page.locator('input[type="password"]').first();
    const submitButton = page.locator('button[type="submit"]').first();

    await expect(usernameInput).toBeVisible();
    await usernameInput.fill('owner');

    await expect(passwordInput).toBeVisible();
    await passwordInput.fill('password');

    // 3. Klik tombol submit untuk login
    await submitButton.click();

    // 4. Verifikasi bahwa URL berhasil dialihkan ke dashboard backoffice
    await page.waitForURL(url => url.pathname.startsWith('/backoffice') && !url.pathname.includes('/login'), {
      timeout: 15000,
      waitUntil: 'networkidle',
    });

    // Pastikan URL saat ini adalah /backoffice
    expect(page.url()).toContain('/backoffice');

    // 5. Verifikasi bahwa elemen utama Dashboard (seperti sidebar Filament / User menu) tampil
    const sidebar = page.locator('.fi-sidebar').or(page.locator('nav'));
    await expect(sidebar.first()).toBeVisible({ timeout: 10000 });

    // Beri jeda 1 detik agar snapshot & tampilan web di Playwright UI terekam dengan sempurna
    await page.waitForTimeout(1000);
  });
});
