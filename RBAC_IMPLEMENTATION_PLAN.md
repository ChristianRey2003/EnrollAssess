# RBAC Implementation Plan: Superadmin Enhancement

## Overview
Transform the existing `administrator` role into a true **superadmin** that oversees everything, assigns roles, and delegates capabilities, while maintaining the current department-head operational capabilities.

---

## Current State Analysis

### ✅ What's Already Working
- `administrator` role exists and has `isAdministrator()` helper
- `hasPermission()` treats `administrator` as super-role (returns true for all permissions)
- Delegation system (`RoleDelegation`) is functional
- Role middleware (`RoleMiddleware`) and capability middleware (`CheckDelegatedPermission`) are working

### ❌ What Needs to Change

1. **User Management Routes** (routes/admin.php:193)
   - Currently: `role:department-head` only
   - Should be: `role:department-head,administrator`

2. **Role Assignment Restrictions** (UserManagementController)
   - Currently: Both department-head and administrator can change any role
   - Should be: Only `administrator` can change department-head roles
   - Department-head can only manage instructors (create/edit, but not change roles)

3. **Delegation Authority** (UserManagementController@delegate)
   - Currently: Only allows delegation to `instructor` role
   - Should be: 
     - `administrator` can delegate to both `department-head` and `instructor`
     - `department-head` can delegate to `instructor` only

4. **User Creation Restrictions** (UserManagementController@store)
   - Currently: Can create `department-head` or `instructor`
   - Should be:
     - `administrator` can create any role (including other administrators, but with safeguards)
     - `department-head` can only create `instructor` role

5. **View Restrictions** (resources/views/admin/users/)
   - Need to hide/show role change UI based on current user's role
   - Department-head should not see option to change roles to `department-head` or `administrator`

---

## Implementation Tasks

### Phase 1: Route Access Updates

#### Task 1.1: Update User Management Routes
**File**: `routes/admin.php`
- Change line 193 from `role:department-head` to `role:department-head,administrator`
- This allows administrator to access all user management endpoints

**Impact**: Administrator can now access `/admin/users/*` routes

---

### Phase 2: Controller Authorization Logic

#### Task 2.1: Add Authorization Helper Method
**File**: `app/Http/Controllers/UserManagementController.php`
- Add private method `canChangeRole($targetUser, $newRole)` that:
  - Returns `true` if current user is `administrator`
  - Returns `true` if current user is `department-head` AND target role is `instructor` AND target user is currently `instructor`
  - Returns `false` otherwise

#### Task 2.2: Restrict Role Changes in `store()` Method
**File**: `app/Http/Controllers/UserManagementController.php` (line ~96)
- Add check: If creating `department-head` role, require `administrator` role
- Add check: If `department-head` is creating user, only allow `instructor` role
- Update validation to reflect these restrictions

#### Task 2.3: Restrict Role Changes in `update()` Method
**File**: `app/Http/Controllers/UserManagementController.php` (line ~198)
- Add authorization check before allowing role change:
  - If changing TO `department-head` or `administrator`: require `administrator` role
  - If changing FROM `department-head` or `administrator`: require `administrator` role
  - If `department-head` is updating: only allow changing `instructor` users, and only to `instructor` role
- Add validation error if unauthorized role change attempted

#### Task 2.4: Restrict User Deletion
**File**: `app/Http/Controllers/UserManagementController.php` (line ~246)
- Add check: Only `administrator` can delete `department-head` or `administrator` users
- `department-head` can only delete `instructor` users

#### Task 2.5: Update Delegation Logic
**File**: `app/Http/Controllers/UserManagementController.php` (line ~396)
- Update `delegate()` method:
  - If current user is `administrator`: allow delegation to `department-head` OR `instructor`
  - If current user is `department-head`: allow delegation to `instructor` only (current behavior)
  - Update validation message accordingly

---

### Phase 3: View Updates

#### Task 3.1: Update User Create Form
**File**: `resources/views/admin/users/create.blade.php`
- Add conditional logic:
  - If current user is `administrator`: show all role options (`administrator`, `department-head`, `instructor`)
  - If current user is `department-head`: show only `instructor` option
