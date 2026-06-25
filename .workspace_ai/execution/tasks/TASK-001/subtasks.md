# Subtasks: TASK-001 - Database Migration & Branch Table Setup

## Checklist
- [ ] **Analysis & Design**:
  - [x] Design database schema for `branches` table (id, name, address, phone, is_active).
  - [x] Design pivot table `branch_user` (user_id, branch_id) to map user branch assignments.
- [ ] **Implementation**:
  - [x] Create Laravel migration file for `branches` table.
  - [x] Create Laravel migration file for pivot `branch_user` table.
  - [x] Run `./docker-artisan.sh migrate` in container to apply new schema.
- [ ] **Verification**:
  - [ ] Inspect database using PHPMyAdmin or terminal to verify tables are created correctly.
