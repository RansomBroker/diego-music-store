---
description: Diego Music Store Workflow
---

---
description: Diego Music Store Workflow
---

# Instruksi Awal: Workspace AI

selelau read folder ./workspace_ai

Selamat datang di Workspace AI. Sistem ini didesain agar pengembangan proyek berjalan secara terstruktur menggunakan file lokal sebagai **source of truth** (sumber kebenaran tunggal), sementara GitHub digunakan sebagai proyeksi eksternal untuk klien.

---

## 1. Cara Memberikan Perintah (Commands)

Anda dapat berinteraksi dengan Workspace AI cukup dengan menyebutkan (tag) nama file yang relevan dan memberikan instruksi kerja. 

### Contoh Perintah:
- **Analisis Fitur Baru**: 
  > "Tolong analisis fitur @prd.md untuk modul POS, buatkan rancangan fiturnya di folder `workspace/features/`."
- **Mulai Pengerjaan Task**: 
  > "Saya ingin mulai mengerjakan `execution/tasks/TASK-001`. Tolong update statusnya ke `Development`."
- **Review Hasil Pekerjaan**:
  > "Tolong review hasil coding untuk TASK-001 berdasarkan @review-template.md."

---

## 2. Struktur Folder & Peran

Setiap folder memiliki fungsi khusus dalam siklus pengembangan:

- **`workspace/`**: Berisi dokumen dasar proyek (`project.md`, `prd.md`, `roadmap.md`), folder `epics/`, `features/`, dan `decisions/` (arsitektur).
- **`execution/`**: Berisi `backlog/` tugas, folder `sprints/` aktif, tugas individual di `tasks/`, dan laporan kemajuan.
- **`roles/`**: Definisi alur kerja dari **Product Manager** (Analysis) $\rightarrow$ **Architect** (Ready) $\rightarrow$ **Developer** (Development) $\rightarrow$ **Reviewer** (Review) $\rightarrow$ **Documentation Writer** (Done).
- **`templates/`**: Template standar untuk memudahkan pembuatan PRD, Epic, Feature, Task, Review, dan Sprint baru.

---

## 3. Alur Kerja Task

Setiap tugas baru akan dibuat di bawah `execution/tasks/TASK-XXX/` dengan sub-file berikut:
1. **`task.md`**: Informasi detail, Acceptance Criteria, Role, dan Status.
2. **`subtasks.md`**: Checklist teknis pengerjaan.
3. **`review.md`**: Evaluasi dari Reviewer.
4. **`discussion.md`**: Catatan diskusi / feedback.
5. **`history.md`**: Riwayat perubahan status tugas.
6. **`outputs/`**: Folder hasil pengerjaan (jika berupa dokumen/file asset).

---

## 4. Eksekusi Perintah Terminal (Composer & Artisan)

Untuk menjalankan perintah Composer dan Laravel Artisan, gunakan script pembungkus Docker berikut agar permission file tetap sinkron dengan user host:
- **Artisan Command**: Jalankan `./docker-artisan.sh <perintah>` (Contoh: `./docker-artisan.sh migrate` atau `./docker-artisan.sh make:livewire`)
- **Composer Command**: Jalankan `./docker-composer.sh <perintah>` (Contoh: `./docker-composer.sh install` atau `./docker-composer.sh require livewire/livewire`)


## 5. Style dan kode patern
1. gunakan action pattern semua terkait logika bisnis buat di App\Action\{nama_feature}
2. kode resource harus clean jika ada satu kode yang terindikasi akan digunakan di tempat lain buat sebagai helper dan taruh di App\Helper
3. jika dimungkinkan 1 form resource maupuan penyajian datanya 1 file tidak lebih dari 1K line


## 6. Pengelolaan Software Requirements Specification (SRS)
Untuk menjaga agar spesifikasi sistem tetap rapi dan mudah dirujuk di masa mendatang:
1. **Source of Truth pada Level Feature**: Dokumen SRS diletakkan pada level **Feature** (`workspace/features/FEATURE-XXX.md` atau `workspace/features/FEATURE-XXX/srs.md` jika sangat kompleks). Hindari menulis detail SRS di level Task karena Task bersifat sementara (*short-lived*).
2. **Referensi pada Task**: File Task (`execution/tasks/TASK-XXX/task.md`) tidak perlu menulis ulang detail spesifikasi, melainkan cukup menautkan **referensi/link** ke Feature terkait dan berfokus pada *Acceptance Criteria* teknis spesifik untuk task tersebut.
3. **Template Standar**: Gunakan template standar di `.workspace_ai/templates/srs-template.md` sebagai acuan untuk mendefinisikan detail kebutuhan fitur.
