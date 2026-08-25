# Thinker HUB JSON Import Templates & Specification Guide

This directory contains updated JSON templates for bulk importing **Courses**, **Quizzes**, and **Sessions** into Thinker HUB via the Admin and Instructor panels.

---

## 1. Courses Template (`courses.sample.json`)

Used in **Admin Panel -> Courses -> Bulk Import JSON**.

### Root Format
A JSON **Array** of course objects.

### Field Definitions

| Field | Type | Required | Description / Format |
|---|---|---|---|
| `title` | `string` | **Yes** | Full human-readable course name (e.g. `"Full-Stack Web Development Mastery"`). |
| `code` | `string` | **Yes** | Unique course identifier (e.g. `"WEB-101"`). If a course with this code already exists, its fields will be updated (upsert). |
| `description` | `string` | No | Short 1–3 sentence summary of the course. |
| `overview` | `string` | No | In-depth syllabus and curriculum overview displayed on the student course details page. |
| `timeline` | `string` | No | Estimated course duration (e.g. `"8 Weeks (approx. 6-8 hours per week)"`). |
| `fees` | `array` or `string` | No | Breakdown of fees by category and level. Objects accept `category`, `level`, `amount`, and `duration`. |
| `requirements` | `array` or `string` | No | Array of requirement strings or newline-separated string. |
| `level_progression` | `array` or `string` | No | Progression milestones across `"Beginner"`, `"Intermediate"`, and `"Advanced"`. |
| `key_outcome` | `string` | No | Concrete capabilities and portfolio projects learners achieve upon completion. |
| `is_active` | `boolean` | No | Defaults to `true`. Set to `false` for draft mode. |

---

## 2. Quizzes Template (`quizzes.sample.json`)

Used in **Admin / Instructor Panel -> Quizzes -> Import Quizzes (JSON)**.

### Root Format
A JSON **Array** of quiz objects.

### Field Definitions

| Field | Type | Required | Description / Format |
|---|---|---|---|
| `course_code` | `string` | **Yes** | Matches the `code` of the associated Course (e.g. `"WEB-101"`). |
| `title` | `string` | **Yes** | Title of the assessment or quiz. |
| `description` | `string` | No | Guidelines, instructions, or scope for the quiz. |
| `publish_at` | `string` | No | Schedule release datetime in `"YYYY-MM-DD HH:MM:SS"` format (e.g. `"2026-09-01 09:00:00"`). |
| `time_limit_minutes`| `integer`| No | Allowed duration in minutes (e.g. `45`). Leave `null` for untimed. |
| `shuffle_questions` | `boolean`| No | Whether questions are randomized for each student attempt (default `false`). |
| `show_results` | `boolean`| No | Whether to show score breakdown and explanations upon submission (default `true`). |
| `pass_percentage` | `integer`| No | Minimum passing score percentage between `0` and `100` (default `50`). |
| `is_active` | `boolean`| No | Defaults to `true`. |
| `questions` | `array` | **Yes** | Array of question objects (minimum 1 question required). |

### Question Object Fields

| Question Field | Type | Allowed Values | Description |
|---|---|---|---|
| `type` | `string` | `multiple_choice`, `theory`, `practical` | Type of question. Aliases like `mcq`, `essay`, `coding` are also normalized. |
| `question` | `string` | (Required) | The prompt or question text. |
| `explanation` | `string` | (Optional) | Feedback / rubric / ideal solution shown during review. |
| `points` | `integer` | (Default `1`) | Number of points awarded for a correct response. |
| `sort_order` | `integer` | (Optional) | Order of the question in the quiz. |
| `options` | `array` | Required for `multiple_choice` | Array of choices (between 2 and 6 options). At least one option must have `"is_correct": true`. Each option object has `"option_text"`, `"is_correct"`, and `"sort_order"`. |

---

## 3. Sessions Template (`sessions.sample.json`)

Used in **Admin / Instructor Panel -> Schedule / Course Sessions -> Import Sessions (JSON)**.

### Root Format
A JSON **Array** of session objects.

### Field Definitions

| Field | Type | Required | Description / Format |
|---|---|---|---|
| `course_code` | `string` | **Yes** | Matches the `code` of an existing Course (e.g. `"WEB-101"`). |
| `title` | `string` | No | Session topic / agenda title (e.g. `"Module 1: Semantic HTML5 & Modern CSS Layouts"`). |
| `session_date` | `string` | **Yes** | Date in `"YYYY-MM-DD"` format (e.g. `"2026-09-02"`). |
| `start_time` | `string` | **Yes** | Start time in `"HH:MM"` 24-hour format (e.g. `"09:00"`). |
| `end_time` | `string` | **Yes** | End time in `"HH:MM"` 24-hour format (e.g. `"10:30"`). |
| `type` | `string` | No | `"group"` or `"one_on_one"`. |
| `student_email` | `string` | Required if `one_on_one` | Email address of the enrolled student for 1-on-1 mentorship sessions. |
| `status` | `string` | No | `"scheduled"`, `"completed"`, `"rescheduled"`, or `"cancelled"` (defaults to `"scheduled"`). |
| `notes` | `string` | No | Meeting links (Zoom/Google Meet), room numbers, or preparation requirements. |

> **Note on Upsert**: Duplicate sessions matching `course_code` + `session_date` + `start_time` + `type` will automatically update existing records without generating duplicate rows.
