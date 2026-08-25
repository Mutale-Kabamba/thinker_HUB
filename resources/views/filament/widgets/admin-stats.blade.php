<x-filament-widgets::widget>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-edtech.stat-card
            title="Registered Students"
            :value="$registeredStudents"
            delta="+100%"
            deltaType="positive"
            subtitle="Active learner accounts"
            color="teal"
            :sparkline="[20, 35, 50, 65, 80, 95]"
        />

        <x-edtech.stat-card
            title="Assigned Assessments"
            :value="$assignedAssessments"
            delta="Evaluations"
            deltaType="neutral"
            subtitle="Assessment records"
            color="sky"
            :sparkline="[15, 25, 40, 55, 70, 85]"
        />

        <x-edtech.stat-card
            title="Published Assignments"
            :value="$publishedAssignments"
            delta="Deliverables"
            deltaType="neutral"
            subtitle="Assignment items"
            color="indigo"
            :sparkline="[30, 45, 40, 60, 75, 90]"
        />

        <x-edtech.stat-card
            title="Learning Materials"
            :value="$materials"
            delta="Resources"
            deltaType="neutral"
            subtitle="Course guides & notes"
            color="amber"
            :sparkline="[10, 20, 35, 45, 60, 75]"
        />
    </div>
</x-filament-widgets::widget>
