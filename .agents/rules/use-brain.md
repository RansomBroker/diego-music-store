---
trigger: always_on
description: Enforce checking and maintaining the Project Registry (Brain)
---

# Use Brain (Project Registry)

Before starting any task and after completing any architectural change, follow these steps:

1. **READ FIRST**: Read `docs/project-registry.md` to see existing Action domains, models, and architecture before writing new code. Avoid creating duplicate logic.
2. **RESPECT PATTERNS**: Diego Music Store uses the **Action Pattern** (`app/Actions/<Domain>/`). Do not introduce unnecessary Repositories or bloated Services.
3. **KEEP IT CONCISE**: When updating the registry, use a single-line responsibility format (e.g., `- DomainName/: Deskripsi singkat tanggung jawab`). Do NOT list individual methods.
4. **USE JIT FOR SCHEMA**: Do not document table columns or relationships in the registry. Inspect them via:
   `docker exec -w /var/www/public/diego-music-store lemp-app-diego-music-store php artisan model:show <ModelName>`