<x-filament-panels::page>
    @php
        $user = auth()->user();
        $canReadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $latestNotifications = $canReadNotifications ? auth()->user()->notifications->take(5) : collect();
    @endphp

    <div class="space-y-6 font-sans">
        {{-- Header Card --}}
        <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-[#7C3AED] dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800">
                Account & Preferences
            </span>
            <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Instructor Settings
            </h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium">
                Manage your public teaching profile, workspace preferences, notification feeds, and login security.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Profile Settings Card --}}
            <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Personal Profile
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Update your name, primary email address, and avatar photo.
                    </p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1.5">
                        <label for="instr_name" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="instr_name" 
                            name="name" 
                            type="text" 
                            value="{{ old('name', $user->name) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            required
                        />
                        @error('name')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="instr_email" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="instr_email" 
                            name="email" 
                            type="email" 
                            value="{{ old('email', $user->email) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            required
                        />
                        @error('email')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="instr_photo" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Profile Picture
                        </label>
                        <div class="flex items-center gap-4">
                            @if ($user->profile_photo_path)
                                <img 
                                    src="{{ $user->getFilamentAvatarUrl() }}" 
                                    alt="Profile photo" 
                                    class="w-14 h-14 rounded-full object-cover border-2 border-[#7C3AED] shadow-sm"
                                />
                            @else
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#7C3AED] to-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-sm">
                                    {{ Str::upper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                            <input 
                                id="instr_photo" 
                                name="profile_photo" 
                                type="file" 
                                accept="image/*" 
                                class="text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-[#7C3AED] hover:file:bg-purple-100 transition"
                            />
                        </div>
                        @error('profile_photo')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (count($user->getAvailablePortals()) > 1)
                        <div class="space-y-1.5 pt-2">
                            <label for="default_portal" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                                Default Account / Workspace
                            </label>
                            <p class="text-[11px] text-slate-400">Select which portal opens automatically when you sign in.</p>
                            <select 
                                id="default_portal" 
                                name="default_portal" 
                                class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition"
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
                        <button 
                            type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-sm transition transform hover:-translate-y-0.5"
                        >
                            Save Profile
                        </button>
                        @if (session('status') === 'profile-updated')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-s-check-circle class="w-4 h-4" />
                                Profile updated
                            </span>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Professional Details Card --}}
            <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Teaching Credentials & Bio
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Information visible to students across courses and webinars.
                    </p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-3.5">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1">
                        <label for="instr_proficiency" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Expertise & Topics
                        </label>
                        <input 
                            id="instr_proficiency" 
                            name="proficiency" 
                            type="text" 
                            value="{{ old('proficiency', $user->proficiency) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            placeholder="e.g. Data Science, Machine Learning, Full-Stack"
                        />
                        @error('proficiency')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="instr_occupation" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Occupation / Title
                        </label>
                        <input 
                            id="instr_occupation" 
                            name="occupation" 
                            type="text" 
                            value="{{ old('occupation', $user->occupation) }}" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            placeholder="e.g. Senior Software Architect"
                        />
                        @error('occupation')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="instr_bio" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Instructor Bio
                        </label>
                        <textarea 
                            id="instr_bio" 
                            name="bio" 
                            rows="3" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            placeholder="Write a brief description about your teaching style, experience, and background..."
                        >{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="instr_whatsapp" class="block text-xs font-bold text-slate-700 dark:text-slate-200">WhatsApp</label>
                            <input id="instr_whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" placeholder="+260 97 xxxxxxx" />
                        </div>
                        <div class="space-y-1">
                            <label for="instr_linkedin" class="block text-xs font-bold text-slate-700 dark:text-slate-200">LinkedIn</label>
                            <input id="instr_linkedin" name="linkedin_url" type="url" value="{{ old('linkedin_url', $user->linkedin_url) }}" class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" placeholder="https://linkedin.com/in/..." />
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button 
                            type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-sm transition transform hover:-translate-y-0.5"
                        >
                            Save Details
                        </button>
                        @if (session('status') === 'profile-updated')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-s-check-circle class="w-4 h-4" />
                                Details saved
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Notifications List Card --}}
            <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                            Recent Alerts & Notifications
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500">
                            Activity logs and system broadcasts.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-slate-400">{{ count($latestNotifications) }} alerts</span>
                </div>

                <div class="space-y-2">
                    @forelse ($latestNotifications as $note)
                        <div 
                            x-data="{ cleared: false }" 
                            x-show="!cleared" 
                            x-transition.opacity
                            @click="fetch('/notifications/{{ $note->id }}/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); cleared = true;"
                            class="p-3 rounded-xl border border-slate-100 dark:border-[#233842] bg-slate-50/70 dark:bg-slate-800/40 hover:bg-purple-50/50 dark:hover:bg-slate-800 transition cursor-pointer flex items-center justify-between gap-3"
                            title="Click to mark as read"
                        >
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                {{ $note->data['message'] ?? ($note->data['title'] ?? 'Notification') }}
                            </p>
                            <span class="text-[10px] text-slate-400 flex-shrink-0">
                                {{ $note->created_at?->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs font-medium">
                            No notifications yet.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Update Password Security Card --}}
            <div class="edtech-card bg-white dark:bg-[#102028] p-6 rounded-2xl border border-slate-100 dark:border-[#233842] shadow-sm space-y-4">
                <div class="border-b border-slate-100 dark:border-[#233842] pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        Security & Password
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        Ensure your account uses a strong, secure passphrase.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <label for="instr_current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Current Password
                        </label>
                        <input 
                            id="instr_current_password" 
                            name="current_password" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            autocomplete="current-password"
                        />
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="text-xs font-semibold text-rose-600">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <label for="instr_new_password" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            New Password
                        </label>
                        <input 
                            id="instr_new_password" 
                            name="password" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            autocomplete="new-password"
                        />
                        @if ($errors->updatePassword->has('password'))
                            <p class="text-xs font-semibold text-rose-600">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <label for="instr_new_password_confirm" class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                            Confirm Password
                        </label>
                        <input 
                            id="instr_new_password_confirm" 
                            name="password_confirmation" 
                            type="password" 
                            class="w-full text-xs font-medium rounded-xl border border-slate-300 dark:border-[#233842] bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 focus:ring-2 focus:ring-[#7C3AED] focus:border-[#7C3AED] transition" 
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button 
                            type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-extrabold text-white bg-[#7C3AED] hover:bg-[#6D28D9] dark:bg-purple-600 dark:hover:bg-purple-500 shadow-sm transition transform hover:-translate-y-0.5"
                        >
                            Update Password
                        </button>
                        @if (session('status') === 'password-updated')
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-s-check-circle class="w-4 h-4" />
                                Password updated
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @include('partials.passkey-manager')
        </div>
    </div>
</x-filament-panels::page>
