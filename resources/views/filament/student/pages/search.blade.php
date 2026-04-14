<x-filament-panels::page>
    <div class="hub-shell">
        <section class="hub-card">
            <p class="hub-eyebrow">System Search</p>
            <h2 class="hub-title">Find Anything Fast</h2>
            <p class="hub-copy">Search across your courses, assignments, materials, assessments, and submissions.</p>
            <div style="margin-top:0.75rem;">
                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Type keywords..." class="hub-input">
            </div>
        </section>

        @if (trim($query) !== '')
            <div class="hub-grid hub-grid-2">
                <section class="hub-card">
                    <h3 class="hub-title">Courses</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['courses'] as $item)
                            <a href="{{ route('filament.student.pages.courses') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">{{ $item['code'] }} - {{ $item['title'] }}</a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Assignments</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['assignments'] as $item)
                            <a href="{{ route('filament.student.pages.assignments') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">
                                <p style="margin:0;font-weight:700;">{{ $item['name'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Due: {{ $item['due'] }}</p>
                            </a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Materials</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['materials'] as $item)
                            <a href="{{ route('filament.student.pages.materials') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">{{ $item['title'] }} ({{ $item['material_type'] }})</a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">Assessments</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['assessments'] as $item)
                            <a href="{{ route('filament.student.pages.assessments') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">
                                <p style="margin:0;font-weight:700;">{{ $item['name'] ?? 'Assessment' }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Due: {{ $item['due_date'] ?? '-' }} | Score: {{ $item['score'] ?? '-' }}</p>
                            </a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">My Assignment Submissions</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['my_assignment_submissions'] as $item)
                            <a href="{{ route('filament.student.pages.assignments') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">
                                <p style="margin:0;font-weight:700;">{{ $item['assignment'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Status: {{ $item['status'] }} | Grade: {{ $item['grade'] ?? '-' }}</p>
                            </a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="hub-card">
                    <h3 class="hub-title">My Assessment Submissions</h3>
                    <div class="hub-stack" style="margin-top:0.65rem;">
                        @forelse ($results['my_assessment_submissions'] as $item)
                            <a href="{{ route('filament.student.pages.assessments') }}" style="display:block;border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;text-decoration:none;color:inherit;transition:background 0.15s,border-color 0.15s;" onmouseover="this.style.background='var(--hub-surface)';this.style.borderColor='var(--hub-primary)'" onmouseout="this.style.background='';this.style.borderColor='var(--hub-border)'">
                                <p style="margin:0;font-weight:700;">{{ $item['assessment'] }}</p>
                                <p style="margin:0.3rem 0 0;color:var(--hub-muted);font-size:0.78rem;">Status: {{ $item['status'] }} | Score: {{ $item['score'] ?? '-' }}</p>
                            </a>
                        @empty
                            <p class="hub-copy">No matches.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
