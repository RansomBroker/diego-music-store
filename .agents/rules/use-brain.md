---
trigger: always_on
description: Enforce checking and maintaining the Project Registry (Brain)
---

# Use Brain (Project Registry)

Before starting any task and after completing any architectural change, follow these steps:

1. **ROUTE BY KEYWORD FIRST**: Match the user's business terms/prompt using the CGC keyword router (`resolve_feature_context` MCP tool, or CLI `tools/codegraphcontext-laravel/.venv/bin/cgc feature resolve "<prompt>" --repo /home/skylantern/Projects/diego-music-store-project/public/diego-music-store`) backed by `docs/cgc-features.yaml` before running broad `grep` scans:
   - Use `expand_graph: true` (default) for business logic, bug tracing, impact analysis, cross-layer features, or refactors.
   - For simple UI tweaks where the user already gives an explicit route/file/component (e.g. hiding a button or adjusting a label), use `expand_graph: false` (manifest lookup only) or go directly to the target Filament/Livewire file.
2. **READ ARCHITECTURE REGISTRY**: Read `docs/project-registry.md` to see existing Action domains and patterns before writing new code. Avoid duplicate logic.
3. **RESPECT PATTERNS**: Diego Music Store uses the **Action Pattern** (`app/Actions/<Domain>/`). Do not introduce unnecessary Repositories or bloated Services.
4. **KEEP REGISTRIES UPDATED & CONCISE**: When adding a new feature or business term, add/update its entry and keywords in `docs/cgc-features.yaml` and keep `docs/project-registry.md` as a single-line domain summary (`- DomainName/: Deskripsi singkat`). Do NOT list individual methods.
5. **USE JIT FOR SCHEMA**: Do not document table columns or relationships in registries. Inspect them via:
   `./docker-artisan.sh model:show <ModelName>`