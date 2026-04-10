<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Course Enrollments') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('The profile owner and admins can update enrolled courses.') }}
                            </p>
                        </header>

                        <form method="POST" action="{{ route('profiles.enrollments.sync', $user) }}" class="mt-6 space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="space-y-2">
                                @forelse ($availableCourses as $course)
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input
                                            type="checkbox"
                                            name="course_ids[]"
                                            value="{{ $course->id }}"
                                            class="rounded border-gray-300"
                                            @checked(in_array($course->id, old('course_ids', $enrolledCourseIds), true))
                                        >
                                        <span>{{ $course->title }} ({{ $course->code }})</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500">No courses available.</p>
                                @endforelse
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save Enrollments') }}</x-primary-button>

                                @if (session('status') === 'enrollments-updated')
                                    <p class="text-sm text-gray-600">{{ __('Enrollments updated.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            @if ($isOwnProfile)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
