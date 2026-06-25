# Task: TASK-002 - User Management & Spatie RBAC Setup

## Description
Set up User management in Filament Backoffice, define standard Spatie Roles (Owner, Admin, Cashier, Sales, Technician) through a database seeder, and implement UI components in `UserResource` to assign branches and roles to users.

## Technical Details
- **Role**: Developer / Architect
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-001 - Multi-Cabang & User RBAC Setup
- **Status**: Merged/Completed

## Acceptance Criteria
- [x] Spatie Roles (Owner, Admin, Cashier, Sales, Technician) are defined in a database seeder.
- [x] Filament `UserResource` is created to perform CRUD operations on users.
- [x] Users can be assigned to multiple branches in the User form using a relation manager or select field.
- [x] Users can be assigned to Spatie Roles in the User form.

## Assignee
- Developer
