<div>
    @auth
        @if ($isOpen)
            <div style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(6px);"
                 x-data
                 @keydown.escape.window="$wire.closeModal()">
                <div style="position: relative; width: 100%; max-width: 520px; max-height: 90vh; background: #ffffff; border-radius: 16px; box-shadow: none; display: flex; flex-direction: column; overflow: hidden; border: 1px solid #cbd5e1;"
                     class="dark:bg-slate-900 dark:border-slate-800">
                    
                    {{-- Modal Header --}}
                    <div style="padding: 1rem 1.25rem; background: linear-gradient(135deg, #0d9488, #0f766e); display: flex; justify-content: space-between; align-items: center; color: #ffffff;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center;">
                                <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 style="font-weight: 800; font-size: 1rem; line-height: 1.2; margin: 0;">
                                    @if ($targetType === 'course')
                                        Rate &amp; Review Course
                                    @elseif ($targetType === 'instructor')
                                        Rate &amp; Review Instructor
                                    @else
                                        Share Your Platform Experience
                                    @endif
                                </h3>
                                <p style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.85); margin: 0;">
                                    {{ $targetTitle ?: 'Your honest feedback empowers the thinker_HUB community' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal"
                                style="background: transparent; border: none; font-size: 1.4rem; line-height: 1; color: #ffffff; cursor: pointer; padding: 0.2rem 0.4rem; opacity: 0.8;"
                                title="Close modal">
                            &times;
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div style="padding: 1.25rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                        {{-- Star Rating Picker (Optional) --}}
                        <div style="text-align: center; padding: 0.75rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;"
                             class="dark:bg-slate-800/50 dark:border-slate-700">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <label style="font-size: 0.76rem; font-weight: 700; color: #475569; margin: 0;"
                                       class="dark:text-slate-300">
                                    Star Rating (Optional)
                                </label>
                                @if ($rating !== null)
                                    <button type="button" wire:click="clearRating"
                                            style="background: none; border: none; font-size: 0.72rem; color: #0d9488; font-weight: 600; cursor: pointer; text-decoration: underline;">
                                        Clear Rating
                                    </button>
                                @endif
                            </div>
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                @for ($star = 1; $star <= 5; $star++)
                                    <button type="button"
                                            wire:click="setRating({{ $star }})"
                                            style="background: none; border: none; cursor: pointer; padding: 0.15rem; transition: transform 0.15s ease;"
                                            onmouseover="this.style.transform='scale(1.2)'"
                                            onmouseout="this.style.transform='scale(1)'"
                                            title="{{ $star }} Star{{ $star > 1 ? 's' : '' }}">
                                        <svg style="width: 2rem; height: 2rem;"
                                             class="{{ $rating !== null && $star <= $rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            <p style="font-size: 0.78rem; font-weight: 700; color: #0d9488; margin: 0.25rem 0 0 0;">
                                @if ($rating !== null)
                                    {{ match($rating) {
                                        5 => '⭐⭐⭐⭐⭐ Exceptional (5.0)',
                                        4 => '⭐⭐⭐⭐ Great (4.0)',
                                        3 => '⭐⭐⭐ Good / Average (3.0)',
                                        2 => '⭐⭐ Needs Improvement (2.0)',
                                        1 => '⭐ Poor (1.0)',
                                        default => $rating . ' Stars'
                                    } }}
                                @else
                                    <span style="color: #64748b; font-weight: 500;">No rating selected (Written review only)</span>
                                @endif
                            </p>
                            @error('rating') <span style="font-size: 0.7rem; color: #ef4444;">{{ $message }}</span> @enderror
                        </div>

                        {{-- Review Headline / Title --}}
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;"
                                   class="dark:text-slate-200">
                                Headline / Title (Optional)
                            </label>
                            <input type="text" wire:model="title"
                                   placeholder="e.g. In-depth content with practical code exercises!"
                                   maxlength="120"
                                   style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.84rem;"
                                   class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" />
                            @error('title') <span style="font-size: 0.7rem; color: #ef4444;">{{ $message }}</span> @enderror
                        </div>

                        {{-- Review Comment (Optional) --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <label style="font-size: 0.78rem; font-weight: 700; color: #1e293b;"
                                       class="dark:text-slate-200">
                                    Detailed Feedback (Optional)
                                </label>
                                <span style="font-size: 0.68rem; color: #64748b;">
                                    Rate without review or vice versa
                                </span>
                            </div>
                            <textarea wire:model="comment" rows="4"
                                      placeholder="What did you like the most? Share details (or leave empty if submitting only a star rating)..."
                                      style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.84rem;"
                                      class="dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100"></textarea>
                            @error('comment') <span style="font-size: 0.7rem; color: #ef4444;">{{ $message }}</span> @enderror
                        </div>

                        {{-- Anonymous Option --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.2rem;">
                            <input type="checkbox" id="isAnonymous" wire:model="isAnonymous"
                                   style="width: 1rem; height: 1rem; accent-color: #0d9488; cursor: pointer;" />
                            <label for="isAnonymous" style="font-size: 0.78rem; color: #334155; cursor: pointer;"
                                   class="dark:text-slate-300">
                                Post review anonymously (hide my full name and avatar)
                            </label>
                        </div>

                        {{-- Reward notice --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.75rem; background: rgba(13, 148, 136, 0.08); border-radius: 8px; border: 1px solid rgba(13, 148, 136, 0.2); font-size: 0.74rem; color: #0f766e;"
                             class="dark:text-teal-300 dark:bg-teal-950/30">
                            <span>✨</span>
                            <span>Earn <strong>+10 XP</strong> and <strong>+3 Thinker Coins</strong> for submitting your review!</span>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 0.6rem;"
                         class="dark:bg-slate-800/80 dark:border-slate-700">
                        <button type="button" wire:click="closeModal"
                                style="padding: 0.45rem 0.95rem; font-size: 0.8rem; font-weight: 600; color: #475569; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer;"
                                class="dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600">
                            Cancel
                        </button>
                        <button type="button" wire:click="submitReview"
                                wire:loading.attr="disabled"
                                style="padding: 0.45rem 1.15rem; font-size: 0.8rem; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #0d9488, #0f766e); border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <span wire:loading.remove>Submit Review</span>
                            <span wire:loading>Submitting...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</div>
