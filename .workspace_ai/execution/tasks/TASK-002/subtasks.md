# Subtasks: TASK-002 - User Management & Spatie RBAC Setup

## Checklist
- [ ] **Analysis & Design**:
  - [ ] Define the roles matrix (Owner, Admin, Cashier, Sales, Technician) and their basic permission requirements.
  - [ ] Plan UserResource form fields (Name, Username, Email, Password, Assigned Branches, and Spatie Roles).
- [ ] **Implementation**:
  - [ ] Create Spatie Roles seeder class `RoleAndPermissionSeeder.php`.
  - [ ] Run the database seeder to populate roles.
  - [ ] Edit `UserResource` form components to configure inputs and relationships for Roles and Branches.
  - [ ] Edit `UserResource` table columns to display User details, assigned branches, and roles.
- [ ] **Verification**:
  - [ ] Verify that new users can be created with usernames, assigned to specific branches, and given roles.
  - [ ] Test logging in with a newly created user using their username and password.
