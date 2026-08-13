# Thinker HUB — System Requirements Specification (SRS)

**Document Version:** 2.1.0  
**Status:** Approved / Active  
**Last Updated:** August 2026  
**Target Platform:** Thinker HUB Learning Ecosystem (Laravel 12 / Filament v3 / Livewire 3 / Tailwind CSS)

---

## 1. Executive Summary

Thinker HUB is a modern, unified learning management and creator community platform designed to empower students, instructors, contributors, and administrators. This specification defines the functional, architectural, UI/UX, and security requirements for the platform, explicitly detailing the **Multi-Role Account System**, **Platform-Wide Icon Standardization (Emoji Replacement)**, and the **Interactive Two-Column Schedule & Timetable Architecture**.

---

## 2. Core Feature Specifications

### 2.1 Specification 1: Multi-Role Account Architecture

#### 2.1.1 Overview & Dual-Role Registration
The platform supports multi-role accounts allowing a single identity (one email / credential set) to operate concurrently as both a **Student** and an **Instructor**.

1. **Dual Registration Workflow**:
   - A registered Student can submit an application to become an Instructor through the public or student-facing instructor application workflow.
   - A registered Instructor can enroll in courses as a Student without creating a separate secondary account.
   - Account state transitions and dual capabilities require formal verification and approval by an Administrator.

2. **Administrative Approval Gate**:
   - Instructor status remains in `pending` review until verified by an Admin within the Admin Panel.
   - Upon Admin approval (`status = approved`, `is_active = true`), the user's account profile is elevated to active dual-role status.
   - Admin rejection retains the user's primary/student role without interrupting course enrollments or historical learning data.

3. **Automatic Contributor Feature Access**:
   - Any account verified and approved with both **Student** and **Instructor** roles must automatically receive full access to all **Contributor** features without requiring additional manual role applications.
   - **Unlocked Contributor Capabilities**:
     - Authoring and publishing technical articles & blogs (`isBlogger`).
     - Sharing research insights, study hacks, tips & tricks (`isResearcher`).
     - Posting job openings, internships, and project opportunities (`isEmployer`).
     - Access to the Contributor Panel (`/contributor`) alongside the Student Panel (`/student`) and Instructor Panel (`/instructor`).

#### 2.1.2 Role & Panel Access Matrix

| Account Role Configuration | Student Panel (`/student`) | Instructor Panel (`/instructor`) | Contributor Panel (`/contributor`) | Admin Panel (`/admin`) | Contributor Features (Blogs / Tips / Jobs) |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Student Only** | Yes | No | No | No | No |
| **Instructor Only** | No (unless enrolled) | Yes | Partial (Tips/Videos) | No | Partial |
| **Dual Role (Student + Instructor)** *(Approved)* | **Yes** | **Yes** | **Yes** | No | **Full Access** |
| **Contributor Only** (Blogger/Researcher/Employer) | No | No | Yes | No | Role-specific |
| **Administrator** | Yes | Yes | Yes | Yes | Full Access |

---

### 2.2 Specification 2: Platform-Wide Emoji to System Icons Migration

#### 2.2.1 Design Principle & Quality Standard
To ensure a cohesive, professional, enterprise-grade user interface, **all instances of Unicode emojis must be replaced with standard, scalable system vector icons** (Blade Heroicons / Filament System SVG Icons) across all customer-facing and administrative views.

#### 2.2.2 Icon Standardization Taxonomy

| Component / Feature Area | Legacy Emoji Representation | Standard System Icon Component | System Meaning / Token |
| :--- | :--- | :--- | :--- |
| **Calendar View** | 📅 | `<x-heroicon-o-calendar-days>` | Calendar view switcher & date indicators |
| **List View** | 📋 | `<x-heroicon-o-queue-list>` / `<x-heroicon-o-bars-3-bottom-left>` | Tabular / list view mode |
| **Course Progress** | 📊 | `<x-heroicon-o-chart-bar-square>` | Progress tracking & analytics metrics |
| **Attendance & Completion**| ✅ | `<x-heroicon-o-check-badge>` / `<x-heroicon-o-check-circle>` | Verified attendance & completion status |
| **Pending / In-Review** | ⏳ | `<x-heroicon-o-clock>` | Awaiting approval or upcoming session |
| **Alerts & Warnings** | ⚠️ | `<x-heroicon-o-exclamation-triangle>` | Action required or alert notice |
| **Certificates** | 🎓 | `<x-heroicon-o-academic-cap>` | Certificate awards & graduation criteria |
| **Quizzes & Tests** | 📝 | `<x-heroicon-o-document-check>` | Gradable quizzes and assessments |
| **Assignments** | 💼 / 📂 | `<x-heroicon-o-folder-open>` / `<x-heroicon-o-clipboard-document-list>` | Student project submissions |
| **Community & Chat** | 💬 / 👥 | `<x-heroicon-o-chat-bubble-left-right>` / `<x-heroicon-o-user-group>` | Discussions & cohort channels |
| **Opportunities & Jobs** | 🚀 | `<x-heroicon-o-briefcase>` | Career opportunities & internships |
| **Settings & Profile** | ⚙️ | `<x-heroicon-o-cog-6-tooth>` | User preferences & profile settings |

