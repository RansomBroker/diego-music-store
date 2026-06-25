# Task: TASK-001 - Database Migration & Branch Table Setup

## Description
Create the initial database schema, including the `branches` table, Laravel migration for core relationship tables (`users`, `branches`, `branch_user`), and data isolation design using Eloquent global scopes.

## Technical Details
- **Role**: Architect / Developer
- **Epic**: EPIC-001 - Master Data Setup & Basic Config
- **Feature**: FEATURE-001 - Multi-Cabang & User RBAC Setup
- **Status**: Done

## Acceptance Criteria
- [x] Database migration runs successfully without errors in the Docker container.
- [x] Many-to-Many relationship between `users` and `branches` is established via the `branch_user` pivot table.
- [x] Database schema natively supports multi-tenant branch isolation.

## Assignee
- Architect
