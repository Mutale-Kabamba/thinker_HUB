@extends('reports.layout')

@section('title', 'Instructor Applications Batch Dossier (' . $totalCount . ' Applications)')
@section('report_type', 'Instructor Applications Consolidated Dossier')

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

    @foreach ($applicationsData as $data)
        @php
            $application = $data['application'];
            $applicant = $data['applicant'];
            $review = $data['review'];
            $proposal = $data['proposal'];
            $account = $data['account'];
            $qualifications = $data['qualifications'];
            $experience = $data['experience'];
            $attachments = $data['attachments'];
            $reference_id = $data['reference_id'];
        @endphp

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

        {{-- Section 1: Candidate Profile & Background --}}
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
                        <td class="meta-label">LinkedIn:</td>
                        <td class="meta-val">{{ $applicant['linkedin_url'] ?: 'Not provided' }}</td>
                        <td class="meta-label">Portfolio:</td>
                        <td class="meta-val">{{ $applicant['portfolio_url'] ?: 'Not provided' }}</td>
                    </tr>
                </table>
                <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Candidate Bio:</div>
                <div class="text-block-box">{!! nl2br(e($applicant['bio'])) !!}</div>
            </div>
        </div>

        {{-- Section 2: Qualifications & Experience --}}
        <div class="section-box">
            <div class="section-header">2. Qualifications & Professional Experience</div>
            <div class="section-body">
                <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Qualifications:</div>
                <div class="text-block-box" style="margin-bottom: 6px;">{!! nl2br(e($qualifications)) !!}</div>

                <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Experience:</div>
                <div class="text-block-box">{!! nl2br(e($experience)) !!}</div>
            </div>
        </div>

        {{-- Section 3: Course Proposal --}}
        <div class="section-box">
            <div class="section-header">3. Course Proposal Dossier ({{ $proposal['type_label'] }})</div>
            <div class="section-body">
                @if ($proposal['type'] === 'existing')
                    <table class="meta-grid-table" style="margin-bottom: 6px;">
                        <tr>
                            <td class="meta-label">Selected Course:</td>
                            <td class="meta-val" colspan="3">
                                <strong>{{ $proposal['preferred_course']?->title ?? 'Course #' . $application->preferred_course_id }}</strong>
                            </td>
                        </tr>
                    </table>
                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Motivation:</div>
                    <div class="text-block-box" style="margin-bottom: 6px;">{!! nl2br(e($proposal['motivation_note'] ?: 'Not provided')) !!}</div>

                    <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Competence:</div>
                    <div class="text-block-box">{!! nl2br(e($proposal['competence_note'] ?: 'Not provided')) !!}</div>
                @elseif ($proposal['type'] === 'new')
                    <table class="meta-grid-table" style="margin-bottom: 6px;">
                        <tr>
                            <td class="meta-label">Course Name:</td>
                            <td class="meta-val"><strong>{{ $proposal['proposed_course_name'] ?: 'Untitled' }}</strong></td>
                            <td class="meta-label">Code:</td>
                            <td class="meta-val"><code>{{ $proposal['proposed_course_code'] ?: 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <td class="meta-label">Location:</td>
                            <td class="meta-val">{{ $proposal['teaching_location'] }}</td>
                            <td class="meta-label">Timeline:</td>
                            <td class="meta-val">{{ $proposal['timeline'] }}</td>
                        </tr>
                    </table>
                    @if ($proposal['overview'])
                        <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Overview:</div>
                        <div class="text-block-box" style="margin-bottom: 6px;">{!! nl2br(e($proposal['overview'])) !!}</div>
                    @endif
                    @if ($proposal['description'])
                        <div style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase;">Description:</div>
                        <div class="text-block-box">{!! nl2br(e($proposal['description'])) !!}</div>
                    @endif
                @else
                    @if ($proposal['concept_note'])
                        <div class="text-block-box">{!! nl2br(e($proposal['concept_note'])) !!}</div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Section 4: Attached Documents --}}
        <div class="section-box">
            <div class="section-header">4. Attached Documents & Status</div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Status</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attachments as $doc)
                            <tr>
                                <td>{{ $doc['title'] }}</td>
                                <td>
                                    @if ($doc['attached'])
                                        <span class="badge badge-success">Attached</span>
                                    @else
                                        <span class="badge badge-gray">Not Provided</span>
                                    @endif
                                </td>
                                <td>{{ $doc['attached'] ? $doc['filename'] : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 5: Admin Evaluation --}}
        <div class="section-box">
            <div class="section-header">5. Administrative Evaluation</div>
            <div class="section-body">
                <table class="meta-grid-table">
                    <tr>
                        <td class="meta-label">Decision:</td>
                        <td class="meta-val"><span class="badge {{ $review['status_badge_class'] }}">{{ $review['status_label'] }}</span></td>
                        <td class="meta-label">Reviewer:</td>
                        <td class="meta-val">{{ $review['reviewer_name'] }} ({{ $review['reviewed_at'] }})</td>
                    </tr>
                </table>
                <div class="text-block-box" style="margin-top: 6px;">{!! nl2br(e($review['admin_notes'])) !!}</div>
            </div>
        </div>

        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
