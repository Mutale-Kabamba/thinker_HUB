<x-filament-panels::page>
    @php
        $user = auth()->user();
    @endphp

    <div class="hub-shell">
        <div class="hub-grid hub-grid-2">
            
            {{-- Account & Personal Info --}}
            <section class="hub-card" style="padding:1rem;">
                <p class="hub-eyebrow">Account</p>
                <h3 class="hub-title">Profile Information</h3>
                <p class="hub-copy">Update your basic name, email, and profile avatar.</p>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="hub-eyebrow">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="hub-input" required>
                        @error('name') <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="hub-eyebrow">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="hub-input" required>
                        @error('email') <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="profile_photo" class="hub-eyebrow">Profile Photo</label>
                        @if ($user->profile_photo_path)
                            <div style="display:flex; align-items:center; gap:0.6rem; margin:0.4rem 0;">
                                <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Profile photo" style="height:3rem; width:3rem; border-radius:999px; object-fit:cover; border:1px solid var(--hub-border);">
                                <span class="hub-copy">Current avatar</span>
                            </div>
                        @endif
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="hub-input">
                        @error('profile_photo') <p class="hub-copy" style="color:var(--hub-danger);">{{ $message }}</p> @enderror
                    </div>

                    <div style="display:flex; align-items:center; gap:0.6rem; margin-top:0.4rem;">
                        <button type="submit" class="hub-btn hub-btn-primary">Save Profile</button>
                        @if (session('status') === 'profile-updated')
                            <span class="hub-chip hub-chip-green">Saved successfully</span>
                        @endif
                    </div>
                </form>
            </section>

            {{-- Contributor Specialty & Social Links --}}
            <section class="hub-card" style="padding:1rem;">
                <p class="hub-eyebrow">Contributor Profile</p>
                <h3 class="hub-title">Specialty &amp; Public Links</h3>
                <p class="hub-copy">This information appears on your public contributor profile in the Knowledge Network.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="hub-stack" style="margin-top:0.8rem;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">

                    <div>
                        <label for="specialty" class="hub-eyebrow">Technical Specialty / Track</label>
                        <input id="specialty" name="specialty" type="text" value="{{ old('specialty', $user->specialty) }}" placeholder="e.g. Full Stack, AI/ML, Cloud Architecture" class="hub-input">
                    </div>

                    @if ($user->isEmployer())
                        <div>
                            <label for="company" class="hub-eyebrow">Company / Organization</label>
                            <input id="company" name="company" type="text" value="{{ old('company', $user->company) }}" placeholder="e.g. Tech Corp" class="hub-input">
                        </div>
                    @endif

                    <div>
                        <label for="bio" class="hub-eyebrow">Bio / Overview</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Tell the community about your background..." class="hub-textarea">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(1, minmax(0, 1fr)); gap:0.55rem;">
                        <div>
                            <label for="whatsapp_number" class="hub-eyebrow">WhatsApp Phone / Link</label>
                            <input id="whatsapp_number" name="whatsapp_number" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="+260..." class="hub-input">
                        </div>
                        <div>
                            <label for="linkedin_url" class="hub-eyebrow">LinkedIn URL</label>
                            <input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="https://linkedin.com/in/..." class="hub-input">
                        </div>
                        <div>
                            <label for="facebook_url" class="hub-eyebrow">Facebook URL</label>
                            <input id="facebook_url" name="facebook_url" type="url" value="{{ old('facebook_url', $user->facebook_url) }}" placeholder="https://facebook.com/..." class="hub-input">
                        </div>
                        <div>
                            <label for="github_url" class="hub-eyebrow">GitHub URL</label>
                            <input id="github_url" name="github_url" type="url" value="{{ old('github_url', $user->github_url) }}" placeholder="https://github.com/..." class="hub-input">
                        </div>
                        <div>
                            <label for="instagram_url" class="hub-eyebrow">Instagram URL</label>
                            <input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $user->instagram_url) }}" placeholder="https://instagram.com/..." class="hub-input">
                        </div>
                    </div>

                    <div style="margin-top:0.4rem;">
                        <button type="submit" class="hub-btn hub-btn-primary">Save Details</button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</x-filament-panels::page>
