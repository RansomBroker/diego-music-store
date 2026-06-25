# Coding Standards

## 1. PHP/Laravel Coding Standards
- Follow **PSR-12** standards for code style.
- Use explicit dependency injection inside Controllers and Service constructors.
- Maintain separate **Service Layers** for complex business logic (e.g. accounting journal postings, commissions, API integrations).
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
