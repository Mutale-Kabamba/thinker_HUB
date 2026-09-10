<x-filament-panels::page>
    <div class="hub-shell" style="max-width: 100%; padding: 0.25rem 0;">

        {{-- Top Navigation & Action Bar --}}
        <div class="hub-card" style="padding: 0.85rem 1.15rem; border-radius: 12px; margin-bottom: 0.9rem; background: var(--hub-surface); border: 1px solid var(--hub-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--hub-primary); letter-spacing: 0.05em;">
                            Academics & Content
                        </span>
                        <span style="color: var(--hub-border);">•</span>
                        <span style="font-size: 0.72rem; color: var(--hub-muted);">
                            Live Session Register & Tracking
                        </span>
                    </div>
                    <h2 class="hub-title" style="font-size: 1.25rem; font-weight: 800; margin: 0.15rem 0 0 0; color: var(--hub-ink);">
                        Attendance Register
                    </h2>
                </div>

                {{-- Mode Switcher & Export Actions --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: inline-flex; background: var(--hub-surface-soft); padding: 3px; border-radius: 8px; border: 1px solid var(--hub-border);">
                        <button type="button"
                                wire:click="setViewMode('register')"
                                style="font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; border: none; cursor: pointer; background: {{ $viewMode === 'register' ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $viewMode === 'register' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <x-heroicon-o-clipboard-document-check style="width: 0.95rem; height: 0.95rem;" />
                            Markable Register
                        </button>
                        <button type="button"
                                wire:click="setViewMode('overview')"
                                style="font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; border: none; cursor: pointer; background: {{ $viewMode === 'overview' ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $viewMode === 'overview' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <x-heroicon-o-calendar-days style="width: 0.95rem; height: 0.95rem;" />
                            All Sessions
                        </button>
                    </div>

                    @if($this->selectedSession)
                        {{-- Excel Export --}}
                        <button type="button"
                                wire:click="exportExcel"
                                wire:loading.attr="disabled"
                                style="font-size: 0.75rem; padding: 0.35rem 0.75rem; background: var(--hub-surface); border: 1px solid #10b981; color: #047857; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; transition: all 0.15s ease;"
                                title="Export session attendance to Excel (CSV with UTF-8 BOM)">
                            <x-heroicon-o-arrow-down-tray style="width: 0.9rem; height: 0.9rem; color: #10b981;" />
                            <span>Export Excel</span>
                        </button>

                        {{-- PDF Export --}}
                        <button type="button"
                                wire:click="exportPdf"
                                wire:loading.attr="disabled"
                                style="font-size: 0.75rem; padding: 0.35rem 0.75rem; background: var(--hub-surface); border: 1px solid #f43f5e; color: #e11d48; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; transition: all 0.15s ease;"
                                title="Download official printable PDF attendance sheet">
                            <x-heroicon-o-document-arrow-down style="width: 0.9rem; height: 0.9rem; color: #f43f5e;" />
                            <span>Export PDF</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Course & Session Filter Selectors --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.6rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--hub-border);">
                {{-- Course Filter --}}
                <div>
                    <label style="display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-muted); margin-bottom: 0.25rem;">
                        Filter by Course
                    </label>
                    <select wire:model.live="filterCourseId"
                            style="width: 100%; font-size: 0.75rem; padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-ink);">
                        <option value="">All Courses</option>
                        @foreach($this->getAvailableCourses() as $course)
                            <option value="{{ $course->id }}">{{ $course->title }} {{ $course->code ? '('.$course->code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Intake Filter --}}
                <div>
                    <label style="display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-muted); margin-bottom: 0.25rem;">
                        Class / Intake
                    </label>
                    <select wire:model.live="filterIntakeId"
                            style="width: 100%; font-size: 0.75rem; padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-ink);">
                        <option value="">All Cohorts / Classes</option>
                        @foreach($this->getAvailableIntakes() as $intake)
                            <option value="{{ $intake->id }}">{{ $intake->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Active Session Selector --}}
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-primary); margin-bottom: 0.25rem;">
                        Active Session
                    </label>
                    <select wire:change="selectSession($event.target.value)"
                            style="width: 100%; font-size: 0.75rem; padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-primary); background: var(--hub-surface); color: var(--hub-ink); font-weight: 600;">
                        <option value="">-- Choose a session to mark attendance --</option>
                        @foreach($this->sessionsList as $s)
                            <option value="{{ $s->id }}" @selected($selectedSessionId === $s->id)>
                                {{ $s->getEffectiveDate()->format('D, M j, Y') }} ({{ $s->getEffectiveStartTime() }}) • {{ $s->course?->title ?? 'Course' }} — {{ $s->title ?: 'Session' }} [{{ $s->attendances_count }} students]
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($viewMode === 'register')
            @if($session = $this->selectedSession)
                @php
                    $summary = $this->attendanceSummary;
                    $records = $this->registerRecords;
                @endphp

                {{-- Session Header Banner --}}
                <div class="hub-card" style="padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 0.9rem; background: linear-gradient(135deg, var(--hub-surface) 0%, var(--hub-surface-soft) 100%); border: 1px solid var(--hub-border);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                                <span style="background: var(--hub-primary-soft); color: var(--hub-primary); font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; text-transform: uppercase;">
                                    {{ $session->course?->code ?? 'COURSE' }}
                                </span>
                                @if($session->intake)
                                    <span style="background: var(--hub-surface); border: 1px solid var(--hub-border); color: var(--hub-muted); font-size: 0.68rem; font-weight: 600; padding: 2px 8px; border-radius: 6px;">
                                        {{ $session->intake->name }}
                                    </span>
                                @endif
                                <span style="background: {{ $session->status === 'completed' ? '#ecfdf5' : '#eff6ff' }}; color: {{ $session->status === 'completed' ? '#059669' : '#2563eb' }}; font-size: 0.68rem; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                    {{ ucfirst($session->status ?: 'Scheduled') }}
                                </span>
                            </div>

                            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--hub-ink); margin: 0 0 0.35rem 0;">
                                {{ $session->title ?: 'Class Session' }}
                            </h3>

                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; font-size: 0.75rem; color: var(--hub-muted);">
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <x-heroicon-o-academic-cap style="width: 0.85rem; height: 0.85rem; color: var(--hub-primary);" />
                                    {{ $session->course?->title ?? '—' }}
                                </span>
                                <span>•</span>
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <x-heroicon-o-calendar style="width: 0.85rem; height: 0.85rem; color: var(--hub-primary);" />
                                    {{ $session->getEffectiveDate()->format('l, F j, Y') }}
                                </span>
                                <span>•</span>
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <x-heroicon-o-clock style="width: 0.85rem; height: 0.85rem; color: var(--hub-primary);" />
                                    {{ $session->getEffectiveStartTime() }} – {{ $session->getEffectiveEndTime() }}
                                </span>
                                @if($session->instructor)
                                    <span>•</span>
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <x-heroicon-o-user style="width: 0.85rem; height: 0.85rem; color: var(--hub-primary);" />
                                        {{ $session->instructor->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Sync Roster Action --}}
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem;">
                            <button type="button"
                                    wire:click="syncRoster"
                                    wire:loading.attr="disabled"
                                    style="font-size: 0.72rem; font-weight: 600; padding: 0.4rem 0.85rem; background: var(--hub-surface); border: 1px solid var(--hub-primary); color: var(--hub-primary); border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease;"
                                    title="Synchronize attendance register with enrolled student roster">
                                <x-heroicon-o-arrow-path style="width: 0.85rem; height: 0.85rem;" wire:loading.class="animate-spin" />
                                <span>Sync Session Roster</span>
                            </button>
                            <span style="font-size: 0.68rem; color: var(--hub-muted);">
                                Auto-synced with course enrollments
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Attendance Summary Metric Cards --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.65rem; margin-bottom: 0.9rem;">
                    {{-- Total Registered --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-muted);">
                            Registered
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: var(--hub-ink); margin-top: 0.2rem;">
                            {{ $summary['total'] }}
                        </div>
                        <div style="font-size: 0.68rem; color: var(--hub-muted); margin-top: 0.1rem;">
                            Total Expected
                        </div>
                    </div>

                    {{-- Present --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #065f46;">
                            Present
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #059669; margin-top: 0.2rem;">
                            {{ $summary['present'] }}
                        </div>
                        <div style="font-size: 0.68rem; color: #047857; margin-top: 0.1rem;">
                            {{ $summary['total'] > 0 ? round(($summary['present'] / $summary['total']) * 100) : 0 }}% of cohort
                        </div>
                    </div>

                    {{-- Absent --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: #fff1f2; border: 1px solid #fecdd3;">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #9f1239;">
                            Absent
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #e11d48; margin-top: 0.2rem;">
                            {{ $summary['absent'] }}
                        </div>
                        <div style="font-size: 0.68rem; color: #be123c; margin-top: 0.1rem;">
                            {{ $summary['total'] > 0 ? round(($summary['absent'] / $summary['total']) * 100) : 0 }}% of cohort
                        </div>
                    </div>

                    {{-- Late --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: #fffbeb; border: 1px solid #fde68a;">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #92400e;">
                            Late
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #d97706; margin-top: 0.2rem;">
                            {{ $summary['late'] }}
                        </div>
                        <div style="font-size: 0.68rem; color: #b45309; margin-top: 0.1rem;">
                            {{ $summary['total'] > 0 ? round(($summary['late'] / $summary['total']) * 100) : 0 }}% of cohort
                        </div>
                    </div>

                    {{-- Apology --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: #eff6ff; border: 1px solid #bfdbfe;">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #1e40af;">
                            Apology / Excused
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: #2563eb; margin-top: 0.2rem;">
                            {{ $summary['apology'] }}
                        </div>
                        <div style="font-size: 0.68rem; color: #1d4ed8; margin-top: 0.1rem;">
                            Approved Leave
                        </div>
                    </div>

                    {{-- Overall Rate --}}
                    <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                        <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-muted);">
                            Attendance Rate
                        </div>
                        <div style="font-size: 1.35rem; font-weight: 800; color: {{ $summary['attendance_rate'] >= 75 ? '#059669' : ($summary['attendance_rate'] >= 50 ? '#d97706' : '#e11d48') }}; margin-top: 0.2rem;">
                            {{ $summary['attendance_rate'] }}%
                        </div>
                        <div style="background: var(--hub-surface-soft); border-radius: 3px; height: 4px; width: 100%; margin-top: 0.35rem; overflow: hidden;">
                            <div style="background: {{ $summary['attendance_rate'] >= 75 ? '#059669' : ($summary['attendance_rate'] >= 50 ? '#d97706' : '#e11d48') }}; height: 100%; width: {{ $summary['attendance_rate'] }}%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Quick Batch Marking & Student Search Toolbar --}}
                <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 0.75rem; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        {{-- Search Input & Status Filter Pills --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; flex: 1; min-width: 260px;">
                            <div style="position: relative; min-width: 200px; flex: 1;">
                                <input type="text"
                                       wire:model.live.debounce.300ms="searchStudent"
                                       placeholder="Search student by name or email..."
                                       style="width: 100%; font-size: 0.75rem; padding: 0.35rem 0.6rem 0.35rem 1.85rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-ink);" />
                                <x-heroicon-o-magnifying-glass style="position: absolute; left: 0.55rem; top: 50%; transform: translateY(-50%); width: 0.85rem; height: 0.85rem; color: var(--hub-muted);" />
                            </div>

                            <div style="display: inline-flex; background: var(--hub-surface-soft); padding: 2px; border-radius: 6px; border: 1px solid var(--hub-border);">
                                @foreach(['all' => 'All', 'present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'apology' => 'Apology'] as $stKey => $stLabel)
                                    <button type="button"
                                            wire:click="$set('statusFilter', '{{ $stKey }}')"
                                            style="font-size: 0.68rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 4px; border: none; cursor: pointer; background: {{ $statusFilter === $stKey ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $statusFilter === $stKey ? '#ffffff' : 'var(--hub-muted)' }};">
                                        {{ $stLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Batch Action Buttons --}}
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-muted); margin-right: 0.2rem;">
                                Bulk Mark:
                            </span>

                            <button type="button"
                                    wire:click="markAll('present')"
                                    style="font-size: 0.7rem; font-weight: 600; padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid #10b981; background: #ecfdf5; color: #065f46; cursor: pointer;">
                                Mark All Present
                            </button>

                            <button type="button"
                                    wire:click="markAll('absent')"
                                    style="font-size: 0.7rem; font-weight: 600; padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid #f43f5e; background: #fff1f2; color: #9f1239; cursor: pointer;">
                                Mark All Absent
                            </button>

                            <button type="button"
                                    wire:click="markAll('late')"
                                    style="font-size: 0.7rem; font-weight: 600; padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid #f59e0b; background: #fffbeb; color: #92400e; cursor: pointer;">
                                Mark All Late
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Markable Student Register Roster --}}
                <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                            <thead>
                                <tr style="background: var(--hub-surface-soft); border-bottom: 1px solid var(--hub-border); text-align: left;">
                                    <th style="padding: 0.65rem 0.85rem; width: 4%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">#</th>
                                    <th style="padding: 0.65rem 0.85rem; width: 32%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Student</th>
                                    <th style="padding: 0.65rem 0.85rem; width: 38%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700; text-align: center;">Mark Attendance Status</th>
                                    <th style="padding: 0.65rem 0.85rem; width: 26%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Remarks / Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $index => $record)
                                    @php
                                        $student = $record->student;
                                        $status = $record->status;
                                    @endphp
                                    <tr style="border-bottom: 1px solid var(--hub-border); transition: background 0.15s ease;"
                                        onmouseover="this.style.background='var(--hub-surface-soft)'"
                                        onmouseout="this.style.background='transparent'">
                                        
                                        {{-- Row Number --}}
                                        <td style="padding: 0.65rem 0.85rem; color: var(--hub-muted); font-size: 0.7rem;">
                                            {{ $index + 1 }}
                                        </td>

                                        {{-- Student Info --}}
                                        <td style="padding: 0.65rem 0.85rem;">
                                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: var(--hub-primary-soft); color: var(--hub-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                                                    {{ strtoupper(substr($student?->name ?? 'S', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight: 700; color: var(--hub-ink); font-size: 0.78rem;">
                                                        {{ $student?->name ?? 'Student #' . $record->user_id }}
                                                    </div>
                                                    <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                        {{ $student?->email ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Markable Status Buttons --}}
                                        <td style="padding: 0.65rem 0.85rem; text-align: center;">
                                            <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: var(--hub-surface-soft); padding: 3px 4px; border-radius: 8px; border: 1px solid var(--hub-border);">
                                                
                                                {{-- Present Button --}}
                                                <button type="button"
                                                        wire:click="markStatus({{ $record->id }}, 'present')"
                                                        style="font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; cursor: pointer; border: 1px solid {{ $status === 'present' ? '#059669' : 'transparent' }}; background: {{ $status === 'present' ? '#059669' : 'transparent' }}; color: {{ $status === 'present' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.12s ease; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <x-heroicon-m-check style="width: 0.8rem; height: 0.8rem;" />
                                                    Present
                                                </button>

                                                {{-- Absent Button --}}
                                                <button type="button"
                                                        wire:click="markStatus({{ $record->id }}, 'absent')"
                                                        style="font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; cursor: pointer; border: 1px solid {{ $status === 'absent' ? '#e11d48' : 'transparent' }}; background: {{ $status === 'absent' ? '#e11d48' : 'transparent' }}; color: {{ $status === 'absent' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.12s ease; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <x-heroicon-m-x-mark style="width: 0.8rem; height: 0.8rem;" />
                                                    Absent
                                                </button>

                                                {{-- Late Button --}}
                                                <button type="button"
                                                        wire:click="markStatus({{ $record->id }}, 'late')"
                                                        style="font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; cursor: pointer; border: 1px solid {{ $status === 'late' ? '#d97706' : 'transparent' }}; background: {{ $status === 'late' ? '#d97706' : 'transparent' }}; color: {{ $status === 'late' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.12s ease; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <x-heroicon-m-clock style="width: 0.8rem; height: 0.8rem;" />
                                                    Late
                                                </button>

                                                {{-- Apology Button --}}
                                                <button type="button"
                                                        wire:click="markStatus({{ $record->id }}, 'apology')"
                                                        style="font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; cursor: pointer; border: 1px solid {{ $status === 'apology' ? '#2563eb' : 'transparent' }}; background: {{ $status === 'apology' ? '#2563eb' : 'transparent' }}; color: {{ $status === 'apology' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.12s ease; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <x-heroicon-m-chat-bubble-left-ellipsis style="width: 0.8rem; height: 0.8rem;" />
                                                    Apology
                                                </button>
                                            </div>
                                        </td>

                                        {{-- Inline Editable Note --}}
                                        <td style="padding: 0.65rem 0.85rem;">
                                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                <input type="text"
                                                       value="{{ $record->notes }}"
                                                       placeholder="Optional note / reason..."
                                                       wire:change="updateNote({{ $record->id }}, $event.target.value)"
                                                       style="width: 100%; font-size: 0.72rem; padding: 0.3rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink);" />
                                                @if($status === 'present')
                                                    <span title="+25 XP & 8 TC Active Reward" style="color: #059669; font-size: 0.65rem; font-weight: 700; flex-shrink: 0;">
                                                        ✓ XP
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2.5rem 1rem; color: var(--hub-muted);">
                                            <x-heroicon-o-user-group style="width: 2.5rem; height: 2.5rem; margin: 0 auto 0.5rem auto; color: var(--hub-border);" />
                                            <div style="font-size: 0.9rem; font-weight: 700; color: var(--hub-ink);">No student records found</div>
                                            <div style="font-size: 0.75rem; margin-top: 0.25rem;">
                                                Click "Sync Session Roster" above to pull enrolled students into this register.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- No Session Selected Placeholder --}}
                <div class="hub-card" style="padding: 3rem 1.5rem; text-align: center; border-radius: 12px; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                    <x-heroicon-o-clipboard-document-list style="width: 3rem; height: 3rem; margin: 0 auto 0.75rem auto; color: var(--hub-primary);" />
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                        No Session Selected
                    </h3>
                    <p style="font-size: 0.8rem; color: var(--hub-muted); max-width: 450px; margin: 0.4rem auto 1rem auto;">
                        Please select an active session from the dropdown above or switch to "All Sessions" to browse scheduled classes and mark attendance.
                    </p>
                    <button type="button"
                            wire:click="setViewMode('overview')"
                            style="font-size: 0.78rem; font-weight: 700; padding: 0.45rem 1rem; border-radius: 8px; border: none; background: var(--hub-primary); color: #ffffff; cursor: pointer;">
                        Browse All Sessions
                    </button>
                </div>
            @endif

        @else
            {{-- Sessions Overview & Cumulative Matrix Mode --}}
            <div class="hub-card" style="padding: 0.85rem 1.15rem; border-radius: 12px; margin-bottom: 0.9rem; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                            Session Timetable & Attendance Status
                        </h3>
                        <p style="font-size: 0.72rem; color: var(--hub-muted); margin: 0.15rem 0 0 0;">
                            Browse scheduled sessions, inspect attendance completion, or export cumulative cohort reports.
                        </p>
                    </div>

                    @if($filterCourseId)
                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                            <button type="button"
                                    wire:click="exportCourseCumulativeExcel"
                                    style="font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.75rem; background: var(--hub-surface); border: 1px solid #10b981; color: #047857; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <x-heroicon-o-arrow-down-tray style="width: 0.85rem; height: 0.85rem; color: #10b981;" />
                                Course Matrix (Excel)
                            </button>

                            <button type="button"
                                    wire:click="exportCourseCumulativePdf"
                                    style="font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.75rem; background: var(--hub-surface); border: 1px solid #f43f5e; color: #e11d48; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <x-heroicon-o-document-arrow-down style="width: 0.85rem; height: 0.85rem; color: #f43f5e;" />
                                Course Matrix (PDF)
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Date Filter & Search --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--hub-border);">
                    <div style="display: inline-flex; background: var(--hub-surface-soft); padding: 2px; border-radius: 6px; border: 1px solid var(--hub-border);">
                        @foreach(['all' => 'All Dates', 'today' => 'Today', 'upcoming' => 'Upcoming', 'past' => 'Past Sessions'] as $dfKey => $dfLabel)
                            <button type="button"
                                    wire:click="$set('sessionDateFilter', '{{ $dfKey }}')"
                                    style="font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 4px; border: none; cursor: pointer; background: {{ $sessionDateFilter === $dfKey ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $sessionDateFilter === $dfKey ? '#ffffff' : 'var(--hub-muted)' }};">
                                {{ $dfLabel }}
                            </button>
                        @endforeach
                    </div>

                    <div style="position: relative; min-width: 220px; flex: 1;">
                        <input type="text"
                               wire:model.live.debounce.300ms="sessionSearch"
                               placeholder="Search session title or course name..."
                               style="width: 100%; font-size: 0.75rem; padding: 0.35rem 0.6rem 0.35rem 1.85rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface-soft); color: var(--hub-ink);" />
                        <x-heroicon-o-magnifying-glass style="position: absolute; left: 0.55rem; top: 50%; transform: translateY(-50%); width: 0.85rem; height: 0.85rem; color: var(--hub-muted);" />
                    </div>
                </div>
            </div>

            {{-- Sessions List Table --}}
            <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; background: var(--hub-surface); border: 1px solid var(--hub-border);">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                        <thead>
                            <tr style="background: var(--hub-surface-soft); border-bottom: 1px solid var(--hub-border); text-align: left;">
                                <th style="padding: 0.65rem 0.85rem; width: 14%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Date & Time</th>
                                <th style="padding: 0.65rem 0.85rem; width: 22%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Course & Cohort</th>
                                <th style="padding: 0.65rem 0.85rem; width: 24%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700;">Session Details</th>
                                <th style="padding: 0.65rem 0.85rem; width: 16%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700; text-align: center;">Attendance Status</th>
                                <th style="padding: 0.65rem 0.85rem; width: 24%; color: var(--hub-muted); font-size: 0.68rem; text-transform: uppercase; font-weight: 700; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->sessionsList as $sess)
                                @php
                                    $sessSummary = app(\App\Services\AttendanceService::class)->getSessionAttendanceSummary($sess);
                                @endphp
                                <tr style="border-bottom: 1px solid var(--hub-border); transition: background 0.15s ease;"
                                    onmouseover="this.style.background='var(--hub-surface-soft)'"
                                    onmouseout="this.style.background='transparent'">
                                    
                                    {{-- Date & Time --}}
                                    <td style="padding: 0.65rem 0.85rem;">
                                        <div style="font-weight: 700; color: var(--hub-ink);">
                                            {{ $sess->getEffectiveDate()->format('M j, Y') }}
                                        </div>
                                        <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                            {{ $sess->getEffectiveStartTime() }} – {{ $sess->getEffectiveEndTime() }}
                                        </div>
                                    </td>

                                    {{-- Course & Cohort --}}
                                    <td style="padding: 0.65rem 0.85rem;">
                                        <div style="font-weight: 700; color: var(--hub-ink);">
                                            {{ $sess->course?->title ?? '—' }}
                                        </div>
                                        <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                            {{ $sess->intake?->name ?? 'All Enrolled Cohorts' }}
                                        </div>
                                    </td>

                                    {{-- Session Details --}}
                                    <td style="padding: 0.65rem 0.85rem;">
                                        <div style="font-weight: 700; color: var(--hub-ink);">
                                            {{ $sess->title ?: 'Class Session' }}
                                        </div>
                                        <div style="font-size: 0.68rem; color: var(--hub-muted); display: flex; align-items: center; gap: 0.4rem;">
                                            <span>{{ $sess->isOneOnOne() ? '1-on-1' : 'Group' }}</span>
                                            @if($sess->instructor)
                                                <span>•</span>
                                                <span>{{ $sess->instructor->name }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Attendance Summary --}}
                                    <td style="padding: 0.65rem 0.85rem; text-align: center;">
                                        @if($sessSummary['total'] > 0)
                                            <div style="display: inline-block; text-align: center;">
                                                <div style="font-weight: 800; font-size: 0.78rem; color: {{ $sessSummary['attendance_rate'] >= 75 ? '#059669' : ($sessSummary['attendance_rate'] >= 50 ? '#d97706' : '#e11d48') }};">
                                                    {{ $sessSummary['attended_total'] }} / {{ $sessSummary['total'] }} ({{ $sessSummary['attendance_rate'] }}%)
                                                </div>
                                                <div style="font-size: 0.65rem; color: var(--hub-muted);">
                                                    {{ $sessSummary['present'] }}P • {{ $sessSummary['absent'] }}A • {{ $sessSummary['late'] }}L
                                                </div>
                                            </div>
                                        @else
                                            <span style="font-size: 0.68rem; color: var(--hub-muted); background: var(--hub-surface-soft); padding: 2px 6px; border-radius: 4px;">
                                                Not Synced
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Row Actions --}}
                                    <td style="padding: 0.65rem 0.85rem; text-align: right;">
                                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <button type="button"
                                                    wire:click="selectSession({{ $sess->id }})"
                                                    style="font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 6px; border: 1px solid var(--hub-primary); background: var(--hub-primary); color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                <x-heroicon-o-clipboard-document-check style="width: 0.85rem; height: 0.85rem;" />
                                                Mark Register
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: var(--hub-muted);">
                                        No sessions found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