#### 2.2.3 Implementation Guidelines
- **Scalability**: All icons must use SVG-based rendering with consistent stroke widths (`1.5` or `2.0` on outline variants).
- **Accessibility**: System icons accompanying action buttons or standalone indicators must maintain proper `aria-hidden="true"` attributes when decorative or descriptive `aria-label` / screen-reader text when interactive.
- **Theme Compliance**: Icons must dynamically inherit color tokens (`var(--hub-primary)`, `var(--hub-ink)`, `var(--hub-muted)`, status chip colors) across light and dark display modes.

---

### 2.3 Specification 3: Schedule & Timetable Architecture

#### 2.3.1 Two-Column Side-by-Side Layout & Mobile Responsiveness
The Schedule interface for both Students (`/student/schedule`) and Instructors (`/instructor/schedule`) renders as a responsive **two-column side-by-side workspace** on desktop/tablet, and smoothly stacks into a single-column, touch-optimized workflow on mobile devices:

```
+-----------------------------------------------------------------------------+
|                           MY SCHEDULE & TIMETABLE                           |
+------------------------------------+----------------------------------------+
|       COLUMN 1: CALENDAR VIEW      |    COLUMN 2: FILTER BY STATUS & LIST   |
|              (LEFT)                |                 (RIGHT)                |
+------------------------------------+----------------------------------------+
| [ Month* | Week | Day | Custom ]   | [ All Statuses v ] [ Search Session ]  |
|                                    |                                        |
|  Sun   Mon   Tue   Wed   Thu   Fri | * Scheduled (3)                        |
| [ 1]  [ 2]  [ 3]  [ 4]  [ 5]  [ 6] |   - WD-101: Frontend React (10:00 AM)  |
|   |     |     |     |     |        |   - DS-201: Python Basics (02:00 PM)   |
|   *     *           *              | * Completed (12)                       |
| (Click event/cell -> opens Modal)  | * Rescheduled (1)                      |
|                                    | * Cancelled (0)                        |
|                                    | (Click row -> opens Modal)             |
+------------------------------------+----------------------------------------+
```

1. **Left Column (Calendar View)**:
   - Visual temporal grid displaying class slots, dates, time markers, and course tags.
   - Interactive date cells allowing rapid date navigation and class modal opening.
   - Status-colored badges and indicators for each date.
   - **Mobile Responsiveness (< 640px / < 768px)**:
     - Month view adapts compactly with bold day numerals, session count badges, and color-coded status dots (`.hub-cal-dot`), avoiding overflow or clipped text. Clicking any day cell opens the class modal.
     - Week view activates a smooth horizontal scroll container with scroll-snap (`.hub-cal-week-wrap`), maintaining clear column widths and legibility.
     - Controls wrap cleanly into multi-row touch targets without horizontal body scroll.

2. **Right Column (Filter by Status & Session Feed)**:
   - Dedicated filter controls by session status: `All`, `Scheduled`, `Completed`, `Rescheduled`, `Cancelled`.
   - Chronological list of session cards showing session code, course title, scheduled time, instructor/student names, and meeting status.
   - Live count indicators for each status category.
   - Responsive cards with touch hover/tap states.

#### 2.3.2 Temporal Filter Modes & Default View
- **Default View**: The schedule page defaults to showing the **Current Month's Schedule** (`$rangeMode = 'month'`).
- **Supported Filter Range Modes**:
  1. **Month View** *(Default)*: Comprehensive monthly calendar grid with full-pill event tags on desktop and color-coded touch dots on mobile devices.
  2. **Week View**: 7-day view displaying all sessions distributed from Monday to Sunday, with horizontal swipe support on small screens.
  3. **Day View**: Focused hourly breakdown for the selected single day.
  4. **Custom Range**: Start-date and End-date picker allowing arbitrary timetable extraction (e.g., intensive workshop periods, examination weeks).

#### 2.3.3 Interactive Class Details Pop-up Modal
Clicking on any scheduled class anywhere in the schedule interface must trigger a detailed **Pop-up Modal**.

1. **Trigger Parity**:
   - Must open when clicking an event pill, dot, or day cell on the **Left Column (Calendar View)**.
   - Must open when clicking any session card / row in the **Right Column (Filter by Status list)**.

