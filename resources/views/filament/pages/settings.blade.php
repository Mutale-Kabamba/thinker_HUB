<x-filament-panels::page>
    @php
        $user = auth()->user();
        $canReadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
        $latestNotifications = $canReadNotifications ? auth()->user()->notifications->take(6) : collect();
    @endphp

    <div class="hub-shell">
        <div class="hub-grid hub-grid-2">
            <section class="hub-card">
                <p class="hub-eyebrow">Admin Account</p>
                <h2 class="hub-title">Profile & Security</h2>
                <p class="hub-copy">Manage your admin identity directly in Filament.</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="admin_settings_name" class="hub-eyebrow">Name</label>
                        <input id="admin_settings_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="hub-input" required>
                        @error('name')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="admin_settings_email" class="hub-eyebrow">Email</label>
                        <input id="admin_settings_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="hub-input" required>
                        @error('email')
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="admin_settings_profile_photo" class="hub-eyebrow">Profile Picture</label>
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Profile photo" style="margin:0.45rem 0;height:4.2rem;width:4.2rem;border-radius:999px;object-fit:cover;border:1px solid var(--hub-border);">
                        @endif
                        <input id="admin_settings_profile_photo" name="profile_photo" type="file" accept="image/*" class="hub-input">
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

            <section class="hub-card">
                <p class="hub-eyebrow">System Alerts</p>
                <h2 class="hub-title">Latest Notifications</h2>
                <p class="hub-copy">Assignment, submission, and workflow updates delivered to your account.</p>
                <div class="hub-stack" style="margin-top:0.7rem;">
                    @forelse ($latestNotifications as $note)
                        <div style="border:1px solid var(--hub-border);border-radius:10px;padding:0.65rem;">
                            {{ $note->data['message'] ?? ($note->data['title'] ?? 'Notification') }}
                        </div>
                    @empty
                        <p class="hub-copy">{{ $canReadNotifications ? 'No notifications yet.' : 'Notifications are unavailable until migrations are applied.' }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="hub-grid hub-grid-2">
            <section class="hub-card">
                <p class="hub-eyebrow">Security</p>
                <h2 class="hub-title">Update Password</h2>
                <p class="hub-copy">Rotate your password regularly for better account security.</p>

                <form method="POST" action="{{ route('password.update') }}" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="admin_current_password" class="hub-eyebrow">Current Password</label>
                        <input id="admin_current_password" name="current_password" type="password" class="hub-input" autocomplete="current-password">
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="admin_new_password" class="hub-eyebrow">New Password</label>
                        <input id="admin_new_password" name="password" type="password" class="hub-input" autocomplete="new-password">
                        @if ($errors->updatePassword->has('password'))
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="admin_new_password_confirmation" class="hub-eyebrow">Confirm Password</label>
                        <input id="admin_new_password_confirmation" name="password_confirmation" type="password" class="hub-input" autocomplete="new-password">
                    </div>

                    <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
                        <button type="submit" class="hub-btn hub-btn-primary">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <span class="hub-chip hub-chip-green">Password updated</span>
                        @endif
                    </div>
                </form>
            </section>

            <section class="hub-card">
                <p class="hub-eyebrow">Account Cleanup</p>
                <h2 class="hub-title">Delete Account</h2>
                <p class="hub-copy">This will permanently delete your admin account and sign you out.</p>

                <form method="POST" action="{{ route('profile.destroy') }}" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="admin_delete_password" class="hub-eyebrow">Confirm Password</label>
                        <input id="admin_delete_password" name="password" type="password" class="hub-input" autocomplete="current-password" required>
                        @if ($errors->userDeletion->has('password'))
                            <p class="hub-copy" style="color:var(--hub-danger);">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <button type="submit" class="hub-btn hub-btn-danger">Delete Account</button>
                </form>
            </section>
        </div>
    </div>
</x-filament-panels::page>
