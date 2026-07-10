# Thinker HUB Course Creation Template Guide

Use this guide before creating a course in the admin panel. Fill every section, then copy the content into the course form.

## 1) Quick Rules

1. Keep the course title clear and outcome-focused.
2. Use a unique course code (example: `WD-101`, `DS-201`).
3. Write for the target learner level (Beginner, Intermediate, Advanced).
4. If enrollment is locked, selected participants must be added.
5. Set the course to active only when content is ready.

## 2) Course Template (Copy/Paste)

### Course Title
[Insert course title]

### Course Code
[Insert unique code]

### Description (Short)
[1-3 sentence summary of what the course teaches and who it is for]

### Overview (Detailed)
[Write a high-level intro shown in the course details modal. Include learner profile, teaching style, and practical outcomes.]

### Timeline
[Example: 4 Weeks (approx. 4-5 hours per week)]

### Fees
Add fee entries by category and level.

Entry format:
Category: One-On-One or Group
Level: Beginner / Intermediate / Advanced
Amount: [example: K450]
Duration: [example: 6 Weeks]

Example entries:
- One-On-One | Beginner | K450 | 6 Weeks
- One-On-One | Intermediate | K600 | 6 Weeks
- Group | Beginner | K250 | 6 Weeks

### Requirements
Write one requirement per line.

Example:
- Access to a laptop
- Stable internet connection
- Basic computer literacy

### Level Progression
Add one entry per level.

Entry format:
Level: Beginner / Intermediate / Advanced
Details: [What learners will cover at this level]

Example entries:
- Beginner: Understand fundamentals, tooling setup, basic workflow.
- Intermediate: Build projects with best practices and collaboration.
- Advanced: Solve real-world cases, optimize, and present portfolio-quality work.

### Key Outcome
[Summarize what learners will be able to do after completing the course.]

### Enrollment Mode
Choose one:
- Open For Public Enrollment: ON
- Open For Public Enrollment: OFF (Then select allowed participants)

### Active Status
Choose one:
- Active: ON (visible and usable)
- Active: OFF (draft mode)

## 3) Field-to-System Mapping

Use this mapping when entering data in Thinker HUB:

- Title -> `title`
- Code -> `code` (must be unique)
- Description -> `description`
- Overview -> `overview`
- Timeline -> `timeline`
- Fees -> `fees`
- Requirements -> `requirements`
- Level Progression -> `level_progression`
- Key Outcome -> `key_outcome`
- Open For Public Enrollment -> `is_open_enrollment`
- Selected Participants -> `selected_participant_ids` (only when enrollment is OFF)
- Active -> `is_active`

## 4) Quality Checklist Before Submit

1. Title and code are correct and unique.
2. Description and overview are complete and free of typos.
3. Fees cover relevant category/level combinations.
4. Requirements are realistic for the target learners.
5. Level progression clearly shows Beginner -> Intermediate -> Advanced path.
6. Key outcome is measurable and practical.
7. Enrollment mode is correctly set.
8. Course is Active only if fully ready.

## 5) Ready-to-Share Message for Instructors

Use this message when requesting course details:

Please complete the Thinker HUB Course Template and send it back in one document.
Required sections:
- Title, Code, Description, Overview, Timeline
- Fees (One-On-One/Group + level + amount + duration)
- Requirements (line by line)
- Level Progression (Beginner/Intermediate/Advanced)
- Key Outcome
- Enrollment Mode (Open or Restricted)
- Active Status (ON/OFF)

We will copy your template directly into the platform, so please keep formatting clean and complete.
