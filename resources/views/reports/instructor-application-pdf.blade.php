@extends('reports.layout')

@section('title', 'Instructor Application Dossier - ' . $applicant['name'])
@section('report_type', 'Instructor Application & Accreditation Dossier')

@section('content')
    <style>
        .meta-grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .meta-grid-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: 700;
            color: #475569;
            width: 25%;
        }
        .meta-val {
            color: #0f172a;
            width: 25%;
        }
        .text-block-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.4;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-top: 4px;
        }
        .signature-box {
            border: 1px dashed #cbd5e1;
            background: #fcfdfe;
            border-radius: 4px;
            padding: 8px 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sign-line {
            border-bottom: 1px solid #475569;
            width: 180px;
            display: inline-block;
            margin-top: 25px;
        }
        .doc-link {
            color: #0284c7;
            text-decoration: none;
            font-weight: 600;
        }
    </style>

    {{-- Header Banner Card --}}
    <div class="section-box" style="border-left: 4px solid #0d9488;">
        <div class="section-header" style="background: #f0fdfa;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: 800; font-size: 9pt; color: #0f172a;">
                        {{ $applicant['name'] }}
                    </td>
                    <td class="text-right">
                        <span class="badge {{ $review['status_badge_class'] }}">
                            {{ $review['status_label'] }}
                        </span>
                        <span class="badge badge-info" style="margin-left: 4px;">
                            {{ $proposal['type_label'] }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="section-body">
            <table class="meta-grid-table">
                <tr>
                    <td class="meta-label">Application ID:</td>
                    <td class="meta-val"><strong>{{ $reference_id }}</strong></td>
                    <td class="meta-label">Submitted On:</td>
                    <td class="meta-val">{{ $application->created_at ? $application->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Primary Email:</td>
                    <td class="meta-val">{{ $applicant['email'] }}</td>
                    <td class="meta-label">Telephone:</td>
                    <td class="meta-val">{{ $applicant['phone'] }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Current Role:</td>
                    <td class="meta-val">{{ $applicant['occupation'] }}</td>
                    <td class="meta-label">Core Proficiency:</td>
                    <td class="meta-val">{{ $applicant['proficiency'] }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Metrics Summary Grid --}}
    <table class="stats-grid-table">
        <tr>
            <td style="width: 25%;">
                <div class="stat-value" style="font-size: 9pt; text-transform: uppercase;">
                    <span class="badge {{ $review['status_badge_class'] }}">{{ $review['status_label'] }}</span>
                </div>
                <div class="stat-caption">Review Decision</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value" style="font-size: 9pt; color: #0f172a;">
                    {{ $proposal['type'] === 'new' ? 'New Course' : 'Existing Course' }}
                </div>
                <div class="stat-caption">Proposal Format</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value" style="font-size: 9pt; color: #0284c7;">
                    {{ $account['exists'] ? 'User Linked' : 'Guest' }}
                </div>
                <div class="stat-caption">LMS User Account</div>
            </td>
            <td style="width: 25%;">
                <div class="stat-value" style="font-size: 9pt; color: #0d9488;">
                    {{ $application->created_at ? $application->created_at->format('d M Y') : 'Recent' }}
                </div>
                <div class="stat-caption">Application Date</div>
            </td>
        </tr>
    </table>

    {{-- Section 1: Candidate Profile & Professional Background --}}
    <div class="section-box">
        <div class="section-header">1. Candidate Profile & Contact Details</div>
        <div class="section-body">
            <table class="meta-grid-table" style="margin-bottom: 6px;">
                <tr>
                    <td class="meta-label">Full Legal Name:</td>
                    <td class="meta-val"><strong>{{ $applicant['name'] }}</strong></td>
                    <td class="meta-label">WhatsApp Contact:</td>
                    <td class="meta-val">{{ $applicant['whatsapp'] }}</td>
                </tr>
                <tr>
                    <td class="meta-label">LinkedIn Profile:</td>
                    <td class="meta-val">
                        @if ($applicant['linkedin_url'])
                            <a href="{{ $applicant['linkedin_url'] }}" class="doc-link" target="_blank">{{ Str::limit($applicant['linkedin_url'], 32) }}</a>
                        @else
                            <span class="text-muted">Not provided</span>
                        @endif
                    </td>
                    <td class="meta-label">Facebook Profile:</td>
                    <td class="meta-val">
                        @if ($applicant['facebook_url'])
                            <a href="{{ $applicant['facebook_url'] }}" class="doc-link" target="_blank">{{ Str::limit($applicant['facebook_url'], 32) }}</a>
                        @else
                            <span class="text-muted">Not provided</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="meta-label">Portfolio / Website:</td>
                    <td class="meta-val" colspan="3">
                        @if ($applicant['portfolio_url'])
                            <a href="{{ $applicant['portfolio_url'] }}" class="doc-link" target="_blank">{{ $applicant['portfolio_url'] }}</a>
                        @else
                            <span class="text-muted">Not provided</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px;">Candidate Bio / Executive Summary:</div>
            <div class="text-block-box">{!! nl2br(e($applicant['bio'])) !!}</div>
        </div>
    </div>

    {{-- Section 2: Qualifications & Experience --}}
    <div class="section-box">
        <div class="section-header">2. Qualifications & Professional Experience</div>
        <div class="section-body">
            <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Academic & Professional Credentials:</div>
            <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($qualifications)) !!}</div>

            <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Industry Experience & Pedagogical Track Record:</div>
            <div class="text-block-box">{!! nl2br(e($experience)) !!}</div>
        </div>
    </div>

    {{-- Section 3: Course Proposal Dossier --}}
    <div class="section-box">
        <div class="section-header">
            3. Course Proposal Dossier
            <span style="font-weight: 400; text-transform: none; color: #64748b; margin-left: 6px;">
                ({{ $proposal['type_label'] }})
            </span>
        </div>
        <div class="section-body">
            @if ($proposal['type'] === 'existing')
                {{-- Existing Course Proposal Details --}}
                <table class="meta-grid-table" style="margin-bottom: 6px;">
                    <tr>
                        <td class="meta-label">Selected Course:</td>
                        <td class="meta-val" colspan="3">
                            <strong>{{ $proposal['preferred_course']?->title ?? 'Course ID #' . $application->preferred_course_id }}</strong>
                            @if ($proposal['preferred_course']?->code)
                                <span class="badge badge-gray" style="margin-left: 4px;">{{ $proposal['preferred_course']->code }}</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px;">Motivation Statement:</div>
                <div class="text-block-box" style="margin-bottom: 8px;">
                    {!! nl2br(e($proposal['motivation_note'] ?: 'No motivation statement provided.')) !!}
                </div>

                <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Competence & Teaching Methodology Note:</div>
                <div class="text-block-box">
                    {!! nl2br(e($proposal['competence_note'] ?: 'No competence statement provided.')) !!}
                </div>

            @elseif ($proposal['type'] === 'new')
                {{-- New Course Proposal Details --}}
                <table class="meta-grid-table" style="margin-bottom: 8px;">
                    <tr>
                        <td class="meta-label">Proposed Course Name:</td>
                        <td class="meta-val"><strong>{{ $proposal['proposed_course_name'] ?: 'Untitled Course' }}</strong></td>
                        <td class="meta-label">Proposed Course Code:</td>
                        <td class="meta-val"><code>{{ $proposal['proposed_course_code'] ?: 'PENDING' }}</code></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Teaching Location:</td>
                        <td class="meta-val">{{ $proposal['teaching_location'] }}</td>
                        <td class="meta-label">Estimated Timeline:</td>
                        <td class="meta-val">{{ $proposal['timeline'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Public Enrollment:</td>
                        <td class="meta-val">
                            <span class="badge {{ $proposal['is_open_enrollment'] ? 'badge-success' : 'badge-gray' }}">
                                {{ $proposal['is_open_enrollment'] ? 'Open to Public' : 'Restricted Cohort' }}
                            </span>
                        </td>
                        <td class="meta-label">Curriculum Format:</td>
                        <td class="meta-val">Complete Syllabus Proposal</td>
                    </tr>
                </table>

                @if ($proposal['key_outcome'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px;">Key Learning Outcomes:</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['key_outcome'])) !!}</div>
                @endif

                @if ($proposal['overview'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Executive Course Overview:</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['overview'])) !!}</div>
                @endif

                @if ($proposal['description'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Detailed Course Description:</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['description'])) !!}</div>
                @endif

                @if ($proposal['requirements'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Prerequisites & Student Requirements:</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['requirements'])) !!}</div>
                @endif

                {{-- Structured Fees Table --}}
                @if (!empty($proposal['fees_structured']))
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px; margin-bottom: 3px;">Proposed Fee Structure:</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Delivery Category</th>
                                <th style="width: 25%;">Level Tier</th>
                                <th style="width: 25%;">Proposed Tuition</th>
                                <th style="width: 20%;">Duration / Pacing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proposal['fees_structured'] as $fee)
                                <tr>
                                    <td><strong>{{ $fee['category'] }}</strong></td>
                                    <td>{{ $fee['level'] }}</td>
                                    <td><strong style="color: #0d9488;">{{ $fee['amount'] }}</strong></td>
                                    <td>{{ $fee['duration'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif (filled($proposal['fees_raw']))
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px;">Proposed Fee Structure (Raw):</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['fees_raw'])) !!}</div>
                @endif

                {{-- Structured Progression Table --}}
                @if (!empty($proposal['progression_structured']))
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px; margin-bottom: 3px;">Curriculum Level Progression:</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Progression Tier</th>
                                <th style="width: 75%;">Milestones & Focus Areas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proposal['progression_structured'] as $prog)
                                <tr>
                                    <td><strong>{{ $prog['level'] }}</strong></td>
                                    <td>{{ $prog['details'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif (filled($proposal['progression_raw']))
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 6px;">Curriculum Level Progression (Raw):</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['progression_raw'])) !!}</div>
                @endif

            @else
                {{-- Legacy Application fields --}}
                @if ($proposal['concept_note'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Course Concept Note:</div>
                    <div class="text-block-box" style="margin-bottom: 8px;">{!! nl2br(e($proposal['concept_note'])) !!}</div>
                @endif

                @if ($proposal['curriculum_note'])
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Proposed Curriculum & Outline:</div>
                    <div class="text-block-box">{!! nl2br(e($proposal['curriculum_note'])) !!}</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Section 4: Attached Verification Documents & Files --}}
    <div class="section-box">
        <div class="section-header">4. Attached Verification Documents & Files</div>
        <div class="section-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Document Name</th>
                        <th style="width: 25%;">Document Scope</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 25%;">File Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attachments as $doc)
                        <tr>
                            <td><strong>{{ $doc['title'] }}</strong></td>
                            <td class="text-muted">{{ $doc['category'] }}</td>
                            <td>
                                @if ($doc['attached'])
                                    <span class="badge badge-success">Attached</span>
                                @else
                                    <span class="badge badge-gray">Not Provided</span>
                                @endif
                            </td>
                            <td>
                                @if ($doc['attached'])
                                    <code>{{ $doc['filename'] }}</code>
                                    @if ($doc['size'])
                                        <span class="text-muted">({{ $doc['size'] }})</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 5: Administrative Governance & Audit Sign-off --}}
    <div class="section-box">
        <div class="section-header">5. Administrative Evaluation & Decision</div>
        <div class="section-body">
            <table class="meta-grid-table" style="margin-bottom: 8px;">
                <tr>
                    <td class="meta-label">Current Status:</td>
                    <td class="meta-val">
                        <span class="badge {{ $review['status_badge_class'] }}">
                            {{ $review['status_label'] }}
                        </span>
                    </td>
                    <td class="meta-label">Reviewing Official:</td>
                    <td class="meta-val"><strong>{{ $review['reviewer_name'] }}</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">Evaluation Date:</td>
                    <td class="meta-val">{{ $review['reviewed_at'] }}</td>
                    <td class="meta-label">Linked Account ID:</td>
                    <td class="meta-val">{{ $account['user_id'] ? '#' . $account['user_id'] . ' (' . ($account['is_active'] ? 'Active' : 'Inactive') . ')' : 'None' }}</td>
                </tr>
            </table>

            <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Administrative Evaluation Notes & Feedback:</div>
            <div class="text-block-box">{!! nl2br(e($review['admin_notes'])) !!}</div>

            {{-- Official Audit Sign-off Box --}}
            <div class="signature-box">
                <table style="width: 100%; border-collapse: collapse; font-size: 7pt;">
                    <tr>
                        <td style="width: 60%; vertical-align: top;">
                            <div style="font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">
                                Thinker HUB Accreditation & Faculty Governance
                            </div>
                            <div style="color: #64748b; font-size: 6.5pt; margin-top: 2px;">
                                This record represents the official instructor application submitted through the thinker.HUB academic portal.
                                Content verified for accreditation, course assignment, and instructor onboarding compliance.
                            </div>
                            <div style="margin-top: 15px; color: #475569;">
                                <strong>Reviewed by:</strong> {{ $review['reviewer_name'] }}
                            </div>
                        </td>
                        <td style="width: 40%; text-align: right; vertical-align: bottom;">
                            <div class="sign-line"></div>
                            <div style="font-size: 6.5pt; color: #64748b; margin-top: 3px;">
                                Authorized Administrator Signature / Verification Seal
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
