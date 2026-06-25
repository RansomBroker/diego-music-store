# Coding Standards

## 1. PHP/Laravel Coding Standards
- Follow **PSR-12** standards for code style.
- Enforce **Action-Based Architecture** (Action Pattern) for all core business logic (e.g., creating sales, adjusting stock, posting journal entries, calculating payroll):
  - Every action must reside under `app/Actions/{Domain}/` and represent a single, testable transaction/operation.
  - Actions must have a single public entry point, typically named `execute()`.
  - Actions can be injected using Laravel's container or resolved using `app(ActionName::class)->execute(...)`.
  - Code Example:
    ```php
    namespace App\Actions\Inventory;

    class DeductStockAction {
        public function execute(int $productId, int $branchId, int $qty): void {
            // Business validation and DB updates run here...
        }
    }
    ```
- Controllers, Livewire components, and Filament resources should only handle request validation, UI state, and delegation to Action classes.
- All Eloquent models must define relationships clearly with foreign keys.

## 2. Database & SQL Standards
- Table names must be lowercase and plural, utilizing snake_case.
- Every transaction modification (insert/update/delete) that affects accounting records must run inside a **SQL Transaction Block** to prevent partial writes.
- Enforce strict foreign key constraints at the database level.
- Indexes should be placed on columns frequently used in WHERE queries (e.g. `cabang_id`, `created_at`, `status`).

## 3. Frontend Standards
- Maintain semantic HTML tags.
- Styles should reside in clean Vanilla CSS blocks; avoid inline styling.
- All interactive components (buttons, links) must have unique, descriptive IDs to allow automated testing.
- Vanilla JavaScript must use ES6 conventions (let, const, arrow functions).
