<x-filament-panels::page>
    @php
        $user = auth()->user();
        $canReadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $latestNotifications = $canReadNotifications ? auth()->user()->notifications->take(5) : collect();
    @endphp

    <style>
        .hub-st-settings {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .hub-st-hero {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 1.35rem 1.5rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .hub-st-hero {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dark .hub-st-hero {
            background: #111b21;
            border-color: #1f2c34;
            box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.35);
        }
        .hub-st-hero-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5px;
            background: linear-gradient(90deg, #0d9488 0%, #10b981 50%, #0ea5e9 100%);
        }
        .hub-st-grid-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            align-items: stretch;
        }
        @media (min-width: 960px) {
            .hub-st-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .hub-st-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
        .dark .hub-st-card {
            background: #111b21;
            border-color: #1f2c34;
            box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.35);
        }
        .hub-st-card-danger {
            border-color: #fecdd3;
        }
        .dark .hub-st-card-danger {
            border-color: rgba(244, 63, 94, 0.3);
        }
        .hub-st-card-head {
            padding-bottom: 1rem;
            margin-bottom: 1.1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .hub-st-card-head {
            border-bottom-color: #1f2c34;
        }
        .hub-st-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #0d9488;
            margin: 0 0 0.25rem 0;
        }
        .dark .hub-st-kicker {
            color: #2dd4bf;
        }
        .hub-st-kicker-danger {
            color: #e11d48;
        }
        .dark .hub-st-kicker-danger {
            color: #fb7185;
        }
        .hub-st-card-title {
            font-size: 1.08rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .dark .hub-st-card-title {
            color: #f8fafc;
        }
        .hub-st-card-desc {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0.2rem 0 0 0;
            line-height: 1.45;
        }
        .dark .hub-st-card-desc {
            color: #94a3b8;
        }
        .hub-st-form {
            display: flex;
            flex-direction: column;
            gap: 0.95rem;
            flex: 1;
        }
        .hub-st-row-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.85rem;
        }
        @media (min-width: 540px) {
            .hub-st-row-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .hub-st-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #334155;
            margin-bottom: 0.35rem;
        }
        .dark .hub-st-label {
            color: #cbd5e1;
        }
        .hub-st-input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0.55rem 0.8rem;
            font-size: 0.82rem;
            font-weight: 500;
            background: #f8fafc;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }
        .dark .hub-st-input {
            background: #0d171d;
            border-color: #263844;
            color: #f8fafc;
        }
        .hub-st-input:focus {
            outline: none;
            border-color: #0d9488;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14);
        }
        .dark .hub-st-input:focus {
            background: #111f27;
            border-color: #14b8a6;
        }
        .hub-st-upload-box {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 0.85rem;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
        }
        .dark .hub-st-upload-box {
            border-color: #263844;
            background: #0d171d;
        }
        .hub-st-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            object-fit: cover;
            border: 2px solid #0d9488;
            flex-shrink: 0;
            background: linear-gradient(135deg, #0d9488, #059669);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .hub-st-file-input {
            font-size: 0.76rem;
            color: #475569;
            width: 100%;
        }
        .dark .hub-st-file-input {
            color: #94a3b8;
        }
        .hub-st-file-input::file-selector-button {
            margin-right: 0.65rem;
            padding: 0.38rem 0.8rem;
            border-radius: 8px;
            border: 1px solid #99f6e4;
            background: #f0fdfa;
            color: #0f766e;
            font-weight: 700;
            font-size: 0.74rem;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .hub-st-file-input::file-selector-button:hover {
            background: #ccfbf1;
        }
        .dark .hub-st-file-input::file-selector-button {
            background: rgba(13, 148, 136, 0.2);
            border-color: rgba(20, 184, 166, 0.35);
            color: #5eead4;
        }
        .hub-st-empty-notes {
            border-radius: 12px;
            border: 1.5px dashed #cbd5e1;
            background: rgba(248, 250, 252, 0.65);
            padding: 1.75rem 1.25rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            flex: 1;
        }
        .dark .hub-st-empty-notes {
            border-color: #263844;
            background: rgba(13, 23, 29, 0.5);
        }
    </style>

    <div class="hub-st-settings">
        {{-- Top Hero Summary Banner --}}
        <div class="hub-st-hero">
            <div class="hub-st-hero-bar"></div>
            <div>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.3rem;">
                    <span class="hub-chip hub-chip-primary" style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;">
                        Student Portal &bull; Settings
                    </span>
                </div>
                <h1 style="font-size:1.25rem;font-weight:900;margin:0;letter-spacing:-0.015em;color:inherit;">
                    Account &amp; Security Settings
                </h1>
                <p style="font-size:0.8rem;color:#64748b;margin:0.2rem 0 0 0;">
                    Manage your personal profile, password credentials, biometric passkeys, and notification alerts.
                </p>
            </div>

            <div style="display:flex;align-items:center;gap:0.75rem;">
                @if ($user->profile_photo_path)
                    <img src="{{ $user->getFilamentAvatarUrl() }}" alt="{{ $user->name }}" class="hub-st-avatar" style="width:3.25rem;height:3.25rem;" onerror="this.style.display='none'">
                @else
                    <div class="hub-st-avatar" style="width:3.25rem;height:3.25rem;">
                        {{ \Illuminate\Support\Str::upper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <div style="min-width:0;">
                    <div style="font-size:0.88rem;font-weight:800;color:inherit;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $user->name }}
                    </div>
                    <div style="font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $user->email }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 1: Profile Settings & Update Password (2 Balanced Columns) --}}
        <div class="hub-st-grid-2">
            {{-- Left Column: Profile Settings --}}
            <section class="hub-st-card">
                <div>
                    <div class="hub-st-card-head">
                        <p class="hub-st-kicker">Account</p>
                        <h2 class="hub-st-card-title">Profile Settings</h2>
                        <p class="hub-st-card-desc">Update your name, email address, and profile image without leaving Filament.</p>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hub-st-form">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="student_settings_name" class="hub-st-label">Name</label>
                            <input id="student_settings_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="hub-st-input" required>
                            @error('name')
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="student_settings_email" class="hub-st-label">Email</label>
                            <input id="student_settings_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="hub-st-input" required>
                            @error('email')
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="hub-st-row-2">
                            <div>
                                <label for="student_settings_gender" class="hub-st-label">Gender</label>
                                <select id="student_settings_gender" name="gender" class="hub-st-input">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="student_settings_nrc" class="hub-st-label">NRC / Passport Number</label>
                                <input id="student_settings_nrc" name="nrc_passport" type="text" value="{{ old('nrc_passport', $user->nrc_passport) }}" placeholder="e.g. 123456/11/1" class="hub-st-input">
                                @error('nrc_passport')
                                    <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="student_settings_profile_photo" class="hub-st-label">Profile Picture</label>
                            <div class="hub-st-upload-box">
                                @if ($user->profile_photo_path)
                                    <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Profile photo" class="hub-st-avatar" onerror="this.style.display='none'">
                                @else
                                    <div class="hub-st-avatar">
                                        {{ \Illuminate\Support\Str::upper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div style="min-width:0;flex:1;">
                                    <input id="student_settings_profile_photo" name="profile_photo" type="file" accept="image/*" class="hub-st-file-input">
                                </div>
                            </div>
                            @error('profile_photo')
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (count($user->getAvailablePortals()) > 1)
                            <div>
                                <label for="default_portal" class="hub-st-label">Default Account / Workspace</label>
                                <select id="default_portal" name="default_portal" class="hub-st-input">
                                    @foreach ($user->getAvailablePortals() as $portalKey => $portalData)
                                        <option value="{{ $portalKey }}" {{ old('default_portal', $user->default_portal ?? '') === $portalKey ? 'selected' : '' }}>
                                            {{ $portalData['label'] }} ({{ $portalData['path'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_portal')
                                    <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div style="display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;padding-top:0.25rem;margin-top:auto;">
                            <button type="submit" class="hub-btn hub-btn-primary">Save Profile</button>
                            @if (session('status') === 'profile-updated')
                                <span class="hub-chip hub-chip-green">Profile updated</span>
                            @endif
                        </div>
                    </form>
                </div>
            </section>

            {{-- Right Column: Security / Update Password --}}
            <section class="hub-st-card">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="hub-st-card-head">
                        <p class="hub-st-kicker">Security</p>
                        <h2 class="hub-st-card-title">Update Password</h2>
                        <p class="hub-st-card-desc">Use a strong password you do not use on another platform.</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="hub-st-form">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="student_current_password" class="hub-st-label">Current Password</label>
                            <input id="student_current_password" name="current_password" type="password" class="hub-st-input" placeholder="Enter current password" autocomplete="current-password">
                            @if ($errors->updatePassword->has('current_password'))
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('current_password') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="student_new_password" class="hub-st-label">New Password</label>
                            <input id="student_new_password" name="password" type="password" class="hub-st-input" placeholder="Enter new password" autocomplete="new-password">
                            @if ($errors->updatePassword->has('password'))
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('password') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="student_new_password_confirmation" class="hub-st-label">Confirm Password</label>
                            <input id="student_new_password_confirmation" name="password_confirmation" type="password" class="hub-st-input" placeholder="Re-enter new password" autocomplete="new-password">
                        </div>

                        <div style="padding:0.75rem 0.9rem;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;font-size:0.75rem;color:#64748b;line-height:1.45;">
                            <strong style="color:#0f172a;">Password Tip:</strong> Combine at least 8 characters including upper &amp; lowercase letters, numbers, and symbols.
                        </div>

                        <div style="display:flex;align-items:center;gap:0.65rem;flex-wrap:wrap;padding-top:0.25rem;margin-top:auto;">
                            <button type="submit" class="hub-btn hub-btn-primary">Update Password</button>
                            @if (session('status') === 'password-updated')
                                <span class="hub-chip hub-chip-green">Password updated</span>
                            @endif
                        </div>
                    </form>
                </div>
            </section>
        </div>

        {{-- Row 2: Full-Width Fingerprint & Biometrics Passkey Manager --}}
        <div>
            @include('partials.passkey-manager')
        </div>

        {{-- Row 3: Recent Notifications & Account Cleanup (2 Balanced Columns) --}}
        <div class="hub-st-grid-2">
            <section class="hub-st-card">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="hub-st-card-head" style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
                        <div>
                            <p class="hub-st-kicker">Alerts</p>
                            <h2 class="hub-st-card-title">Recent Notifications</h2>
                            <p class="hub-st-card-desc">Course updates, grading alerts, and system announcements.</p>
                        </div>
                        <span class="hub-chip hub-chip-gray">{{ count($latestNotifications) }} alerts</span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0.55rem;flex:1;">
                        @forelse ($latestNotifications as $note)
                            <div x-data="{ cleared: false }" x-show="!cleared" x-transition.opacity
                                 @click="fetch('/notifications/{{ $note->id }}/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); cleared = true;"
                                 style="border:1px solid var(--hub-border);border-radius:10px;padding:0.7rem 0.85rem;cursor:pointer;transition:all 0.15s;"
                                 class="hover:opacity-80" title="Click to clear notification">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                    <div style="font-size:0.82rem;font-weight:600;">
                                        {{ $note->data['message'] ?? ($note->data['title'] ?? 'Notification') }}
                                    </div>
                                    <span style="font-size:0.7rem;opacity:0.6;white-space:nowrap;">{{ $note->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="hub-st-empty-notes">
                                <x-heroicon-o-bell-slash style="width:1.75rem;height:1.75rem;color:#94a3b8;" />
                                <p class="hub-copy" style="margin:0;font-weight:700;">
                                    {{ $canReadNotifications ? 'No notifications yet.' : 'Notifications are unavailable until migrations are applied.' }}
                                </p>
                                <span style="font-size:0.73rem;color:#94a3b8;">You're all caught up on course and account alerts.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="hub-st-card hub-st-card-danger">
                <div style="display:flex;flex-direction:column;height:100%;">
                    <div class="hub-st-card-head">
                        <p class="hub-st-kicker hub-st-kicker-danger">Account Cleanup</p>
                        <h2 class="hub-st-card-title">Delete Account</h2>
                        <p class="hub-st-card-desc">Deleting your account permanently removes your access and profile data.</p>
                    </div>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="hub-st-form">
                        @csrf
                        @method('DELETE')

                        <div>
                            <label for="student_delete_password" class="hub-st-label">Confirm Password</label>
                            <input id="student_delete_password" name="password" type="password" class="hub-st-input" placeholder="Enter your password to confirm deletion" autocomplete="current-password" required>
                            @if ($errors->userDeletion->has('password'))
                                <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>

                        <div style="padding-top:0.25rem;margin-top:auto;">
                            <button type="submit" class="hub-btn hub-btn-danger">Delete Account</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
