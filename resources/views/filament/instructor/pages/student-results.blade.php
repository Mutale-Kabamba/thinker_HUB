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
                            {{ count($students) }} Students • {{ $tasksData['totals']['total'] }} Tasks Consolidated
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
                    <span style="color: var(--hub-muted);">Ordering:</span>
                    <strong style="color: var(--hub-primary);">DEC (Highest Scores First)</strong>
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
                           placeholder="Search student or evaluation task..."
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

        {{-- ==================== VIEW 1: 3 HORIZONTAL CATEGORY CARDS (QUIZZES / ASSIGNMENTS / ASSESSMENTS) ==================== --}}
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
                            {{ $catStats['quizzes']['count'] }} Quizzes
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 0.45rem 0 0.35rem 0;">
                        <div>
                            <span style="font-size: 0.66rem; color: var(--hub-muted); display: block;">Quiz Average</span>
                            <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;">
                                {{ $catStats['quizzes']['avg_score'] !== null ? $catStats['quizzes']['avg_score'].'%' : '—' }}
                            </span>
                        </div>
                        <div style="text-align: right; font-size: 0.72rem;">
                            <span style="color: var(--hub-muted);">Total Attempts:</span>
                            <strong style="color: var(--hub-ink);">{{ $catStats['quizzes']['attempts'] }}</strong>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.68rem; padding-top: 0.4rem; border-top: 1px solid var(--hub-border); color: var(--hub-muted);">
                        <span>Passed: <strong style="color: #059669;">{{ $catStats['quizzes']['passed'] }}</strong></span>
                        <span style="font-weight: 700; color: {{ $isQuizActive ? '#059669' : 'var(--hub-muted)' }};">
                            {{ $isQuizActive ? '▲ Viewing Details' : '▼ Click to Open' }}
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
                            {{ $catStats['assignments']['count'] }} Assignments
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
                            {{ $isAssignActive ? '▲ Viewing Details' : '▼ Click to Open' }}
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
                            {{ $catStats['assessments']['count'] }} Assessments
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
                            {{ $isAssessActive ? '▲ Viewing Details' : '▼ Click to Open' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ==================== OPENED FULL CATEGORY DETAILS SECTION ==================== --}}
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- 1. FULL QUIZZES DETAIL --}}
                @if($isQuizActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #10b981;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(16, 185, 129, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-academic-cap style="width: 1.1rem; height: 1.1rem; color: #047857;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    All Quizzes — Student Results in Descending (DEC) Order
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['quizzes']) }} Quizzes • {{ $catStats['quizzes']['attempts'] }} Total Attempts
                            </span>
                        </div>

                        @if(count($tasksData['quizzes']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No quizzes found matching your search or filters.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['quizzes'] as $q)
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Quiz Title Bar --}}
                                        <div style="padding: 0.55rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                    <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $q['title'] }}</span>
                                                    <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $q['course_code'] }}</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                    {{ $q['course'] }} • Pass: {{ $q['pass_percentage'] }}% • {{ $q['results_count'] }} Attempts
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
                                            </div>
                                        </div>

                                        {{-- Student Attempts List in DEC Order --}}
                                        @if(count($q['results']) === 0)
                                            <div style="padding: 0.75rem 0.85rem; font-size: 0.74rem; color: var(--hub-muted); font-style: italic;">
                                                No attempts recorded for this quiz yet.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($q['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
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

                {{-- 2. FULL ASSIGNMENTS DETAIL --}}
                @if($isAssignActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #6366f1;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(99, 102, 241, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-document-text style="width: 1.1rem; height: 1.1rem; color: #4338ca;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    All Assignments — Student Submissions in Descending (DEC) Order
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['assignments']) }} Assignments • {{ $catStats['assignments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assignments']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No assignments found matching your search or filters.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['assignments'] as $a)
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Assignment Title Bar --}}
                                        <div style="padding: 0.55rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                    <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $a['title'] }}</span>
                                                    <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $a['course_code'] }}</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                    {{ $a['course'] }} • Due: {{ $a['due_date'] }} • {{ $a['results_count'] }} Submissions
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
                                            </div>
                                        </div>

                                        {{-- Student Submissions List in DEC Order --}}
                                        @if(count($a['results']) === 0)
                                            <div style="padding: 0.75rem 0.85rem; font-size: 0.74rem; color: var(--hub-muted); font-style: italic;">
                                                No submissions recorded for this assignment yet.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($a['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
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

                {{-- 3. FULL ASSESSMENTS DETAIL --}}
                @if($isAssessActive)
                    <div class="hub-card" style="padding: 0; border-radius: 12px; overflow: hidden; border: 1.5px solid #8b5cf6;">
                        {{-- Category Section Header --}}
                        <div style="padding: 0.65rem 0.85rem; background: rgba(139, 92, 246, 0.08); border-bottom: 1px solid var(--hub-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <x-heroicon-o-clipboard-document-check style="width: 1.1rem; height: 1.1rem; color: #6d28d9;" />
                                <h3 style="font-size: 0.92rem; font-weight: 800; color: var(--hub-ink); margin: 0;">
                                    All Assessments — Student Submissions in Descending (DEC) Order
                                </h3>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--hub-muted);">
                                {{ count($tasksData['assessments']) }} Assessments • {{ $catStats['assessments']['submissions'] }} Submissions
                            </span>
                        </div>

                        @if(count($tasksData['assessments']) === 0)
                            <div style="padding: 1.5rem; text-align: center; color: var(--hub-muted); font-size: 0.78rem;">
                                No assessments found matching your search or filters.
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column;">
                                @foreach ($tasksData['assessments'] as $as)
                                    <div style="border-bottom: {{ $loop->last ? 'none' : '1px solid var(--hub-border)' }};">
                                        {{-- Individual Assessment Title Bar --}}
                                        <div style="padding: 0.55rem 0.85rem; background: var(--hub-surface-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                    <span style="font-weight: 800; font-size: 0.84rem; color: var(--hub-ink);">{{ $as['title'] }}</span>
                                                    <span class="hub-chip" style="font-size: 0.6rem; padding: 0.06rem 0.3rem;">{{ $as['course_code'] }}</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                    {{ $as['course'] }} • Due: {{ $as['due_date'] }} • {{ $as['results_count'] }} Submissions
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
                                            </div>
                                        </div>

                                        {{-- Student Submissions List in DEC Order --}}
                                        @if(count($as['results']) === 0)
                                            <div style="padding: 0.75rem 0.85rem; font-size: 0.74rem; color: var(--hub-muted); font-style: italic;">
                                                No submissions recorded for this assessment yet.
                                            </div>
                                        @else
                                            <div style="display: flex; flex-direction: column;">
                                                @foreach ($as['results'] as $idx => $res)
                                                    <div style="padding: 0.45rem 0.85rem 0.45rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; border-top: 1px solid var(--hub-border); background: {{ $idx % 2 === 0 ? 'transparent' : 'var(--hub-surface-soft)' }}; font-size: 0.76rem;">
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
                                            </div>
                                            <div style="font-size: 0.68rem; color: var(--hub-muted);">
                                                {{ $row['email'] }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mini Scores Breakdown --}}
                                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.72rem;">
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
                                        <div style="text-align: right; min-width: 60px;">
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

                                        <button type="button"
                                                wire:click="toggleExpand({{ $row['id'] }})"
                                                style="font-size: 0.68rem; padding: 0.2rem 0.45rem; background: var(--hub-surface); border: 1px solid var(--hub-border); border-radius: 4px; color: var(--hub-ink); cursor: pointer;">
                                            {{ $isExpanded ? 'Hide' : 'Details' }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Expanded Student Breakdown (DEC Order) --}}
                                @if($isExpanded)
                                    <div style="padding: 0.6rem 0.85rem 0.75rem 2.2rem; background: var(--hub-surface); border-top: 1px solid var(--hub-border);">
                                        {{-- Quizzes DEC --}}
                                        @if(count($row['quiz_details']) > 0)
                                            <div style="margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.68rem; font-weight: 700; color: #10b981; text-transform: uppercase;">Quizzes (DEC Order)</span>
                                                <div style="display: flex; flex-direction: column; gap: 0.2rem; margin-top: 0.2rem;">
                                                    @foreach ($row['quiz_details'] as $qd)
                                                        <div style="display: flex; justify-content: space-between; font-size: 0.72rem; padding: 0.2rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px;">
                                                            <span>{{ $qd['title'] }} ({{ $qd['course'] }})</span>
                                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                                <strong style="color: {{ $qd['passed'] ? '#059669' : '#dc2626' }};">{{ $qd['percentage'] }}%</strong>
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
                                                <div style="display: flex; flex-direction: column; gap: 0.2rem; margin-top: 0.2rem;">
                                                    @foreach ($row['assignment_details'] as $ad)
                                                        <div style="display: flex; justify-content: space-between; font-size: 0.72rem; padding: 0.2rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px;">
                                                            <span>{{ $ad['title'] }} ({{ $ad['course'] }})</span>
                                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                                <strong>{{ $ad['grade'] !== null ? $ad['grade'].'%' : 'Ungraded' }}</strong>
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $ad['status'] }}</span>
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
                                                <div style="display: flex; flex-direction: column; gap: 0.2rem; margin-top: 0.2rem;">
                                                    @foreach ($row['assessment_details'] as $asub)
                                                        <div style="display: flex; justify-content: space-between; font-size: 0.72rem; padding: 0.2rem 0.4rem; background: var(--hub-surface-soft); border-radius: 4px;">
                                                            <span>{{ $asub['title'] }} ({{ $asub['course'] }})</span>
                                                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                                <strong>{{ $asub['score'] !== null ? $asub['score'].'%' : 'Ungraded' }}</strong>
                                                                <span class="hub-chip" style="font-size: 0.58rem; padding: 0.05rem 0.25rem;">{{ $asub['status'] }}</span>
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
    </div>
</x-filament-panels::page>