- Add JavaScript validation to prevent unauthorized role selection

#### Task 3.2: Update User Edit Form
**File**: `resources/views/admin/users/edit.blade.php`
- Add conditional logic for role dropdown:
  - If current user is `administrator`: show all roles, allow changes
  - If current user is `department-head`: 
    - If editing `instructor`: show `instructor` only (read-only or disabled)
    - If editing `department-head` or `administrator`: hide role field entirely (read-only display)
- Show warning message if department-head tries to edit higher-privilege user

#### Task 3.3: Update User Index/List View
**File**: `resources/views/admin/users/index.blade.php`
- Add conditional display for action buttons:
  - "Edit" button: Show for all users if `administrator`, only for `instructor` if `department-head`
  - "Delete" button: Same logic as Edit
  - "Delegate" button: Show for `instructor` if `department-head`, show for both `department-head` and `instructor` if `administrator`

---

### Phase 4: Model & Helper Updates

#### Task 4.1: Add Helper Methods to User Model
**File**: `app/Models/User.php`
- Add method `canManageUser(User $targetUser)`: Returns true if current user can manage target user
- Add method `canDelegateTo(User $targetUser)`: Returns true if current user can delegate to target user
- These methods centralize authorization logic

#### Task 4.2: Verify Permission Logic
**File**: `app/Models/User.php` (line ~199)
- Verify `hasPermission()` correctly treats `administrator` as super-role
- Ensure it returns `true` for all permissions (already correct, just verify)

---

### Phase 5: Testing & Validation

#### Task 5.1: Test Administrator Access
- Verify administrator can:
  - Access all user management routes
  - Create users with any role
  - Change any user's role
  - Delete any user
  - Delegate to both department-heads and instructors

#### Task 5.2: Test Department-Head Restrictions
- Verify department-head can:
  - Access user management routes
  - Create only instructor users
  - Edit only instructor users (cannot change their role)
  - Delete only instructor users
  - Delegate only to instructors
  - Cannot change department-head or administrator roles

#### Task 5.3: Test Edge Cases
- Administrator cannot delete themselves
- Department-head cannot edit themselves through user management
- Role changes are properly logged in ActivityLogger
- Delegation restrictions are enforced

---

## Implementation Order

1. **Phase 1** (Routes) - Quick win, enables access
2. **Phase 4** (Model Helpers) - Foundation for authorization checks
3. **Phase 2** (Controller Logic) - Core authorization enforcement
4. **Phase 3** (Views) - User experience improvements
5. **Phase 5** (Testing) - Validation

---

## Security Considerations

### Critical Checks
1. **Never allow role escalation**: Department-head cannot become administrator
2. **Prevent privilege escalation**: Department-head cannot change other department-heads
3. **Audit trail**: All role changes must be logged via ActivityLogger
4. **Self-protection**: Users cannot delete/disable themselves

### Future Scalability
- Current implementation assumes single department
- When multi-department support is added:
  - Add `department_id` to users table
  - Update `canManageUser()` to check department boundaries
  - Superadmin remains system-wide, department-heads are department-scoped

---

## Files to Modify

### Routes
- `routes/admin.php` (1 change)

### Controllers
- `app/Http/Controllers/UserManagementController.php` (multiple methods)

### Models
- `app/Models/User.php` (add helper methods)

### Views
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/show.blade.php` (if role display needs updates)

---

## Success Criteria

✅ Administrator can access all user management features
✅ Administrator can create users with any role
✅ Administrator can change any user's role (including department-head)
✅ Administrator can delegate to both department-heads and instructors
✅ Department-head can only manage instructors
✅ Department-head cannot change roles (especially not to department-head or administrator)
✅ Department-head can delegate to instructors only
✅ All changes are properly logged
✅ UI reflects authorization restrictions
✅ No privilege escalation vulnerabilities

---

## Notes

- This plan maintains backward compatibility: existing department-head functionality remains, but with clearer boundaries
- The `administrator` role becomes the true superadmin without requiring database migrations
- Delegation system remains intact, just expands who can delegate to whom
- All changes are additive (adding restrictions) rather than removing functionality

