# Thinker HUB — Instructor Course Structure & Curriculum Guide
**Document Version:** 2.4 (Faculty Edition)  
**Target Audience:** New & Existing Instructors, Subject Matter Experts (SMEs), Teaching Assistants  
**Platform:** [Thinker HUB](file:///c:/Users/mukuk/Documents/GitHub/thinker_HUB)  

---

## Welcome to Thinker HUB Faculty

At Thinker HUB, we build **outcome-driven, mastery-based technical and vocational learning programs**. Unlike passive, pre-recorded video sites, our courses blend structured live cohort mentorship, practical project portfolios, rigorous continuous assessments, and gamified student engagement.

When an instructor submits a new course or structures an existing curriculum, it must adhere to the standardized architecture outlined in this guide. This ensures a consistent, high-converting experience for prospective students and allows our administration team to quickly onboard your course into the platform.

---

## 1. The Core Architectural Pillars of a Thinker HUB Course

Every course published on Thinker HUB is composed of six essential layers:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. COURSE IDENTITY & METADATA                               │
│    Title, Code, Short Description, Detailed Overview, Cover │
├─────────────────────────────────────────────────────────────┤
│ 2. DELIVERY & PRICING MATRIX                                │
│    Group vs. 1-on-1 Mentorship (Beginner/Intermediate/Adv)  │
├─────────────────────────────────────────────────────────────┤
│ 3. PREREQUISITES & 3-TIER PROGRESSION                       │
│    Hardware/Software Requirements, Level Milestones         │
├─────────────────────────────────────────────────────────────┤
│ 4. MODULAR CURRICULUM (SECTIONS & LESSONS)                  │
│    Sequential Sections, Video Labs, Reading, Projects       │
├─────────────────────────────────────────────────────────────┤
│ 5. CONTINUOUS ASSESSMENTS & GRADING                         │
│    Formative Quizzes (MCQ/Theory/Code), Practical Homework  │
├─────────────────────────────────────────────────────────────┤
│ 6. LIVE COHORT SESSIONS & CAPSTONE                          │
│    Live Schedule, Google Meet/Zoom, Portfolio Capstone      │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Granular Field Specifications

### 2.1 Course Identity & Metadata
* **Course Title:** Must be engaging, clear, and outcome-oriented.
  * *Good:* `"Full-Stack Web Development Mastery: From Zero to Cloud Deployment"`
  * *Avoid:* `"Web Development Basics"`
* **Course Code:** Unique uppercase alphanumeric identifier (2–5 letter prefix + 3-digit number). Examples: `WEB-101`, `PY-201`, `DATA-301`, `UI-102`.
* **Short Description:** 1–3 punchy sentences summarizing who the course is for, what problem it solves, and the primary skill acquired.
* **Detailed Overview:** A comprehensive 2–4 paragraph narrative displayed in the student syllabus modal:
  * Course philosophy and teaching approach.
  * Target learner profile (career switchers, university students, junior professionals).
  * Real-world problems students will be equipped to tackle.
* **Timeline / Duration:** Total calendar weeks plus estimated weekly time commitment.  
  * *Example:* `8 Weeks (approx. 6–8 hours per week)`.

---

### 2.2 Delivery Categories & Pricing Matrix
Thinker HUB offers dual delivery models to accommodate self-paced group cohorts and premium personalized mentorship.

| Delivery Model | Learner Level | Standard Range (ZMW) | Recommended Duration |
| :--- | :--- | :--- | :--- |
| **Group Cohort** (Max 15) | Beginner | K250 – K350 | 6 to 8 Weeks |
| **Group Cohort** (Max 15) | Intermediate | K350 – K500 | 6 to 8 Weeks |
| **Group Cohort** (Max 15) | Advanced | K500 – K750 | 8 to 10 Weeks |
| **One-On-One Mentorship** | Beginner | K450 – K600 | 6 to 8 Weeks |
| **One-On-One Mentorship** | Intermediate | K600 – K800 | 6 to 8 Weeks |
| **One-On-One Mentorship** | Advanced | K800 – K1,200 | 8 to 10 Weeks |

---

### 2.3 Learner Requirements & Technical Prerequisites
State clear, realistic prerequisites so learners can self-qualify:
1. **Hardware:** Computer specifications (e.g., minimum 8GB RAM, Core i5 or Apple M-series).
2. **Software/Tooling:** Free or open-source software (e.g., VS Code, Node.js LTS, Git, Docker).
3. **Academic / Prior Knowledge:** Foundation expectations (e.g., "No prior programming experience required" or "Familiarity with high school mathematics and basic command line").
4. **Time Dedication:** Realistic hours needed outside live class for homework and debugging.

---

### 2.4 Three-Tier Level Progression
Thinker HUB structures learning curves into three distinct milestones:
1. **Beginner Phase (Foundation & Environment):**
   * Fundamentals, development environment setup, syntax, and heavily guided exercises.
2. **Intermediate Phase (Application & Integration):**
   * Multi-file architectures, database schemas, REST APIs, and collaborative best practices.
3. **Advanced Phase (Production & Capstone):**
   * Security, performance optimization, edge-case troubleshooting, and autonomous portfolio creation.

---

### 2.5 Key Learning Outcome (Capstone)
Every course culminates in a **tangible, verifiable outcome**.
* *Example:* `"Design, build, and deploy 3 production-grade portfolio applications to a live cloud VPS with custom domains, CI/CD automated deployment, and automated unit testing."`

---

## 3. Curriculum Structure: Sections & Lessons

To support sequential e-learning and progress tracking, courses are split into **Sections (Modules)** and individual **Lessons**.

```
Course
 ├── Section 1: Foundations & Environment Setup
 │    ├── Lesson 1.1: Introduction & Architecture Overview (Video - 10 min) [Free Preview]
 │    ├── Lesson 1.2: Tooling & Development Environment Setup (Interactive Lab)
 │    └── Lesson 1.3: Core Syntax & First Project (Reading & Code Starter)
 ├── Section 2: Building Core Features
 │    ├── Lesson 2.1: Data Models & Business Logic (Video - 18 min)
 │    └── Lesson 2.2: RESTful Endpoint Integration (Hands-on Workshop)
 └── Section 3: Capstone, Testing & Deployment
      ├── Lesson 3.1: Security, Auth & Best Practices (Reading & Checklist)
      └── Lesson 3.2: Production Deployment & Live Demo (Project Milestone)
```

### Lesson Types Supported:
* **`video`**: Pre-recorded conceptual video or recorded lecture (YouTube Unlisted, Vimeo, or MP4 cloud stream). Minimum watch completion threshold defaults to 90%.
* **`reading`**: In-depth written guide, reference cheat-sheet, or code architecture documentation.
* **`lab / project`**: Hands-on coding challenge with starter repository and test suite.

---

## 4. Assessment Strategy: Quizzes & Homework Assignments

### 4.1 Formative Quizzes
* **Purpose:** Quick comprehension checks at the end of each module.
* **Question Types:**
  * Multiple Choice (Single or Multi-select with explanations).
  * Theory / Conceptual (Brief written explanation).
  * Practical Code Interpretation (Spot the bug, write the return value).
* **Parameters:**
  * Default Pass Mark: **50%** (Beginner) to **70%** (Advanced).
  * Time Limit: 20–45 minutes.
  * Question Shuffle: Enabled to ensure testing integrity.

### 4.2 Practical Homework Assignments
Assignments account for **60% of the student's final certification score**.
* **Deliverable Formats:** GitHub repository URL, hosted demo URL, or attached PDF write-up / design file.
* **Grading Rubric Breakdown:**
  * Functionality & Requirements Compliance (40%)
  * Code Quality, Organization & Architecture (30%)
  * User Experience & Responsive Design (20%)
  * Documentation, Git Commits & Clean Presentation (10%)
* **Turnaround Standard:** Instructors review and grade submissions within **72 hours**, leaving actionable, constructive feedback. Students who score below 60% are given a **48-hour re-submission window**.

---

## 5. Live Sessions & Office Hours

Courses run on structured **Intakes** (e.g., *May 2026 Cohort*).
* **Timetable Window:** Standard sessions run between 60 to 90 minutes.
* **Platform:** Google Meet or Zoom (link provided in the session notes).
* **Attendance Register:** Instructors record live attendance inside the Instructor Portal immediately after class. Maintaining $\ge 80\%$ attendance is mandatory for student certificate eligibility.

---

## 6. Gamification: XP & Achievement Badges

Thinker HUB engages learners using points and badges:
* **Class Punctuality:** Marked "Present" on the attendance register $\rightarrow$ **+25 XP**
* **Early Submission:** Homework submitted $\ge 24$ hours before deadline $\rightarrow$ **+50 XP** (*Speed Demon* badge)
* **Quiz Mastery:** Scored 100% on first quiz attempt $\rightarrow$ **+75 XP** (*Mastermind* badge)
* **Capstone Completion:** Successfully presented final course capstone $\rightarrow$ **+200 XP** (*Capstone Champion* badge)

---

## 7. Instructor Course Submission Template (Fillable Worksheet)

> **Instructions for the Instructor:**  
> Please fill out the sections below. You can return this document in Markdown, Word (`.docx`), or plain text. Our administration team will configure your course into the Thinker HUB catalog.

```markdown
# Thinker HUB Course Submission Worksheet

## 1. Basic Course Information
- Course Title: [Insert engaging, outcome-driven title]
- Proposed Course Code: [e.g. CS-101, WEB-201]
- Target Audience: [Who is this course for?]
- Estimated Duration: [e.g. 8 Weeks (approx. 5-7 hours per week)]
- Short Description (Catalog Summary):
  > [1-3 sentences highlighting key value]

- Detailed Overview (Syllabus Narrative):
  > [2-4 paragraphs explaining philosophy, pedagogy, skills acquired, and real-world impact]

---

## 2. Fee & Enrollment Structure
Please indicate which delivery tiers you plan to facilitate:

- [ ] Group Cohort | Beginner | Fee: [e.g. K300] | Duration: [e.g. 6 Weeks]
- [ ] Group Cohort | Intermediate | Fee: [e.g. K450] | Duration: [e.g. 6 Weeks]
- [ ] Group Cohort | Advanced | Fee: [e.g. K650] | Duration: [e.g. 8 Weeks]
- [ ] One-On-One Mentorship | Beginner | Fee: [e.g. K500] | Duration: [e.g. 6 Weeks]
- [ ] One-On-One Mentorship | Intermediate | Fee: [e.g. K700] | Duration: [e.g. 6 Weeks]
- [ ] One-On-One Mentorship | Advanced | Fee: [e.g. K950] | Duration: [e.g. 8 Weeks]

Enrollment Mode:
- [ ] Open Public Enrollment (Anyone can register and pay)
- [ ] Private / Restricted Cohort (Students must be manually invited or verified)

---

## 3. Prerequisites & Hardware Requirements
List 3-5 prerequisites (one per line):
1. [e.g. Laptop with at least 8GB RAM running Windows, macOS, or Linux]
2. [e.g. Stable internet connection for live interactive sessions]
3. [e.g. Basic computer literacy and file management]
4. [e.g. Dedication of 6-8 hours weekly for hands-on assignments]

---

## 4. 3-Tier Level Progression Milestones
- Beginner Level Milestone:
  [What will learners master during the foundational phase?]
- Intermediate Level Milestone:
  [What will learners build during the applied development phase?]
- Advanced Level Milestone:
  [What real-world problems will learners solve during the capstone phase?]

---

## 5. Key Learning Outcome (Graduation Deliverable)
- [Summarize the primary project or portfolio piece students will finish with]

---

## 6. Modular Curriculum Outline (Sections & Lessons)

### Module 1: [Module Title]
- Module Overview: [Brief 1-2 sentence description]
- Lessons:
  1. Lesson 1.1: [Lesson Title] | Type: [Video / Reading / Lab] | Duration: [e.g. 15 min] | Free Preview: [Yes/No]
  2. Lesson 1.2: [Lesson Title] | Type: [Video / Reading / Lab] | Duration: [e.g. 25 min] | Free Preview: [No]
  3. Lesson 1.3: [Lesson Title] | Type: [Video / Reading / Lab] | Duration: [e.g. 20 min] | Free Preview: [No]
- Module Assessment:
  - Quiz Title: [e.g. Module 1 Knowledge Check]
  - Quiz Topics: [e.g. 10 MCQs covering syntax and fundamentals]

### Module 2: [Module Title]
- Module Overview: [Brief description]
- Lessons:
  1. Lesson 2.1: [Lesson Title] | Type: [Video / Reading / Lab]
  2. Lesson 2.2: [Lesson Title] | Type: [Video / Reading / Lab]
- Practical Assignment:
  - Assignment Title: [e.g. Mini-Project 1: Responsive Product Landing Page]
  - Deliverable: [e.g. GitHub Repository + Hosted Demo URL]

### Module 3: [Module Title]
- Module Overview: [Brief description]
- Lessons:
  1. Lesson 3.1: [Lesson Title] | Type: [Video / Reading / Lab]
  2. Lesson 3.2: [Lesson Title] | Type: [Video / Reading / Lab]

### Module 4: [Capstone Project & Final Assessment]
- Capstone Project Title: [e.g. Full-Stack E-Commerce Application]
- Project Scope: [Brief summary of full application requirements]
- Evaluation Criteria: [Functionality, Code Architecture, Testing, Deployment]

---

## 7. Sample Assessment Questions (For Module Quizzes)
Provide at least 2 sample questions to calibrate quiz difficulty:

Question 1:
- Type: Multiple Choice
- Question: [Insert Question Prompt]
- Choices:
  A) [Choice text]
  B) [Choice text]
  C) [Choice text]
  D) [Choice text]
- Correct Answer: [A, B, C, or D]
- Feedback / Explanation: [Why this choice is correct]

Question 2:
- Type: Practical / Code Challenge
- Question: [Insert Code snippet or problem statement]
- Model Solution / Criteria: [Expected logic or explanation]
```

---

## 8. Complete Concrete Example: `WEB-101`

To visualize how a completed course looks in practice, here is a reference implementation of a high-performing Thinker HUB course:

### Course Header
* **Title:** Full-Stack Web Development Mastery: From Zero to Cloud Deployed
* **Code:** `WEB-101`
* **Timeline:** 8 Weeks (approx. 6–8 hours per week)
* **Short Description:** Comprehensive journey from modern HTML5/CSS3 and JavaScript to React frontends, robust Laravel APIs, and production cloud deployment.
* **Key Outcome:** Build, test, and deploy 3 production-grade portfolio applications with responsive UI, secure authentication, relational database storage, and CI/CD deployment.

### Pricing Matrix
* Group Cohort | Beginner | K350 | 8 Weeks
* Group Cohort | Intermediate | K550 | 8 Weeks
* One-On-One | Beginner | K650 | 8 Weeks
* One-On-One | Advanced | K950 | 8 Weeks

### Level Progression
* **Beginner:** HTML5 semantic structure, modern CSS3 layout systems (Flexbox & Grid), JavaScript essentials, DOM manipulation, and Git/GitHub version control.
* **Intermediate:** Component-driven development with React & Tailwind CSS, RESTful API consumption, state management, backend routing, and relational database design.
* **Advanced:** Full-stack authentication (Sanctum/OAuth), CRUD API development, asynchronous queues, automated unit testing, and cloud VPS production deployment.

---

## 9. Pre-Flight Submission Checklist for Instructors

Before sending your curriculum proposal to the Thinker HUB admin team, verify:

- [ ] **Title & Code:** Title is outcome-focused; course code is unique and uppercase.
- [ ] **Overview & Audience:** Clear description of student prerequisites and target career outcomes.
- [ ] **Pricing Matrix:** Filled out for all delivery categories you are willing to teach.
- [ ] **Sequential Flow:** Sections and lessons follow a clear progression (Beginner $\rightarrow$ Intermediate $\rightarrow$ Advanced).
- [ ] **Hands-on Emphasis:** Every module contains practical exercises or starter code.
- [ ] **Assessments Defined:** Quizzes include correct answers and explanations; assignments feature explicit grading rubrics.
- [ ] **Capstone Deliverable:** Final project produces a verifiable portfolio piece.

---

## 10. Submission & Support

Completed guides can be submitted directly via:
* **Instructor Portal:** [`/instructor`](file:///c:/Users/mukuk/Documents/GitHub/thinker_HUB/app/Filament/Instructor)
* **Curriculum Desk:** `academics@thinkerhub.ac.zm`
* **Technical Imports:** We also support bulk JSON import for courses, quizzes, and live schedules as documented in [samples/import/README.md](file:///c:/Users/mukuk/Documents/GitHub/thinker_HUB/samples/import/README.md).
