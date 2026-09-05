<x-filament-panels::page>
    @php
        $user = auth()->user();
        $canReadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $latestNotifications = $canReadNotifications ? auth()->user()->notifications->take(6) : collect();
    @endphp

    <div class="space-y-6 font-sans max-w-7xl mx-auto">
        {{-- Hero Header Card --}}
        <div class="relative overflow-hidden rounded-2xl p-6 sm:p-7 bg-white dark:bg-[#102028] border border-slate-200/80 dark:border-[#233842] shadow-sm">
            <div class="absolute top-0 left-0 right-0 h-[3.5px] bg-gradient-to-r from-teal-500 via-emerald-400 to-indigo-500"></div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                            Admin Identity & Security
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">#{{ $user->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Profile & Workspace Settings
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Update your administrator credentials, system alerts, default workspace, and account security.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($user->profile_photo_path)
                        <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-full object-cover border-2 border-teal-500 shadow-sm" onerror="this.style.display='none'">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 text-white font-black text-lg flex items-center justify-center shadow-sm">
                            {{ \Illuminate\Support\Str::upper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Profile Settings Card --}}
            <div class="bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-200/80 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full bg-teal-500"></div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            Personal Profile
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Update your public admin name, notification email, and avatar photo.
                    </p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <label for="admin_settings_name" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="admin_settings_name" 
                            name="name" 
                            type="text" 
                            value="{{ old('name', $user->name) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition" 
                            required
                        />
                        @error('name')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="admin_settings_email" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="admin_settings_email" 
                            name="email" 
                            type="email" 
                            value="{{ old('email', $user->email) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition" 
                            required
                        />
                        @error('email')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="admin_settings_profile_photo" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Avatar Photo
                        </label>
                        <input 
                            id="admin_settings_profile_photo" 
                            name="profile_photo" 
                            type="file" 
                            accept="image/*" 
                            class="text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 dark:file:bg-teal-950/60 dark:file:text-teal-300 transition"
                        />
                        @error('profile_photo')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (count($user->getAvailablePortals()) > 1)
                        <div class="space-y-1.5">
                            <label for="default_portal" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                                Default Workspace on Sign-In
                            </label>
                            <select 
                                id="default_portal" 
                                name="default_portal" 
                                class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            >
                                @foreach ($user->getAvailablePortals() as $portalKey => $portalData)
                                    <option value="{{ $portalKey }}" {{ old('default_portal', $user->default_portal ?? '') === $portalKey ? 'selected' : '' }}>
                                        {{ $portalData['label'] }} ({{ $portalData['path'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('default_portal')
                                <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit" class="hub-btn hub-btn-primary px-5 py-2">
                            Save Profile
                        </button>
                        @if (session('status') === 'profile-updated')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Saved successfully
                            </span>
                        @endif
                    </div>
                </form>
            </div>

            {{-- System Notifications Feed --}}
            <div class="bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-200/80 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full bg-indigo-500"></div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            Recent Alerts & Logs
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Workflow, assignment, and submission updates sent to your admin inbox.
                    </p>
                </div>

                <div class="space-y-2.5">
                    @forelse ($latestNotifications as $note)
                        <div x-data="{ cleared: false }" x-show="!cleared" x-transition.opacity
                             @click="fetch('/notifications/{{ $note->id }}/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); cleared = true;"
                             class="group p-3 rounded-xl border border-slate-200/80 dark:border-[#233842] hover:border-teal-500/40 bg-slate-50/50 dark:bg-slate-900/40 cursor-pointer transition flex items-center justify-between gap-3"
                             title="Click to dismiss notification">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition truncate">
                                    {{ $note->data['message'] ?? ($note->data['title'] ?? 'System Notification') }}
                                </p>
                                <span class="text-[0.65rem] text-slate-400 dark:text-slate-500">
                                    {{ $note->created_at?->diffForHumans() }}
                                </span>
                            </div>
                            <span class="text-[0.7rem] text-slate-400 group-hover:text-rose-500 transition opacity-0 group-hover:opacity-100 flex-shrink-0">
                                Clear
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center rounded-xl bg-slate-50/60 dark:bg-slate-900/30 border border-dashed border-slate-200 dark:border-[#233842]">
                            <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                                {{ $canReadNotifications ? 'No unread notifications at this time.' : 'Notifications will appear here once configured.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Security / Password Update Card --}}
            <div class="bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-200/80 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full bg-amber-500"></div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            Update Password
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Ensure your administrator account uses a strong, unique password.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1.5">
                        <label for="admin_current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Current Password
                        </label>
                        <input 
                            id="admin_current_password" 
                            name="current_password" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition" 
                            autocomplete="current-password"
                        />
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="text-xs font-semibold text-rose-600">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <label for="admin_new_password" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            New Password
                        </label>
                        <input 
                            id="admin_new_password" 
                            name="password" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition" 
                            autocomplete="new-password"
                        />
                        @if ($errors->updatePassword->has('password'))
                            <p class="text-xs font-semibold text-rose-600">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <label for="admin_new_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Confirm Password
                        </label>
                        <input 
                            id="admin_new_password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-slate-50/50 dark:bg-slate-900/60 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition" 
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit" class="hub-btn hub-btn-warning px-5 py-2">
                            Update Password
                        </button>
                        @if (session('status') === 'password-updated')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Password updated
                            </span>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Danger Zone / Account Deletion Card --}}
            <div class="bg-white dark:bg-[#102028] p-6 rounded-2xl border border-rose-200/80 dark:border-rose-900/40 shadow-sm space-y-4">
                <div class="border-b border-rose-100 dark:border-rose-900/30 pb-3.5">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full bg-rose-500"></div>
                        <h2 class="text-sm font-bold text-rose-700 dark:text-rose-400">
                            Danger Zone
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Permanently delete your account and associated privileges.
                    </p>
                </div>

                <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                    @csrf
                    @method('DELETE')

                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Once your account is deleted, all resources and permissions will be permanently removed. Please enter your password to confirm.
                    </p>

                    <div class="space-y-1.5">
                        <label for="admin_delete_password" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Confirm Password
                        </label>
                        <input 
                            id="admin_delete_password" 
                            name="password" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-rose-300 dark:border-rose-900/50 bg-rose-50/20 dark:bg-rose-950/20 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition" 
                            autocomplete="current-password" 
                            required
                        />
                        @if ($errors->userDeletion->has('password'))
                            <p class="text-xs font-semibold text-rose-600">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="hub-btn hub-btn-danger px-5 py-2">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>
