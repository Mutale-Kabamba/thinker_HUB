<x-filament-panels::page>
    @php
        $user = auth()->user();
        $canReadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $latestNotifications = $canReadNotifications ? auth()->user()->notifications->take(5) : collect();
    @endphp

    <div class="hub-shell">
        <div class="hub-grid hub-grid-2">
            {{-- Profile Settings --}}
            <section class="hub-card">
                <p class="hub-eyebrow">Account</p>
                <h2 class="hub-title">Profile Settings</h2>
                <p class="hub-copy">Update your name, email, and profile image.</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="instr_name" class="hub-eyebrow">Name</label>
                        <input id="instr_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="hub-input" required>
                        @error('name')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_email" class="hub-eyebrow">Email</label>
                        <input id="instr_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="hub-input" required>
                        @error('email')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_photo" class="hub-eyebrow">Profile Picture</label>
                        @if ($user->profile_photo_path)
                            <img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="Profile photo" style="margin:0.45rem 0;height:4.2rem;width:4.2rem;border-radius:999px;object-fit:cover;border:1px solid var(--hub-border);" onerror="this.style.display='none'">
                        @endif
                        <input id="instr_photo" name="profile_photo" type="file" accept="image/*" class="hub-input">
                        @error('profile_photo')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                        <button type="submit" class="hub-btn hub-btn-primary">Save Profile</button>
                        @if (session('status') === 'profile-updated')
                            <span class="hub-chip hub-chip-green">Profile updated</span>
                        @endif
                    </div>
                </form>
            </section>

            {{-- Instructor Details --}}
            <section class="hub-card">
                <p class="hub-eyebrow">Instructor</p>
                <h2 class="hub-title">Professional Details</h2>
                <p class="hub-copy">This information appears on your public instructor profile.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="instr_proficiency" class="hub-eyebrow">Proficiency / Expertise</label>
                        <input id="instr_proficiency" name="proficiency" type="text" value="{{ old('proficiency', $user->proficiency) }}" class="hub-input" placeholder="e.g. Data Science, Web Development">
                        @error('proficiency')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_occupation" class="hub-eyebrow">Occupation</label>
                        <input id="instr_occupation" name="occupation" type="text" value="{{ old('occupation', $user->occupation) }}" class="hub-input" placeholder="e.g. Senior Software Engineer">
                        @error('occupation')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_whatsapp" class="hub-eyebrow">WhatsApp Number</label>
                        <input id="instr_whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" class="hub-input" placeholder="+260 97 xxxxxxx">
                        @error('whatsapp')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_linkedin" class="hub-eyebrow">LinkedIn URL</label>
                        <input id="instr_linkedin" name="linkedin_url" type="url" value="{{ old('linkedin_url', $user->linkedin_url) }}" class="hub-input" placeholder="https://linkedin.com/in/yourprofile">
                        @error('linkedin_url')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instr_facebook" class="hub-eyebrow">Facebook URL</label>
                        <input id="instr_facebook" name="facebook_url" type="url" value="{{ old('facebook_url', $user->facebook_url) }}" class="hub-input" placeholder="https://facebook.com/yourprofile">
                        @error('facebook_url')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                        <button type="submit" class="hub-btn hub-btn-primary">Save Details</button>
                        @if (session('status') === 'profile-updated')
                            <span class="hub-chip hub-chip-green">Details saved</span>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        <div class="hub-grid hub-grid-2">
            {{-- Notifications --}}
            <section class="hub-card">
                <p class="hub-eyebrow">Alerts</p>
                <h2 class="hub-title">Recent Notifications</h2>
                <div class="hub-stack" style="margin-top:0.65rem;">
                    @forelse ($latestNotifications as $note)
                        <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                            {{ $note->data['message'] ?? ($note->data['title'] ?? 'Notification') }}
                        </div>
                    @empty
                        <p class="hub-copy">{{ $canReadNotifications ? 'No notifications yet.' : 'Notifications are unavailable until migrations are applied.' }}</p>
                    @endforelse
                </div>
            </section>

            {{-- Password --}}
            <section class="hub-card">
                <p class="hub-eyebrow">Security</p>
                <h2 class="hub-title">Update Password</h2>
                <p class="hub-copy">Use a strong password you do not use on another platform.</p>

                <form method="POST" action="{{ route('password.update') }}" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="instr_current_password" class="hub-eyebrow">Current Password</label>
                        <input id="instr_current_password" name="current_password" type="password" class="hub-input" autocomplete="current-password">
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="instr_new_password" class="hub-eyebrow">New Password</label>
                        <input id="instr_new_password" name="password" type="password" class="hub-input" autocomplete="new-password">
                        @if ($errors->updatePassword->has('password'))
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="instr_new_password_confirm" class="hub-eyebrow">Confirm Password</label>
                        <input id="instr_new_password_confirm" name="password_confirmation" type="password" class="hub-input" autocomplete="new-password">
                    </div>

                    <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                        <button type="submit" class="hub-btn hub-btn-primary">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <span class="hub-chip hub-chip-green">Password updated</span>
                        @endif
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-filament-panels::page>
