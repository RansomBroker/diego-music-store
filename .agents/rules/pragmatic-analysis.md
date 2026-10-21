---
trigger: always_on
description: "Panduan efisiensi analisis dan eksekusi yang proporsional dengan kompleksitas masalah"
---

# Pragmatic & Right-Sized Analysis

1. **Proporsionalitas Masalah (Right-Sized Depth):**
   - Bedakan masalah sederhana (misal: perbaikan UI, binding props, penyesuaian dropdown, pemanggilan API lokal) dengan arsitektur kompleks (misal: migrasi database besar, integrasi multi-servis).
   - Untuk fitur/perbaikan sederhana, langsung fokus pada target file dan perbaiki tanpa analisis berlapis-lapis atau over-investigasi yang memakan waktu.

2. **Hindari Over-Engineering & Over-Tracing:**
   - Jangan melakukan investigasi hingga ke ujung backend/DB/tinker jika masalahnya hanya ada di level frontend data parsing atau UI binding.
   - Analisis mendalam hanya dilakukan jika konteks memang samar, terjadi kegagalan sistemik/bug tersembunyi, atau secara eksplisit diminta oleh user.

3. **Cepat & Tanggap (Bias for Action):**
   - Jika jalur perbaikan sudah jelas, segera implementasikan perubahan dan berikan hasil/konfirmasi langsung kepada user.
