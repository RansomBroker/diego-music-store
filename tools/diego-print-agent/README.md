# Diego Print Agent

Local HTTP print bridge (`127.0.0.1:18920`) for direct thermal receipt & barcode printing from the Diego Music Store POS web app.

## Menjalankan di Windows

### Opsi 1: Menggunakan `.exe` (Tanpa perlu install Python)
1. Unduh `diego-print-agent.exe` atau `diego-print-agent-windows.zip` dari GitHub Actions (**Artifacts**) atau **GitHub Releases**.
2. Letakkan di satu folder pada komputer kasir (contoh: `C:\DiegoPrintAgent\`).
3. Jalankan `diego-print-agent.exe` langsung (atau jalankan `run_agent.bat` — script otomatis mendeteksi `diego-print-agent.exe` jika ada).

### Opsi 2: Menggunakan Python langsung
```bat
pip install pywin32
python agent.py
```

### Opsi CLI
- `--host 127.0.0.1` (default `127.0.0.1`; gunakan `0.0.0.0` jika ingin berbagi printer di LAN)
- `--port 18920` (default `18920`)
- `--ssl --cert cert.pem --key key.pem` (opsional untuk HTTPS)

## Compile `.exe`

### Otomatis via GitHub Actions (Direkomendasikan)
Workflow [`.github/workflows/build-print-agent.yml`](../../.github/workflows/build-print-agent.yml) membangun `diego-print-agent.exe` pada runner `windows-latest`:
- Otomatis berjalan saat ada push ke `main` yang mengubah `tools/diego-print-agent/**`.
- Otomatis membuat GitHub Release saat push tag `print-agent-v*` (contoh: `print-agent-v1.0.0`).
- Bisa dijalankan manual dari tab **Actions -> Build Diego Print Agent (.exe) -> Run workflow** (centang *Publish a GitHub Release* jika ingin sekaligus merilis file ke halaman Releases).

Hasil build tersedia sebagai:
- `diego-print-agent.exe` (single standalone executable)
- `diego-print-agent-windows.zip` (berisi `.exe`, `run_agent.bat`, dan `README.md`)

### Manual di Windows
Pastikan Python 3.10+ sudah terpasang, lalu jalankan:
```bat
build_exe.bat
```
Output berada di `tools\diego-print-agent\dist\diego-print-agent.exe`.

