<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Overview</x-slot>

        <div class="hub-grid hub-stats-grid">
            <section class="hub-card">
                <p class="hub-eyebrow">Registered Students</p>
                <p class="hub-metric">{{ $registeredStudents < 10 ? $registeredStudents : '10+' }}</p>
                <p class="hub-copy">Active learner accounts</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Assigned Assessments</p>
                <p class="hub-metric">{{ $assignedAssessments < 10 ? $assignedAssessments : '10+' }}</p>
                <p class="hub-copy">Assessment records in system</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Published Assignments</p>
                <p class="hub-metric">{{ $publishedAssignments < 10 ? $publishedAssignments : '10+' }}</p>
                <p class="hub-copy">Assignment items published</p>
            </section>
            <section class="hub-card">
                <p class="hub-eyebrow">Materials</p>
                <p class="hub-metric">{{ $materials < 10 ? $materials : '10+' }}</p>
                <p class="hub-copy">Learning resources uploaded</p>
            </section>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
