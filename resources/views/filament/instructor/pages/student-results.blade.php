@php
    $tasksData = $this->getTasksData();
    $students = $this->getStudentsData();
    $kpi = $this->getKpiStats($students);
    $catStats = $tasksData['category_stats'];
@endphp

<x-filament-panels::page>
    <div class="hub-shell" style="max-width: 100%; padding: 0.25rem 0;">
        {{-- Compact Top Navigation & Stats Ribbon --}}
        <div class="hub-card" style="padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 0.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.6rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--hub-primary); letter-spacing: 0.04em;">
                            Grading & Evaluations
                        </span>
                        <span style="color: var(--hub-border);">•</span>
                        <span style="font-size: 0.72rem; color: var(--hub-muted);">
                            {{ count($students) }} Students • {{ $tasksData['totals']['total'] }} Taken Tasks (FILO)
                        </span>
                    </div>
                    <h2 class="hub-title" style="font-size: 1.1rem; margin: 0.15rem 0 0 0;">Evaluation Results</h2>
                </div>

                {{-- Mode Switcher & Export CSV --}}
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <div style="display: inline-flex; background: var(--hub-surface-soft); padding: 2px; border-radius: 8px; border: 1px solid var(--hub-border);">
                        <button type="button"
                                wire:click="setViewMode('tasks')"
                                style="font-size: 0.72rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px; border: none; cursor: pointer; background: {{ $viewMode === 'tasks' ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $viewMode === 'tasks' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.15s ease;">
                            <x-heroicon-o-squares-2x2 style="width: 0.8rem; height: 0.8rem; display: inline-block; vertical-align: -1px;" />
                            Evaluation Cards
                        </button>
                        <button type="button"
                                wire:click="setViewMode('students')"
                                style="font-size: 0.72rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px; border: none; cursor: pointer; background: {{ $viewMode === 'students' ? 'var(--hub-primary)' : 'transparent' }}; color: {{ $viewMode === 'students' ? '#ffffff' : 'var(--hub-muted)' }}; transition: all 0.15s ease;">
                            <x-heroicon-o-users style="width: 0.8rem; height: 0.8rem; display: inline-block; vertical-align: -1px;" />
                            Per Student
                        </button>
                    </div>

                    <button type="button"
                            wire:click="exportCsv"
                            style="font-size: 0.72rem; padding: 0.35rem 0.65rem; background: var(--hub-surface); border: 1px solid var(--hub-border); color: var(--hub-ink); border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 500;">
                        <x-heroicon-o-arrow-down-tray style="width: 0.8rem; height: 0.8rem; color: var(--hub-primary);" />
                        <span class="hub-btn-label">CSV</span>
                    </button>
                </div>
            </div>

            {{-- Compact Inline Stats Ribbon --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.45rem; margin-top: 0.65rem; padding-top: 0.5rem; border-top: 1px solid var(--hub-border); font-size: 0.72rem;">
                <div style="display: inline-flex; align-items: center; gap: 0.25rem; background: var(--hub-surface-soft); padding: 0.2rem 0.5rem; border-radius: 6px;">
                    <span style="color: var(--hub-muted);">Class Avg:</span>
                    <strong style="color: {{ $kpi['avg_overall'] !== null ? ($kpi['avg_overall'] >= 75 ? '#059669' : ($kpi['avg_overall'] >= 50 ? '#d97706' : '#dc2626')) : 'var(--hub-ink)' }};">
                        {{ $kpi['avg_overall'] !== null ? $kpi['avg_overall'].'%' : '—' }}
                    </strong>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 0.25rem; background: var(--hub-surface-soft); padding: 0.2rem 0.5rem; border-radius: 6px;">
                    <span style="color: var(--hub-muted);">Task Order:</span>
                    <strong style="color: var(--hub-primary);">FILO (Most Recent Taken First)</strong>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 0.25rem; background: var(--hub-surface-soft); padding: 0.2rem 0.5rem; border-radius: 6px;">
                    <span style="color: var(--hub-muted);">Student Rank:</span>
                    <strong style="color: #059669;">DEC (Highest Score First)</strong>
                </div>
            </div>
        </div>

        {{-- Minimal Search & Filter Bar --}}
        <div class="hub-card" style="padding: 0.6rem 0.75rem; border-radius: 12px; margin-bottom: 0.75rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center; justify-content: space-between;">
                {{-- Search Box --}}
                <div style="flex: 1; min-width: 180px; position: relative;">
                    <div style="position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--hub-muted);">
                        <x-heroicon-o-magnifying-glass style="width: 0.85rem; height: 0.85rem;" />
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search student or taken task..."
                           style="width: 100%; padding: 0.35rem 0.6rem 0.35rem 1.9rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.76rem;" />
                </div>

                {{-- Filters --}}
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                    {{-- Course Filter --}}
                    <select wire:model.live="courseFilter"
                            style="padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.74rem; cursor: pointer; max-width: 140px;">
                        <option value="">All Courses</option>
                        @foreach ($this->courseOptions as $cId => $cTitle)
                            <option value="{{ $cId }}">{{ $cTitle }}</option>
                        @endforeach
                    </select>

                    {{-- Track Filter --}}
                    <select wire:model.live="trackFilter"
                            style="padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.74rem; cursor: pointer;">
                        <option value="">All Levels</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>

                    @if(!empty($search) || !empty($courseFilter) || !empty($trackFilter) || $activeCategory !== 'quizzes')
                        <button type="button"
                                wire:click="resetFilters"
                                style="padding: 0.35rem 0.5rem; font-size: 0.72rem; background: var(--hub-surface-soft); border: 1px solid var(--hub-border); border-radius: 6px; color: var(--hub-muted); cursor: pointer;"
                                title="Reset filters">
                            <x-heroicon-o-x-mark style="width: 0.75rem; height: 0.75rem; display: inline-block; vertical-align: -1px;" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== VIEW 1: 3 HORIZONTAL CATEGORY CARDS (TAKEN TASKS ONLY • FILO ORDER) ==================== --}}
        @if($viewMode === 'tasks')
            {{-- 3 Horizontal Cards Container --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.75rem; margin-bottom: 0.85rem;">
                {{-- 1. ALL QUIZZES CARD --}}
                @php $isQuizActive = ($activeCategory === 'quizzes' || $activeCategory === 'all'); @endphp
                <div wire:click="selectCategory('quizzes')"
                     style="cursor: pointer; border-radius: 12px; padding: 0.85rem 1rem; border: 2px solid {{ $isQuizActive ? '#10b981' : 'var(--hub-border)' }}; background: {{ $isQuizActive ? 'rgba(16, 185, 129, 0.04)' : 'var(--hub-card)' }}; box-shadow: {{ $isQuizActive ? '0 4px 18px rgba(16, 185, 129, 0.18)' : 'none' }}; transition: all 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #047857;">
                                <x-heroicon-o-academic-cap style="width: 1rem; height: 1rem;" />
                            </span>
                            <strong style="font-size: 0.88rem; color: var(--hub-ink);">Quizzes</strong>
                        </div>
                        <span class="hub-chip" style="font-size: 0.62rem; padding: 0.1rem 0.4rem; background: rgba(16, 185, 129, 0.15); color: #047857; font-weight: 700;">
                            {{ $catStats['quizzes']['count'] }} Taken
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 0.45rem 0 0.35rem 0;">
                        <div>
                            <span style="font-size: 0.66rem; color: var(--hub-muted); display: block;">Quiz Avg</span>
                            <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;">
                                {{ $catStats['quizzes']['avg_score'] !== null ? $catStats['quizzes']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div style="text-align: right; font-size: 0.72rem;">
                            <span style="color: var(--hub-muted);">Attempts:</span>
                            <strong style="color: var(--hub-ink);">{{ $catStats['quizzes']['attempts'] }}</strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.68rem; padding-top: 0.4rem; border-top: 1px solid var(--hub-border); color: var(--hub-muted);">
                        <span>Passed: <strong style="color: #059669;">{{ $catStats['quizzes']['passed'] }}</strong></span>
                        <span style="font-weight: 700; color: {{ $isQuizActive ? '#059669' : 'var(--hub-muted)' }};">
                            {{ $isQuizActive ? '▲ Viewing Taken' : '▼ Click to Open' }}
                        </span>
                    </div>
                </div>

                {{-- 2. ALL ASSIGNMENTS CARD --}}
                @php $isAssignActive = ($activeCategory === 'assignments' || $activeCategory === 'all'); @endphp
                <div wire:click="selectCategory('assignments')"
                     style="cursor: pointer; border-radius: 12px; padding: 0.85rem 1rem; border: 2px solid {{ $isAssignActive ? '#6366f1' : 'var(--hub-border)' }}; background: {{ $isAssignActive ? 'rgba(99, 102, 241, 0.04)' : 'var(--hub-card)' }}; box-shadow: {{ $isAssignActive ? '0 4px 18px rgba(99, 102, 241, 0.18)' : 'none' }}; transition: all 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; border-radius: 8px; background: rgba(99, 102, 241, 0.15); color: #4338ca;">
                                <x-heroicon-o-document-text style="width: 1rem; height: 1rem;" />
                            </span>
                            <strong style="font-size: 0.88rem; color: var(--hub-ink);">Assignments</strong>
                        </div>
                        <span class="hub-chip" style="font-size: 0.62rem; padding: 0.1rem 0.4rem; background: rgba(99, 102, 241, 0.15); color: #4338ca; font-weight: 700;">
                            {{ $catStats['assignments']['count'] }} Taken
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 0.45rem 0 0.35rem 0;">
                        <div>
                            <span style="font-size: 0.66rem; color: var(--hub-muted); display: block;">Assignment Avg</span>
                            <span style="font-size: 1.25rem; font-weight: 800; color: #6366f1;">
                                {{ $catStats['assignments']['avg_score'] !== null ? $catStats['assignments']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div style="text-align: right; font-size: 0.72rem;">
                            <span style="color: var(--hub-muted);">Submissions:</span>
                            <strong style="color: var(--hub-ink);">{{ $catStats['assignments']['submissions'] }}</strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.68rem; padding-top: 0.4rem; border-top: 1px solid var(--hub-border); color: var(--hub-muted);">
                        <span>Graded: <strong style="color: #6366f1;">{{ $catStats['assignments']['graded'] }}</strong></span>
                        <span style="font-weight: 700; color: {{ $isAssignActive ? '#6366f1' : 'var(--hub-muted)' }};">
                            {{ $isAssignActive ? '▲ Viewing Taken' : '▼ Click to Open' }}
                        </span>
                    </div>
                </div>

                {{-- 3. ALL ASSESSMENTS CARD --}}
                @php $isAssessActive = ($activeCategory === 'assessments' || $activeCategory === 'all'); @endphp
                <div wire:click="selectCategory('assessments')"
                     style="cursor: pointer; border-radius: 12px; padding: 0.85rem 1rem; border: 2px solid {{ $isAssessActive ? '#8b5cf6' : 'var(--hub-border)' }}; background: {{ $isAssessActive ? 'rgba(139, 92, 246, 0.04)' : 'var(--hub-card)' }}; box-shadow: {{ $isAssessActive ? '0 4px 18px rgba(139, 92, 246, 0.18)' : 'none' }}; transition: all 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; border-radius: 8px; background: rgba(139, 92, 246, 0.15); color: #6d28d9;">
                                <x-heroicon-o-clipboard-document-check style="width: 1rem; height: 1rem;" />
                            </span>
                            <strong style="font-size: 0.88rem; color: var(--hub-ink);">Assessments</strong>
                        </div>
                        <span class="hub-chip" style="font-size: 0.62rem; padding: 0.1rem 0.4rem; background: rgba(139, 92, 246, 0.15); color: #6d28d9; font-weight: 700;">
                            {{ $catStats['assessments']['count'] }} Taken
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 0.45rem 0 0.35rem 0;">
                        <div>
                            <span style="font-size: 0.66rem; color: var(--hub-muted); display: block;">Assessment Avg</span>
                            <span style="font-size: 1.25rem; font-weight: 800; color: #8b5cf6;">
                                {{ $catStats['assessments']['avg_score'] !== null ? $catStats['assessments']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div style="text-align: right; font-size: 0.72rem;">
                            <span style="color: var(--hub-muted);">Submissions:</span>
                            <strong style="color: var(--hub-ink);">{{ $catStats['assessments']['submissions'] }}</strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.68rem; padding-top: 0.4rem; border-top: 1px solid var(--hub-border); color: var(--hub-muted);">
                        <span>Graded: <strong style="color: #8b5cf6;">{{ $catStats['assessments']['graded'] }}</strong></span>
                        <span style="font-weight: 700; color: {{ $isAssessActive ? '#8b5cf6' : 'var(--hub-muted)' }};">
                            {{ $isAssessActive ? '▲ Viewing Taken' : '▼ Click to Open' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ==================== OPENED FULL CATEGORY DETAILS SECTION (COLLAPSIBLE TASKS IN FILO ORDER) ==================== --}}
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- 1. FULL QUIZZES DETAIL (FILO ORDER) --}}
                @if($isQuizActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #10b981;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(16, 185, 129, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-academic-cap style="width: 1.1rem; height: 1.1rem; color: #047857;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    Quizzes Taken (FILO Order • Student Scores in DEC Order)
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['quizzes']) }} Taken • {{ $catStats['quizzes']['attempts'] }} Total Attempts
                            </span>
                        </div>

                        @if(count($tasksData['quizzes']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No quizzes have been taken yet. Once students take a quiz, it will appear here in FILO order.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['quizzes'] as $q)
                                    @php $isTaskOpen = $this->isTaskExpanded($q['key']); @endphp
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Quiz Collapsible Header --}}
                                        <div wire:click="toggleTask('{{ $q['key'] }}')"
                                             style="padding: 0.6rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; cursor: pointer; transition: background 0.15s ease;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                                                <span style="color: var(--hub-primary);">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down style="width: 1rem; height: 1rem;" />
                                                    @else
                                                        <x-heroicon-o-chevron-right style="width: 1rem; height: 1rem;" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                        <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $q['title'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $q['course_code'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(16, 185, 129, 0.12); color: #047857;">Taken</span>
                                                    </div>
                                                    <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                        {{ $q['course'] }} • Pass: {{ $q['pass_percentage'] }}% • {{ $q['results_count'] }} Attempts
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.72rem;">
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Average:</span>
                                                    <strong style="color: {{ $q['avg_score'] >= 70 ? '#059669' : ($q['avg_score'] >= 50 ? '#d97706' : '#dc2626') }};">
                                                        {{ $q['avg_score'] !== null ? $q['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Passed:</span>
                                                    <strong style="color: #059669;">{{ $q['passed_count'] }}/{{ $q['results_count'] }}</strong>
                                                </div>
                                                <span style="font-size: 0.68rem; color: var(--hub-primary); font-weight: 600;">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Attempts List in DEC Order (Collapsible) --}}
                                        @if($isTaskOpen)
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($q['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.8rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
                                                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.3rem; height: 1.3rem; border-radius: 9999px; font-size: 0.64rem; font-weight: 800; background: {{ match($idx) {
                                                                0 => 'linear-gradient(135deg, #f59e0b, #d97706)',
                                                                1 => 'linear-gradient(135deg, #94a3b8, #64748b)',
                                                                2 => 'linear-gradient(135deg, #b45309, #78350f)',
                                                                default => 'var(--hub-surface-soft)'
                                                            } }}; color: {{ $idx < 3 ? '#ffffff' : 'var(--hub-muted)' }}; border: 1px solid var(--hub-border);">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div style="min-width: 0;">
                                                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                                    <span style="font-weight: 700; color: var(--hub-ink);">{{ $res['student_name'] }}</span>
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.3rem;">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div style="font-size: 0.66rem; color: var(--hub-muted);">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div style="display: flex; align-items: center; gap: 0.55rem; text-align: right; flex-wrap: wrap;">
                                                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                                                {{ $res['score'] ?? 0 }}/{{ $res['total_points'] ?? '—' }} pts
                                                            </span>

                                                            <span style="font-size: 0.88rem; font-weight: 800; min-width: 46px; color: {{ $res['percentage'] >= 70 ? '#059669' : ($res['percentage'] >= 50 ? '#d97706' : '#dc2626') }};">
                                                                {{ $res['percentage'] !== null ? $res['percentage'].'%' : '—' }}
                                                            </span>

                                                            <span class="hub-chip {{ $res['passed'] ? 'hub-chip-green' : 'hub-chip-danger' }}" style="font-size: 0.6rem; padding: 0.08rem 0.35rem;">
                                                                {{ $res['passed'] ? 'Passed' : 'Failed' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.08rem 0.3rem; background: rgba(59, 130, 246, 0.12); color: #1d4ed8; font-weight: 700;" title="2nd Attempt (Capped at pass mark {{ $q['pass_percentage'] ?? 50 }}%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            <button type="button" wire:click="viewQuizAttempt({{ $res['attempt_id'] }})"
                                                                    style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: var(--hub-surface-soft); color: var(--hub-ink); border: 1px solid var(--hub-border); border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;"
                                                                    title="View questions, student selected options, and submitted answers">
                                                                <x-heroicon-o-eye style="width: 0.75rem; height: 0.75rem; color: var(--hub-primary);" />
                                                                View Answers
                                                            </button>

                                                            @if(!empty($res['retake_allowed']))
                                                                <button type="button" wire:click="revokeQuizRetake({{ $res['student_id'] }}, {{ $q['id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: rgba(16, 185, 129, 0.15); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 6px; cursor: pointer; font-weight: 700;"
                                                                        title="Click to revoke 2nd try permission">
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="grantQuizRetake({{ $res['student_id'] }}, {{ $q['id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: var(--hub-surface); color: var(--hub-primary); border: 1px solid var(--hub-border); border-radius: 6px; cursor: pointer; font-weight: 600;"
                                                                        title="Allow student to take this quiz again (score capped at pass mark)">
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span style="font-size: 0.66rem; color: var(--hub-muted); min-width: 75px;">
                                                                {{ $res['completed_at'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 2. FULL ASSIGNMENTS DETAIL (FILO ORDER) --}}
                @if($isAssignActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #6366f1;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(99, 102, 241, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-document-text style="width: 1.1rem; height: 1.1rem; color: #4338ca;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    Assignments Taken (FILO Order • Student Grades in DEC Order)
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['assignments']) }} Taken • {{ $catStats['assignments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assignments']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No assignments have been submitted yet. Once students submit an assignment, it will appear here in FILO order.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['assignments'] as $a)
                                    @php $isTaskOpen = $this->isTaskExpanded($a['key']); @endphp
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Assignment Collapsible Header --}}
                                        <div wire:click="toggleTask('{{ $a['key'] }}')"
                                             style="padding: 0.6rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; cursor: pointer; transition: background 0.15s ease;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                                                <span style="color: var(--hub-primary);">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down style="width: 1rem; height: 1rem;" />
                                                    @else
                                                        <x-heroicon-o-chevron-right style="width: 1rem; height: 1rem;" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                        <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $a['title'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $a['course_code'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(99, 102, 241, 0.12); color: #4338ca;">Taken</span>
                                                    </div>
                                                    <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                        {{ $a['course'] }} • Due: {{ $a['due_date'] }} • {{ $a['results_count'] }} Submissions
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.72rem;">
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Average:</span>
                                                    <strong style="color: {{ $a['avg_score'] >= 70 ? '#059669' : ($a['avg_score'] >= 50 ? '#d97706' : '#dc2626') }};">
                                                        {{ $a['avg_score'] !== null ? $a['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Graded:</span>
                                                    <strong style="color: #6366f1;">{{ $a['graded_count'] }}/{{ $a['results_count'] }}</strong>
                                                </div>
                                                <span style="font-size: 0.68rem; color: var(--hub-primary); font-weight: 600;">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Submissions List in DEC Order (Collapsible) --}}
                                        @if($isTaskOpen)
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($a['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.8rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
                                                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.3rem; height: 1.3rem; border-radius: 9999px; font-size: 0.64rem; font-weight: 800; background: {{ match($idx) {
                                                                0 => 'linear-gradient(135deg, #f59e0b, #d97706)',
                                                                1 => 'linear-gradient(135deg, #94a3b8, #64748b)',
                                                                2 => 'linear-gradient(135deg, #b45309, #78350f)',
                                                                default => 'var(--hub-surface-soft)'
                                                            } }}; color: {{ $idx < 3 ? '#ffffff' : 'var(--hub-muted)' }}; border: 1px solid var(--hub-border);">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div style="min-width: 0;">
                                                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                                    <span style="font-weight: 700; color: var(--hub-ink);">{{ $res['student_name'] }}</span>
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.3rem;">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div style="font-size: 0.66rem; color: var(--hub-muted);">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                                @if(!empty($res['feedback']))
                                                                    <div style="font-size: 0.66rem; color: var(--hub-muted); font-style: italic;">
                                                                        "{{ \Illuminate\Support\Str::limit($res['feedback'], 50) }}"
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div style="display: flex; align-items: center; gap: 0.55rem; text-align: right; flex-wrap: wrap;">
                                                            <span style="font-size: 0.88rem; font-weight: 800; min-width: 46px; color: {{ $res['grade'] !== null ? ($res['grade'] >= 70 ? '#059669' : ($res['grade'] >= 50 ? '#d97706' : '#dc2626')) : 'var(--hub-muted)' }};">
                                                                {{ $res['grade'] !== null ? $res['grade'].'%' : 'Ungraded' }}
                                                            </span>

                                                            <span class="hub-chip {{ ($res['status'] ?? '') === 'Graded' ? 'hub-chip-green' : 'hub-chip-amber' }}" style="font-size: 0.6rem; padding: 0.08rem 0.35rem;">
                                                                {{ $res['status'] ?? 'Submitted' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.08rem 0.3rem; background: rgba(99, 102, 241, 0.12); color: #4338ca; font-weight: 700;" title="2nd Attempt (Capped at 50%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            @if(!empty($res['retake_allowed']))
                                                                <button type="button" wire:click="revokeAssignmentRetake({{ $res['submission_id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: rgba(99, 102, 241, 0.15); color: #4338ca; border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 6px; cursor: pointer; font-weight: 700;"
                                                                        title="Click to revoke 2nd try permission">
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="grantAssignmentRetake({{ $res['submission_id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: var(--hub-surface); color: #4338ca; border: 1px solid var(--hub-border); border-radius: 6px; cursor: pointer; font-weight: 600;"
                                                                        title="Allow student to submit a second attempt (grade capped at 50%)">
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span style="font-size: 0.66rem; color: var(--hub-muted); min-width: 75px;">
                                                                {{ $res['submitted_at'] }}
                                                            </span>

                                                            @if(!empty($res['submission_id']))
                                                                <a href="{{ route('filament.instructor.resources.assignment-submission-resource.assignment-submissions.edit', ['record' => $res['submission_id']]) }}"
                                                                   style="font-size: 0.68rem; padding: 0.2rem 0.45rem; background: var(--hub-surface); border: 1px solid var(--hub-border); border-radius: 4px; color: var(--hub-primary); text-decoration: none; font-weight: 600;">
                                                                    Grade / Review
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 3. FULL ASSESSMENTS DETAIL (FILO ORDER) --}}
                @if($isAssessActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #8b5cf6;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(139, 92, 246, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-clipboard-document-check style="width: 1.1rem; height: 1.1rem; color: #6d28d9;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    Assessments Taken (FILO Order • Student Scores in DEC Order)
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['assessments']) }} Taken • {{ $catStats['assessments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assessments']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No assessments have been submitted yet. Once students submit an assessment, it will appear here in FILO order.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['assessments'] as $as)
                                    @php $isTaskOpen = $this->isTaskExpanded($as['key']); @endphp
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Assessment Collapsible Header --}}
                                        <div wire:click="toggleTask('{{ $as['key'] }}')"
                                             style="padding: 0.6rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; cursor: pointer; transition: background 0.15s ease;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                                                <span style="color: var(--hub-primary);">
                                                    @if($isTaskOpen)
                                                        <x-heroicon-o-chevron-down style="width: 1rem; height: 1rem;" />
                                                    @else
                                                        <x-heroicon-o-chevron-right style="width: 1rem; height: 1rem;" />
                                                    @endif
                                                </span>
                                                <div>
                                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                        <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $as['title'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $as['course_code'] }}</span>
                                                        <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(139, 92, 246, 0.12); color: #6d28d9;">Taken</span>
                                                    </div>
                                                    <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                        {{ $as['course'] }} • Due: {{ $as['due_date'] }} • {{ $as['results_count'] }} Submissions
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.72rem;">
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Average:</span>
                                                    <strong style="color: {{ $as['avg_score'] >= 70 ? '#059669' : ($as['avg_score'] >= 50 ? '#d97706' : '#dc2626') }};">
                                                        {{ $as['avg_score'] !== null ? $as['avg_score'].'%' : '—' }}
                                                    </strong>
                                                </div>
                                                <div style="background: var(--hub-surface); border: 1px solid var(--hub-border); padding: 0.15rem 0.45rem; border-radius: 6px;">
                                                    <span style="color: var(--hub-muted); font-size: 0.62rem;">Graded:</span>
                                                    <strong style="color: #8b5cf6;">{{ $as['graded_count'] }}/{{ $as['results_count'] }}</strong>
                                                </div>
                                                <span style="font-size: 0.68rem; color: var(--hub-primary); font-weight: 600;">
                                                    {{ $isTaskOpen ? '▲ Collapse' : '▼ Expand' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Student Submissions List in DEC Order (Collapsible) --}}
                                        @if($isTaskOpen)
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($as['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.8rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
                                                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.3rem; height: 1.3rem; border-radius: 9999px; font-size: 0.64rem; font-weight: 800; background: {{ match($idx) {
                                                                0 => 'linear-gradient(135deg, #f59e0b, #d97706)',
                                                                1 => 'linear-gradient(135deg, #94a3b8, #64748b)',
                                                                2 => 'linear-gradient(135deg, #b45309, #78350f)',
                                                                default => 'var(--hub-surface-soft)'
                                                            } }}; color: {{ $idx < 3 ? '#ffffff' : 'var(--hub-muted)' }}; border: 1px solid var(--hub-border);">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            <div style="min-width: 0;">
                                                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                                    <span style="font-weight: 700; color: var(--hub-ink);">{{ $res['student_name'] }}</span>
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.3rem;">{{ $res['student_track'] }}</span>
                                                                </div>
                                                                <div style="font-size: 0.66rem; color: var(--hub-muted);">
                                                                    {{ $res['student_email'] }}
                                                                </div>
                                                                @if(!empty($res['feedback']))
                                                                    <div style="font-size: 0.66rem; color: var(--hub-muted); font-style: italic;">
                                                                        "{{ \Illuminate\Support\Str::limit($res['feedback'], 50) }}"
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div style="display: flex; align-items: center; gap: 0.55rem; text-align: right; flex-wrap: wrap;">
                                                            <span style="font-size: 0.88rem; font-weight: 800; min-width: 46px; color: {{ $res['score'] !== null ? ($res['score'] >= 70 ? '#059669' : ($res['score'] >= 50 ? '#d97706' : '#dc2626')) : 'var(--hub-muted)' }};">
                                                                {{ $res['score'] !== null ? $res['score'].'%' : 'Ungraded' }}
                                                            </span>

                                                            <span class="hub-chip {{ ($res['status'] ?? '') === 'Graded' ? 'hub-chip-green' : 'hub-chip-amber' }}" style="font-size: 0.6rem; padding: 0.08rem 0.35rem;">
                                                                {{ $res['status'] ?? 'Submitted' }}
                                                            </span>

                                                            @if(!empty($res['is_retake']))
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.08rem 0.3rem; background: rgba(14, 165, 233, 0.12); color: #0369a1; font-weight: 700;" title="2nd Attempt (Capped at 50%)">
                                                                    2nd Try
                                                                </span>
                                                            @endif

                                                            @if(!empty($res['retake_allowed']))
                                                                <button type="button" wire:click="revokeAssessmentRetake({{ $res['submission_id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: rgba(14, 165, 233, 0.15); color: #0369a1; border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; cursor: pointer; font-weight: 700;"
                                                                        title="Click to revoke 2nd try permission">
                                                                    ⭐ 2nd Try Granted (Revoke)
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="grantAssessmentRetake({{ $res['submission_id'] }})"
                                                                        style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: var(--hub-surface); color: #0369a1; border: 1px solid var(--hub-border); border-radius: 6px; cursor: pointer; font-weight: 600;"
                                                                        title="Allow student to submit a second attempt (score capped at 50%)">
                                                                    + Grant 2nd Try
                                                                </button>
                                                            @endif

                                                            <span style="font-size: 0.66rem; color: var(--hub-muted); min-width: 75px;">
                                                                {{ $res['submitted_at'] }}
                                                            </span>

                                                            @if(!empty($res['submission_id']))
                                                                <a href="{{ route('filament.instructor.resources.assessment-submission-resource.assessment-submissions.edit', ['record' => $res['submission_id']]) }}"
                                                                   style="font-size: 0.68rem; padding: 0.2rem 0.45rem; background: var(--hub-surface); border: 1px solid var(--hub-border); border-radius: 4px; color: var(--hub-primary); text-decoration: none; font-weight: 600;">
                                                                    Grade / Review
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        {{-- ==================== VIEW 2: PER STUDENT (CONSOLIDATED VIEW) ==================== --}}
        @else
            <div class="hub-card" style="padding: 0; overflow: hidden; border-radius: 10px; border: 1px solid var(--hub-border);">
                @if(count($students) === 0)
                    <div style="padding: 2rem 1rem; text-align: center;">
                        <x-heroicon-o-users style="width: 2rem; height: 2rem; color: var(--hub-muted); margin: 0 auto 0.5rem auto;" />
                        <p style="font-size: 0.84rem; font-weight: 600; color: var(--hub-ink); margin: 0;">No students found</p>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @foreach ($students as $idx => $row)
                            @php $isExpanded = !empty($this->expandedStudents[$row['id']]); @endphp
                            <div style="border-bottom: 1px solid var(--hub-border); background: {{ $isExpanded ? 'var(--hub-surface-soft)' : ($idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)') }};">
                                {{-- Student Summary Row --}}
                                <div style="padding: 0.55rem 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                        <button type="button"
                                                wire:click="toggleExpand({{ $row['id'] }})"
                                                style="background: none; border: none; cursor: pointer; color: var(--hub-muted); padding: 0.15rem;">
                                            @if($isExpanded)
                                                <x-heroicon-o-chevron-down style="width: 0.95rem; height: 0.95rem; color: var(--hub-primary);" />
                                            @else
                                                <x-heroicon-o-chevron-right style="width: 0.95rem; height: 0.95rem;" />
                                            @endif
                                        </button>

                                        <div style="min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                <span style="font-weight: 700; font-size: 0.84rem; color: var(--hub-ink);">{{ $row['name'] }}</span>
                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.3rem;">{{ $row['track'] }}</span>

                                                {{-- Overall Course Completion Badge --}}
                                                @if(!empty($row['all_courses_completed']))
                                                    <span class="hub-chip hub-chip-green" style="font-size: 0.58rem; padding: 0.05rem 0.3rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                                        <x-heroicon-s-check-circle style="width: 0.65rem; height: 0.65rem;" /> Completed
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                {{ $row['email'] }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mini Scores Breakdown & Course Completion Actions --}}
                                    <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.72rem; flex-wrap: wrap;">
                                        <div style="text-align: center;">
                                            <span style="color: var(--hub-muted); font-size: 0.64rem; display: block;">Quiz</span>
                                            <strong style="color: #10b981;">{{ $row['avg_quiz_score'] !== null ? $row['avg_quiz_score'].'%' : '—' }}</strong>
                                        </div>
                                        <div style="text-align: center;">
                                            <span style="color: var(--hub-muted); font-size: 0.64rem; display: block;">Assign</span>
                                            <strong style="color: #6366f1;">{{ $row['avg_assignment_grade'] !== null ? $row['avg_assignment_grade'].'%' : '—' }}</strong>
                                        </div>
                                        <div style="text-align: center;">
                                            <span style="color: var(--hub-muted); font-size: 0.64rem; display: block;">Assess</span>
                                            <strong style="color: #8b5cf6;">{{ $row['avg_assessment_score'] !== null ? $row['avg_assessment_score'].'%' : '—' }}</strong>
                                        </div>

                                        {{-- Overall Score --}}
                                        <div style="text-align: right; min-width: 55px;">
                                            <span style="color: var(--hub-muted); font-size: 0.64rem; display: block;">Overall</span>
                                            <span style="font-size: 0.88rem; font-weight: 800; color: {{ match($row['tier_key']) {
                                                'distinction' => '#059669',
                                                'merit' => '#0284c7',
                                                'pass' => '#d97706',
                                                default => '#dc2626'
                                            } }};">
                                                {{ $row['overall_score'] !== null ? $row['overall_score'].'%' : '—' }}
                                            </span>
                                        </div>

                                        {{-- Quick Mark Complete Buttons (Per Enrolled Course) --}}
                                        @foreach ($row['courses'] as $c)
                                            @if($c['is_completed'])
                                                <span class="hub-chip hub-chip-green" style="font-size: 0.64rem; padding: 0.2rem 0.45rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                    <x-heroicon-s-check-circle style="width: 0.75rem; height: 0.75rem;" />
                                                    {{ $c['code'] ?: 'Course' }}: Ready
                                                </span>
                                            @else
                                                <button type="button"
                                                        wire:click="markCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                        style="font-size: 0.68rem; padding: 0.22rem 0.5rem; background: #059669; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.2rem; transition: background 0.15s ease;"
                                                        onmouseover="this.style.background='#047857'"
                                                        onmouseout="this.style.background='#059669'"
                                                        title="Mark course as completed to release certificate for {{ $row['name'] }}">
                                                    <x-heroicon-s-check style="width: 0.75rem; height: 0.75rem;" />
                                                    Complete {{ $c['code'] ?: 'Course' }}
                                                </button>
                                            @endif
                                        @endforeach

                                        <button type="button"
                                                wire:click="openAwardModal({{ $row['id'] }})"
                                                style="font-size: 0.68rem; padding: 0.22rem 0.5rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.2rem; transition: opacity 0.15s ease;"
                                                title="Award XP Points & Badges for off-platform activities (Presentations, Hackathons, Debate, etc.)">
                                            <x-heroicon-s-sparkles style="width: 0.75rem; height: 0.75rem;" />
                                            Award XP/Badge
                                        </button>

                                        <button type="button"
                                                wire:click="toggleExpand({{ $row['id'] }})"
                                                style="font-size: 0.68rem; padding: 0.2rem 0.45rem; background: var(--hub-surface); border: 1px solid var(--hub-border); border-radius: 4px; color: var(--hub-ink); cursor: pointer;">
                                            {{ $isExpanded ? 'Hide' : 'Details' }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Expanded Student Breakdown (DEC Order & Course Completion Card) --}}
                                @if($isExpanded)
                                    <div style="padding: 0.6rem 0.85rem 0.75rem 2.2rem; background: var(--hub-surface); border-top: 1px solid var(--hub-border);">
                                        
                                        {{-- Course Completion & Certificate Section --}}
                                        <div style="margin-bottom: 0.75rem; padding: 0.55rem 0.75rem; background: var(--hub-surface-soft); border-radius: 8px; border: 1px solid var(--hub-border);">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.35rem;">
                                                <span style="font-size: 0.7rem; font-weight: 700; color: var(--hub-primary); text-transform: uppercase; letter-spacing: 0.03em;">
                                                    Course Completion &amp; Certificate Readiness
                                                </span>
                                                <span style="font-size: 0.65rem; color: var(--hub-muted);">
                                                    Certificates are unlocked once instructor clicks completion
                                                </span>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                                @foreach ($row['courses'] as $c)
                                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; padding: 0.35rem 0.5rem; background: var(--hub-surface); border: 1px solid var(--hub-border); border-radius: 6px; font-size: 0.74rem;">
                                                        <div>
                                                            <strong style="color: var(--hub-ink);">{{ $c['title'] }}</strong>
                                                            @if(!empty($c['code']))
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $c['code'] }}</span>
                                                            @endif
                                                        </div>

                                                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                                                            @if($c['is_completed'])
                                                                <span class="hub-chip hub-chip-green" style="font-size: 0.62rem; padding: 0.1rem 0.4rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                                                    <x-heroicon-s-check-circle style="width: 0.75rem; height: 0.75rem;" />
                                                                    Completed on {{ $c['completed_at'] }}
                                                                </span>
                                                                <span class="hub-chip" style="font-size: 0.62rem; padding: 0.1rem 0.4rem; background: rgba(16, 185, 129, 0.12); color: #065f46; font-weight: 700;">
                                                                    Certificate Ready
                                                                </span>
                                                                <button type="button"
                                                                        wire:click="unmarkCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                                        style="font-size: 0.62rem; padding: 0.15rem 0.35rem; background: transparent; border: 1px solid var(--hub-border); border-radius: 4px; color: var(--hub-muted); cursor: pointer;"
                                                                        title="Reset completion status">
                                                                    Reset
                                                                </button>
                                                            @else
                                                                <span class="hub-chip hub-chip-amber" style="font-size: 0.62rem; padding: 0.1rem 0.4rem;">
                                                                    In Progress
                                                                </span>
                                                                <button type="button"
                                                                        wire:click="markCourseComplete({{ $row['id'] }}, {{ $c['id'] }})"
                                                                        style="font-size: 0.7rem; padding: 0.25rem 0.65rem; background: #059669; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; transition: background 0.15s ease;"
                                                                        onmouseover="this.style.background='#047857'"
                                                                        onmouseout="this.style.background='#059669'">
                                                                    <x-heroicon-s-check-circle style="width: 0.8rem; height: 0.8rem;" />
                                                                    Mark Complete &amp; Issue Certificate
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Quizzes DEC --}}
                                        @if(count($row['quiz_details']) > 0)
                                            <div style="margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.68rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Quizzes (DEC Order)</span>
                                                <div style="display: flex; flex-direction: column; gap: 0.25rem; margin-top: 0.2rem;">
                                                    @foreach ($row['quiz_details'] as $qd)
                                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; padding: 0.25rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px; flex-wrap: wrap; gap: 0.3rem;">
                                                            <span>{{ $qd['title'] }} ({{ $qd['course'] }})</span>
                                                            <div style="display: flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;">
                                                                <strong style="color: {{ $qd['passed'] ? '#059669' : '#dc2626' }};">{{ $qd['percentage'] }}%</strong>
                                                                @if(!empty($qd['is_retake']))
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(59, 130, 246, 0.12); color: #1d4ed8; font-weight: 700;">2nd Try</span>
                                                                @endif

                                                                <button type="button" wire:click="viewQuizAttempt({{ $qd['id'] }})"
                                                                        style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: var(--hub-surface); color: var(--hub-ink); border: 1px solid var(--hub-border); border-radius: 4px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;"
                                                                        title="View questions, student selected options, and submitted answers">
                                                                    <x-heroicon-o-eye style="width: 0.7rem; height: 0.7rem; color: var(--hub-primary);" />
                                                                    Answers
                                                                </button>

                                                                @if(!empty($qd['retake_allowed']))
                                                                    <button type="button" wire:click="revokeQuizRetake({{ $row['id'] }}, {{ $qd['quiz_id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: rgba(16, 185, 129, 0.15); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 4px; cursor: pointer; font-weight: 700;">
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button type="button" wire:click="grantQuizRetake({{ $row['id'] }}, {{ $qd['quiz_id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: var(--hub-surface); color: var(--hub-primary); border: 1px solid var(--hub-border); border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                                <span style="color: var(--hub-muted); font-size: 0.65rem;">{{ $qd['date'] }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Assignments DEC --}}
                                        @if(count($row['assignment_details']) > 0)
                                            <div style="margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.68rem; font-weight: 700; color: #6366f1; text-transform: uppercase;">Assignments (DEC Order)</span>
                                                <div style="display: flex; flex-direction: column; gap: 0.25rem; margin-top: 0.2rem;">
                                                    @foreach ($row['assignment_details'] as $ad)
                                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; padding: 0.25rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px; flex-wrap: wrap; gap: 0.3rem;">
                                                            <span>{{ $ad['title'] }} ({{ $ad['course'] }})</span>
                                                            <div style="display: flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;">
                                                                <strong>{{ $ad['grade'] !== null ? $ad['grade'].'%' : 'Ungraded' }}</strong>
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $ad['status'] }}</span>
                                                                @if(!empty($ad['is_retake']))
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(99, 102, 241, 0.12); color: #4338ca; font-weight: 700;">2nd Try</span>
                                                                @endif
                                                                @if(!empty($ad['retake_allowed']))
                                                                    <button type="button" wire:click="revokeAssignmentRetake({{ $ad['id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: rgba(99, 102, 241, 0.15); color: #4338ca; border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 4px; cursor: pointer; font-weight: 700;">
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button type="button" wire:click="grantAssignmentRetake({{ $ad['id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: var(--hub-surface); color: #4338ca; border: 1px solid var(--hub-border); border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Assessments DEC --}}
                                        @if(count($row['assessment_details']) > 0)
                                            <div>
                                                <span style="font-size: 0.68rem; font-weight: 700; color: #8b5cf6; text-transform: uppercase;">Assessments (DEC Order)</span>
                                                <div style="display: flex; flex-direction: column; gap: 0.25rem; margin-top: 0.2rem;">
                                                    @foreach ($row['assessment_details'] as $asub)
                                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; padding: 0.25rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px; flex-wrap: wrap; gap: 0.3rem;">
                                                            <span>{{ $asub['title'] }} ({{ $asub['course'] }})</span>
                                                            <div style="display: flex; gap: 0.45rem; align-items: center; flex-wrap: wrap;">
                                                                <strong>{{ $asub['score'] !== null ? $asub['score'].'%' : 'Ungraded' }}</strong>
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $asub['status'] }}</span>
                                                                @if(!empty($asub['is_retake']))
                                                                    <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem; background: rgba(14, 165, 233, 0.12); color: #0369a1; font-weight: 700;">2nd Try</span>
                                                                @endif
                                                                @if(!empty($asub['retake_allowed']))
                                                                    <button type="button" wire:click="revokeAssessmentRetake({{ $asub['id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: rgba(14, 165, 233, 0.15); color: #0369a1; border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 4px; cursor: pointer; font-weight: 700;">
                                                                        ⭐ 2nd Try Granted (Revoke)
                                                                    </button>
                                                                @else
                                                                    <button type="button" wire:click="grantAssessmentRetake({{ $asub['id'] }})"
                                                                            style="font-size: 0.62rem; padding: 0.1rem 0.35rem; background: var(--hub-surface); color: #0369a1; border: 1px solid var(--hub-border); border-radius: 4px; cursor: pointer; font-weight: 600;">
                                                                        + Grant 2nd Try
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- ==================== QUIZ ATTEMPT ANSWERS REVIEW MODAL ==================== --}}
        @if ($this->selectedQuizAttempt)
            <div style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
                 x-data
                 @keydown.escape.window="$wire.closeQuizAttemptModal()">
                <div style="position: relative; width: 100%; max-width: 920px; max-height: 90vh; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--hub-border);">
                    {{-- Modal Top Bar --}}
                    <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(13, 148, 136, 0.12); display: flex; align-items: center; justify-content: center;">
                                <x-heroicon-o-academic-cap style="width: 1.1rem; height: 1.1rem; color: var(--hub-primary);" />
                            </div>
                            <div>
                                <span style="font-weight: 800; font-size: 0.95rem; color: var(--hub-ink); display: block; line-height: 1.2;">
                                    Student Quiz Review &amp; Answers
                                </span>
                                <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                    Full question breakdown, student selections, and answer accuracy
                                </span>
                            </div>
                        </div>
                        <button type="button" wire:click="closeQuizAttemptModal"
                                style="background: transparent; border: none; font-size: 1.3rem; line-height: 1; color: var(--hub-muted); cursor: pointer; padding: 0.2rem 0.5rem; border-radius: 6px;"
                                title="Close modal">
                            &times;
                        </button>
                    </div>

                    {{-- Scrollable Content Body --}}
                    <div style="padding: 1.25rem; overflow-y: auto; flex: 1; background: #fafafa;">
                        @include('filament.instructor.modals.quiz-attempt-answers', ['attempt' => $this->selectedQuizAttempt])
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--hub-border); display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        @php $modalAttempt = $this->selectedQuizAttempt; @endphp
                        @if ($modalAttempt && !empty($modalAttempt->retake_allowed))
                            <button type="button" wire:click="revokeQuizRetake({{ $modalAttempt->user_id }}, {{ $modalAttempt->quiz_id }})"
                                    style="font-size: 0.78rem; padding: 0.4rem 0.85rem; background: rgba(16, 185, 129, 0.15); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 6px; cursor: pointer; font-weight: 700;">
                                ⭐ 2nd Try Granted (Revoke)
                            </button>
                        @elseif ($modalAttempt && $modalAttempt->completed_at)
                            <button type="button" wire:click="grantQuizRetake({{ $modalAttempt->user_id }}, {{ $modalAttempt->quiz_id }})"
                                    style="font-size: 0.78rem; padding: 0.4rem 0.85rem; background: var(--hub-primary); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
                                + Grant 2nd Try
                            </button>
                        @endif
                        <button type="button" wire:click="closeQuizAttemptModal"
                                class="hub-btn"
                                style="font-size: 0.78rem; padding: 0.4rem 0.95rem; border-radius: 6px; cursor: pointer;">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== AWARD XP & BADGES MODAL ==================== --}}
        @if ($this->showAwardModal)
            <div style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
                 x-data
                 @keydown.escape.window="$wire.closeAwardModal()">
                <div style="position: relative; width: 100%; max-width: 580px; max-height: 90vh; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--hub-border);">
                    {{-- Modal Top Bar --}}
                    <div style="padding: 0.85rem 1.25rem; background: linear-gradient(135deg, #fffbeb, #fef3c7); border-bottom: 1px solid #fde68a; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f59e0b; display: flex; align-items: center; justify-content: center; color: #ffffff;">
                                <x-heroicon-s-sparkles style="width: 1.2rem; height: 1.2rem;" />
                            </div>
                            <div>
                                <span style="font-weight: 800; font-size: 0.95rem; color: #92400e; display: block; line-height: 1.2;">
                                    Award XP &amp; Badges to {{ $this->awardStudentName }}
                                </span>
                                <span style="font-size: 0.7rem; color: #b45309;">
                                    Recognize presentations, hackathons, debate, and off-platform excellence
                                </span>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAwardModal"
                                style="background: transparent; border: none; font-size: 1.3rem; line-height: 1; color: #92400e; cursor: pointer; padding: 0.2rem 0.5rem; border-radius: 6px;"
                                title="Close modal">
                            &times;
                        </button>
                    </div>

                    {{-- Form Content Body --}}
                    <div style="padding: 1.25rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.82rem;">
                        {{-- Course Select --}}
                        <div>
                            <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                Associated Course (Optional)
                            </label>
                            <select wire:model.live="awardCourseId"
                                    style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem;">
                                <option value="">None / General Off-Platform Activity</option>
                                @foreach ($this->instructorCourseOptions() as $cId => $cTitle)
                                    <option value="{{ $cId }}">{{ $cTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Activity Type --}}
                        <div>
                            <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                Activity / Recognition Reason <span style="color: #dc2626;">*</span>
                            </label>
                            <select wire:model.live="awardActivityType"
                                    style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem;">
                                <option value="Outstanding Presentation">🎤 Outstanding Presentation</option>
                                <option value="Classroom Participation & Debate">💬 Classroom Participation & Debate</option>
                                <option value="Project Demo & Showcase">💻 Project Demo & Showcase</option>
                                <option value="Hackathon / Competition Winner">🚀 Hackathon / Competition Winner</option>
                                <option value="Peer Mentoring & Collaboration">🤝 Peer Mentoring & Collaboration</option>
                                <option value="Lab Practical Excellence">⚙️ Lab Practical Excellence</option>
                                <option value="Leadership & Teamwork">👑 Leadership & Teamwork</option>
                                <option value="Extracurricular Contribution">🌟 Extracurricular Contribution</option>
                                <option value="custom">➕ Other / Custom Activity</option>
                            </select>
                        </div>

                        @if ($this->awardActivityType === 'custom')
                            <div>
                                <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                    Custom Activity Name <span style="color: #dc2626;">*</span>
                                </label>
                                <input type="text" wire:model="awardCustomActivity"
                                       placeholder="e.g. AI Prompt Challenge Winner"
                                       style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem;" />
                            </div>
                        @endif

                        {{-- XP and Coins Grid --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div>
                                <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                    XP Points <span style="color: #dc2626;">*</span>
                                </label>
                                <input type="number" wire:model.live.debounce.300ms="awardXp" min="1" max="2000"
                                       style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem; font-weight: 700;" />
                                <span style="font-size: 0.65rem; color: var(--hub-muted); display: block; margin-top: 0.15rem;">
                                    Contributes to lifetime rank
                                </span>
                            </div>

                            <div>
                                <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                    Thinker Coins (TC)
                                </label>
                                <input type="number" wire:model="awardCoins" min="0" max="1000"
                                       style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem; font-weight: 700;" />
                                <span style="font-size: 0.65rem; color: var(--hub-muted); display: block; margin-top: 0.15rem;">
                                    Spendable reward coins (30%)
                                </span>
                            </div>
                        </div>

                        {{-- Badge Selection --}}
                        <div>
                            <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                Award Badge (Optional)
                            </label>
                            <select wire:model.live="awardBadgeId"
                                    style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem;">
                                <option value="">No Badge (XP / Coins only)</option>
                                @foreach (\App\Models\Badge::all() as $b)
                                    <option value="{{ $b->id }}">{{ $b->icon }} {{ $b->name }} (+{{ $b->xp_reward }} XP)</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($this->awardBadgeId)
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <input type="checkbox" id="awardBadgeBonusXp" wire:model="awardBadgeBonusXp" style="cursor: pointer;" />
                                <label for="awardBadgeBonusXp" style="font-size: 0.74rem; color: var(--hub-ink); cursor: pointer;">
                                    Also grant badge's inherent bonus XP reward to student
                                </label>
                            </div>
                        @endif

                        {{-- Commendation Note --}}
                        <div>
                            <label style="font-weight: 700; color: var(--hub-ink); display: block; margin-bottom: 0.25rem; font-size: 0.76rem;">
                                Commendation Note / Reason (Optional)
                            </label>
                            <textarea wire:model="awardNote" rows="2"
                                      placeholder="e.g. Delivered a captivating presentation with outstanding live demonstration."
                                      style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 8px; border: 1px solid var(--hub-border); background: var(--hub-surface); color: var(--hub-ink); font-size: 0.8rem;"></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--hub-border); display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem;">
                        <button type="button" wire:click="closeAwardModal"
                                class="hub-btn"
                                style="font-size: 0.78rem; padding: 0.4rem 0.95rem; border-radius: 6px; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="button" wire:click="submitAward"
                                style="font-size: 0.78rem; padding: 0.4rem 1.1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <x-heroicon-s-sparkles style="width: 0.85rem; height: 0.85rem;" />
                            Award Recognition
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