2. **Modal Content Payload & Responsiveness**:
   - **Header**: Course Title, Course Code (e.g. `WD-101`), and Session Type (One-on-One / Cohort / Workshop).
   - **Time & Location**: Full Date, Start Time, End Time, Timezone, and Direct Join Link (e.g. Google Meet, Zoom, or On-Campus Room).
   - **Participants**:
     - For Student: Instructor Name, Avatar, Bio summary, and Contact Email / WhatsApp button.
     - For Instructor: Enrolled Student Name(s), Contact Info, and Attendance Record.
   - **Session Details**: Topic / Curriculum agenda, Attached learning materials, and Special notes.
   - **Action Controls**:
     - *Request Reschedule Button*: Opens the reschedule submission drawer/dialogue with preferred date/time and reason.
     - *Mark Completed Button* (Instructor view only): Mark session as completed.
     - *Close / Dismiss Button*.
   - **Mobile Modal Layout**: Backdrop blur, centered cards with `max-height: 90vh`, smooth vertical scrolling, and touch-accessible close buttons.

---

## 3. Data Model & Database Schema Requirements

### 3.1 `users` Table Extensions
- `role`: Primary account role (`admin`, `instructor`, `student`, `blogger`, `researcher`, `employer`).
- `is_active`: Boolean status flag indicating administrative activation.
- Multi-role support through role flags / permissions or verified application linking:
  - Methods: `isStudent(): bool`, `isInstructor(): bool`, `isContributor(): bool`, `canAccessPanel(Panel $panel): bool`.
  - Automatic contributor entitlement check:
    ```php
    public function isContributor(): bool
    {
        // Dual-role student + active instructor automatically receives contributor rights
        if ($this->isStudent() && $this->isInstructor() && $this->is_active) {
            return true;
        }

        return in_array($this->role, ['blogger', 'researcher', 'employer', 'contributor'], true);
    }
    ```

### 3.2 `instructor_applications` Table
- `user_id`: Foreign key reference to `users.id`.
- `status`: Enum (`pending`, `approved`, `rejected`).
- `reviewed_by`: Foreign key reference to reviewing Admin `users.id`.
- `reviewed_at`: Timestamp of approval/rejection.
- `admin_notes`: Feedback and verification rationale.

### 3.3 `course_sessions` Table
- `course_id`: Foreign key to `courses.id`.
- `student_id`: Foreign key to `users.id` (for one-on-one sessions) or cohort grouping.
- `instructor_id`: Foreign key to `users.id`.
- `scheduled_at`: Start datetime timestamp.
- `duration_minutes`: Integer duration (default 60).
- `status`: Enum (`scheduled`, `completed`, `rescheduled`, `cancelled`).
- `meeting_link`: Virtual classroom URL.
- `topic`: Session agenda and curriculum unit.

---

## 4. UI/UX & Responsive Design Specifications

1. **Breakpoints & Mobile Stacking**:
   - **Desktop (>= 1024px)**: Full side-by-side 2-column layout (55% Calendar, 45% Filter List).
   - **Tablet / Mobile (< 1024px)**: Gracefully collapses into a stacked view with a top toggle to switch focus between Calendar and Status List while retaining full modal click functionality.

2. **Design Tokens & Theme Consistency**:
   - Modern glassmorphic cards (`.hub-card`) with smooth border radii (`12px` - `16px`).
   - Unified CSS Variables: `--hub-primary`, `--hub-surface`, `--hub-border`, `--hub-ink`, `--hub-muted`.
   - Zero visual degradation in dark mode.

---

## 5. Security, Authorization & Verification

1. **Panel Access Guarding**:
   - Filament panel providers (`AdminPanelProvider`, `InstructorPanelProvider`, `StudentPanelProvider`, `ContributorPanelProvider`) must evaluate `canAccessPanel()` at middleware time.
   - Dual-role accounts must switch seamlessly between panels via profile panel switchers or direct navigation without logging out.

2. **Action Authorization**:
   - Reschedule requests may only be initiated by the assigned student or instructor for that specific session.
   - Status updates on instructor applications are strictly restricted to users with `role === 'admin'`.

---

## 6. Acceptance Criteria & QA Checklist

- [x] **Multi-Role Accounts**:
  - [x] A student account can apply for an instructor profile.
  - [x] Admin can approve/reject the application from the Admin Panel.
  - [x] Approved dual-role users can access `/student`, `/instructor`, and `/contributor` panels.
  - [x] Contributor publishing features (blogs, tips, opportunities) are immediately accessible to dual-role users.
- [x] **Icon Standardization**:
  - [x] No raw emojis exist in navigation items, buttons, cards, headers, or status chips.
  - [x] All icon placements utilize Blade Heroicons / SVG components with uniform sizing and styling.
- [x] **Schedule Layout & Interactions**:
  - [x] Calendar and Filter by Status render side-by-side in a two-column desktop layout.
  - [x] Schedule view defaults to Current Week.
  - [x] Filter options are functional for Day, Week, Month, and Custom Date Range.
  - [x] Clicking any session on the Calendar triggers the Class Details Pop-up Modal.
  - [x] Clicking any session in the Filter by Status list triggers the Class Details Pop-up Modal.
