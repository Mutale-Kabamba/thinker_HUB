<x-filament-panels::page>
    @php
        $user = auth()->user();
        $kids = $user->kids;
    @endphp

    <div class="space-y-6 font-sans max-w-7xl mx-auto">
        {{-- ============================================================ --}}
        {{-- HERO HEADER CARD                                             --}}
        {{-- ============================================================ --}}
        <div class="relative overflow-hidden rounded-2xl p-6 sm:p-7 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            <x-heroicon-o-shield-check class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                            <span>Parent Account Settings</span>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">#{{ $user->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Profile & Family Preferences
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                        Manage your parent contact info, login credentials, default workspace, and connected children.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-black text-xl flex items-center justify-center border-2 border-indigo-200 dark:border-indigo-800 shadow-xs shrink-0">
                        @if ($user->profile_photo_path)
                            <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ============================================================ --}}
            {{-- PROFILE DETAILS (2 COLUMNS)                                  --}}
            {{-- ============================================================ --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-user class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span>Parent Profile Information</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            This contact information is used for academic notifications and safe communication in course parents circles.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="parent_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    id="parent_name" 
                                    name="name" 
                                    type="text" 
                                    value="{{ old('name', $user->name) }}" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                    required
                                />
                                @error('name')
                                    <p class="text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label for="parent_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Email Address <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    id="parent_email" 
                                    name="email" 
                                    type="email" 
                                    value="{{ old('email', $user->email) }}" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                    required
                                />
                                @error('email')
                                    <p class="text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="parent_whatsapp" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Phone / WhatsApp Number
                                </label>
                                <input 
                                    id="parent_whatsapp" 
                                    name="whatsapp" 
                                    type="text" 
                                    value="{{ old('whatsapp', $user->whatsapp) }}" 
                                    placeholder="+260 970 000 000"
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                />
                                @error('whatsapp')
                                    <p class="text-xs font-semibold text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label for="parent_gender" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Gender
                                </label>
                                <select 
                                    id="parent_gender" 
                                    name="gender" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                >
                                    <option value="">Prefer not to say</option>
                                    <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="parent_avatar" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Avatar / Profile Photo
                            </label>
                            <input 
                                id="parent_avatar" 
                                name="profile_photo" 
                                type="file" 
                                accept="image/*" 
                                class="text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 transition"
                            />
                        </div>

                        @if (count($user->getAvailablePortals()) > 1)
                            <div class="space-y-1">
                                <label for="default_portal" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Default Workspace on Sign-In
                                </label>
                                <select 
                                    id="default_portal" 
                                    name="default_portal" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                >
                                    @foreach ($user->getAvailablePortals() as $portalKey => $portalData)
                                        <option value="{{ $portalKey }}" {{ old('default_portal', $user->default_portal ?? '') === $portalKey ? 'selected' : '' }}>
                                            {{ $portalData['label'] }} ({{ $portalData['path'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="pt-2 flex items-center gap-3">
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all">
                                Save Profile
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <x-heroicon-o-check-circle class="w-4 h-4" />
                                    <span>Profile saved successfully</span>
                                </span>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Password Update --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-5">
                    <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-key class="w-4 h-4 text-amber-500" />
                            <span>Security & Password</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Keep your parent login protected to secure your children's records.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-1">
                            <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Current Password
                            </label>
                            <input 
                                id="current_password" 
                                name="current_password" 
                                type="password" 
                                class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition" 
                                autocomplete="current-password"
                            />
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="text-xs font-semibold text-rose-500">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="new_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    New Password
                                </label>
                                <input 
                                    id="new_password" 
                                    name="password" 
                                    type="password" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition" 
                                    autocomplete="new-password"
                                />
                                @if ($errors->updatePassword->has('password'))
                                    <p class="text-xs font-semibold text-rose-500">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Confirm New Password
                                </label>
                                <input 
                                    id="new_password_confirmation" 
                                    name="password_confirmation" 
                                    type="password" 
                                    class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-750 dark:text-white p-2.5 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition" 
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>

                        <div class="pt-2 flex items-center gap-3">
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-xs transition-all">
                                Update Password
                            </button>
                            @if (session('status') === 'password-updated')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    <x-heroicon-o-check-circle class="w-4 h-4" />
                                    <span>Password updated</span>
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SIDEBAR: CONNECTED KIDS & SAFETY INFO                        --}}
            {{-- ============================================================ --}}
            <div class="space-y-6">
                {{-- Connected Kids Card --}}
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                            <x-heroicon-o-face-smile class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span>Connected Children ({{ $kids->count() }})</span>
                        </h3>
                        <a 
                            href="{{ route('filament.parent.pages.manage-kids') }}" 
                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            Manage
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($kids as $child)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-750 border border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-200 font-black text-xs shrink-0">
                                        @if ($child->profile_photo_path)
                                            <img src="{{ $child->getFilamentAvatarUrl() }}" alt="{{ $child->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ strtoupper(substr($child->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $child->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $child->email }}</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold text-amber-500 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                                    {{ number_format($child->lifetime_xp ?? 0) }} XP
                                </span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-4">
                                No children connected yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-2">
                        <a 
                            href="{{ route('filament.parent.pages.manage-kids') }}" 
                            class="block text-center w-full py-2 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-all"
                        >
                            Add or Link Child
                        </a>
                    </div>
                </div>

                {{-- Child Safety Guarantee Card --}}
                <div class="bg-indigo-50 dark:bg-indigo-950/40 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-900/60 space-y-3">
                    <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-bold text-xs uppercase tracking-wider">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        <span>Child Safety Guarantee</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Thinker HUB maintains an isolated environment for minors. Children registered under parent accounts are excluded from public adult forums, open friend directories, and platform-wide rankings.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
