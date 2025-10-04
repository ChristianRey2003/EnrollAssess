## Priority Roadmap (Focused on Main Features)

### Decision
- Deprioritize backend hardening items temporarily and focus on shipping main features that stakeholders touch daily.
- Immediate priority: Implement Admin “Conduct Interview” page (parity with Instructor), since both roles can click “I’ll conduct this interview,” but only the Instructor flow is complete.

### Current Gap (Clarified)
- Instructor portal: “Conduct Interview” is implemented with full rubric, totals, validation, and submission.
- Admin/Department Head: Button exists to claim/conduct interviews, but the actual interview page and submit flow in the admin portal are not implemented.

### Immediate Feature: Admin “Conduct Interview” Page
- Goal: Allow Department Head/Administrator to conduct the same rubric-based interview as instructors, with appropriate role-based permissions and audit trail.

#### Acceptance Criteria
- Admin can open an interview detail and click “Conduct Interview” to reach a rubric page.
- Page shows applicant profile, exam summary, and full rubric sections (Technical 40, Communication 30, Analytical 30), with live totals and validation.
- Admin can save draft and submit final evaluation; totals auto-compute; recommendation captured.
- Submissions immediately reflect in Department Head dashboards/analytics.
- Authorization: Only Department Head or Administrator can access the admin conduct page; instructors cannot.
- Audit: Store evaluator role and user id; record timestamps; prevent double submissions unless explicitly editing.

#### Implementation Outline (High-Level)
- Routes (admin group):
  - GET `admin/interviews/{interview}/conduct` → Admin interview form
  - POST `admin/interviews/{interview}/conduct` → Store/Update evaluation
- Controller:
  - Add methods on `InterviewController`: `adminConductForm()` and `adminConductSubmit()` (or a dedicated `DepartmentHeadController` endpoint if preferred for separation).
  - Reuse rubric calculation utilities used by instructor submission to ensure a single source of truth.
- View:
  - New Blade: `resources/views/admin/interviews/conduct.blade.php`
  - Mirror the instructor UI/UX (without blue theme), align with admin maroon theme; keep components consistent.
- Policies/Authorization:
  - Gate: `department-head` or `administrator` can conduct; instructor cannot access admin routes.
  - Record evaluator metadata on the `Interview` or related `InterviewResult` model.
- Data Model:
  - Reuse existing interview result storage; ensure fields for evaluator, role, totals, recommendation, strengths/improvements.

#### QA Checklist
- Open as Department Head → Form renders with applicant and rubric.
- Required fields enforced; totals correct; submit persists and updates analytics.
- Unauthorized user cannot access admin conduct routes.
- Idempotency: prevent duplicate final submissions unless editing by authorized roles.

### Shortly After (Near-Term)
- Admin route hardening: ensure `auth` + `role` on admin group; keep `ajax.auth` if needed for AJAX specifics.
- Queue emails for exam-set assignment to avoid slow bulk actions.
- Core feature tests: applicants CRUD/bulk, exam section flow, instructor/admin rubric submission, DH bulk decisions.

### Deferred (To pick up next)
- Unify legacy vs sectioned exam routes/UI (remove placeholders).
- Dynamic PDF reports (controller-driven, policies, caching/queue).
- Policies for Applicant/Interview/Question across the board; observability (metrics, slow queries, alerts).

### Risks & Mitigations
- Role overlap: Ensure an interview isn’t double-scored; enforce one active final evaluation per evaluator role.
- Consistency: Reuse the same scoring logic/service for instructor and admin to avoid drift.
- Access: Strict policies and route middleware to prevent cross-role access.

### Status
- ✅ **COMPLETED**: Admin "Conduct Interview" feature fully implemented and ready for use.

### Implementation Summary
- ✅ **Routes Added**: `admin/interviews/{id}/conduct` (GET/POST), claim/release endpoints
- ✅ **Controller Methods**: `adminConductForm()`, `adminConductSubmit()`, `adminClaimInterview()`, `adminReleaseInterview()`
- ✅ **Admin View**: `resources/views/admin/interviews/conduct.blade.php` with full rubric, live totals, and admin styling
- ✅ **Authorization**: Department Head and Administrator access only; soft locking prevents conflicts
- ✅ **UI Integration**: "I'll Conduct This" buttons added to admin interview index with claim status indicators
- ✅ **Data Consistency**: Reuses same scoring logic as instructor; stores evaluator metadata for audit trail
- ✅ **Draft Support**: Save draft and submit final with different validation and status updates
- ✅ **Real-time Totals**: Live score calculation with section breakdowns and validation feedback


