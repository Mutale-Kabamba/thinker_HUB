<div class="hub-shell">
    <style>
        .hub-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            font-family: inherit;
            color: #0f172a;
        }

        .dark .hub-shell,
        .fi-theme-dark .hub-shell {
            color: #f8fafc;
        }

        .hub-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .dark .hub-card,
        .fi-theme-dark .hub-card {
            background: #0f172a;
            border-color: #1e293b;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.2);
        }

        .hub-banner-card {
            background: linear-gradient(135deg, #0a2d27 0%, #0d3b33 50%, #041b17 100%);
            border: 1px solid rgba(20, 184, 166, 0.3);
            border-radius: 1rem;
            padding: 1.75rem 2rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px -1px rgba(10, 45, 39, 0.3);
        }

        .hub-eyebrow {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0d9488;
            margin: 0 0 0.35rem 0;
        }

        .dark .hub-eyebrow,
        .fi-theme-dark .hub-eyebrow {
            color: #2dd4bf;
        }

        .hub-title {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.25;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .dark .hub-title,
        .fi-theme-dark .hub-title {
            color: #ffffff;
        }

        .hub-copy {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0.35rem 0 0 0;
        }

        .dark .hub-copy,
        .fi-theme-dark .hub-copy {
            color: #94a3b8;
        }

        /* Target Selector Cards */
        .hub-target-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
            margin-top: 0.6rem;
        }

        .hub-target-btn {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem;
            border-radius: 0.85rem;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
        }

        .dark .hub-target-btn,
        .fi-theme-dark .hub-target-btn {
            background: #1e293b;
            border-color: #334155;
        }

        .hub-target-btn:hover {
            border-color: #7C3AED;
            background: #f5f3ff;
        }

        .dark .hub-target-btn:hover,
        .fi-theme-dark .hub-target-btn:hover {
            border-color: #a855f7;
            background: #2e1065;
        }

        .hub-target-btn.active {
            border-color: #7C3AED;
            background: #f5f3ff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .dark .hub-target-btn.active,
        .fi-theme-dark .hub-target-btn.active {
            border-color: #c084fc;
            background: #2e1065;
            box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.2);
        }

        .hub-target-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.85rem;
            background: rgba(124, 58, 237, 0.12);
            color: #7C3AED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .dark .hub-target-icon,
        .fi-theme-dark .hub-target-icon {
            background: rgba(192, 132, 252, 0.15);
            color: #c084fc;
        }

        /* Form Inputs */
        .hub-input-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .dark .hub-input-label,
        .fi-theme-dark .hub-input-label {
            color: #cbd5e1;
        }

        .hub-input-field {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0.65rem 0.85rem;
            font-size: 0.875rem;
            color: #0f172a;
            box-sizing: border-box;
            transition: border 0.15s ease;
        }

        .dark .hub-input-field,
        .fi-theme-dark .hub-input-field {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        .hub-input-field:focus {
            outline: none;
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .hub-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 800;
            background: #7C3AED;
            color: #ffffff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(124, 58, 237, 0.4);
            transition: all 0.2s ease;
        }

        .hub-submit-btn:hover {
            background: #6D28D9;
            transform: translateY(-1px);
        }

        /* Star Rating */
        .hub-star-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.25rem;
            text-align: center;
        }

        .dark .hub-star-box,
        .fi-theme-dark .hub-star-box {
            background: #1e293b;
            border-color: #334155;
        }

        .hub-star-btn {
            background: none;
            border: none;
            font-size: 2.25rem;
            line-height: 1;
            cursor: pointer;
            padding: 0 0.25rem;
            transition: transform 0.15s ease;
        }

        .hub-star-btn:hover {
            transform: scale(1.25);
        }

        .hub-star-filled {
            color: #f59e0b;
        }

        .hub-star-empty {
            color: #cbd5e1;
        }

        .dark .hub-star-empty,
        .fi-theme-dark .hub-star-empty {
            color: #475569;
        }

        /* Buttons */
        .hub-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            border-radius: 0.65rem;
            background: #0f766e;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(15, 118, 110, 0.25);
            transition: all 0.2s ease;
        }

        .hub-btn-submit:hover {
            background: #115e59;
            box-shadow: 0 4px 8px rgba(15, 118, 110, 0.35);
        }

        .hub-btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .hub-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .hub-chip-gold {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .dark .hub-chip-gold,
        .fi-theme-dark .hub-chip-gold {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }

        .hub-chip-teal {
            background: rgba(13, 148, 136, 0.12);
            color: #0f766e;
            border: 1px solid rgba(13, 148, 136, 0.25);
        }

        .dark .hub-chip-teal,
        .fi-theme-dark .hub-chip-teal {
            background: rgba(45, 212, 191, 0.15);
            color: #2dd4bf;
            border-color: rgba(45, 212, 191, 0.3);
        }

        /* Review Sliding Carousel */
        .hub-review-carousel {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.75rem;
            padding-top: 0.25rem;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .hub-review-carousel::-webkit-scrollbar {
            display: none;
        }

        .hub-review-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.15rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.85rem;
            min-width: 300px;
            max-width: 360px;
            flex-shrink: 0;
            scroll-snap-align: start;
        }

        .dark .hub-review-item,
        .fi-theme-dark .hub-review-item {
            background: #1e293b;
            border-color: #334155;
        }
    </style>

    {{-- Banner Header --}}
    <div class="hub-banner-card">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
            <div>
                <span class="hub-chip hub-chip-gold">
                    ★ Community Feedback &amp; Ratings
                </span>
                <h1 class="hub-title" style="color: #ffffff; margin-top: 0.6rem; font-size: 1.5rem;">
                    Submit Platform, Course &amp; Instructor Reviews
                </h1>
                <p style="color: #cbd5e1; font-size: 0.85rem; margin-top: 0.35rem; max-width: 600px;">
                    Rate your learning experience, share constructive feedback, and earn XP towards your leaderboard rank.
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-shrink: 0;">
                <div style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.75rem; padding: 0.65rem 1rem; text-align: center;">
                    <p style="font-size: 1.15rem; font-weight: 800; color: #fde047; margin: 0;">+10 XP</p>
                    <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #e2e8f0; margin: 0;">Per Review</p>
                </div>
                <div style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.75rem; padding: 0.65rem 1rem; text-align: center;">
                    <p style="font-size: 1.15rem; font-weight: 800; color: #fde047; margin: 0;">+3 Coins</p>
                    <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #e2e8f0; margin: 0;">Thinker Store</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="hub-card">
        @if (session('success'))
            <div style="margin-bottom: 1.25rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.65rem; padding: 0.85rem 1rem; color: #065f46; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.1rem; color: #059669;">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="submitReview" style="display: flex; flex-direction: column; gap: 1.35rem;">
            
            {{-- Step 1: Select Review Target --}}
            <div>
                <label class="hub-input-label">
                    1. Select Review Target <span style="color: #ef4444;">*</span>
                </label>
                
                <div class="hub-target-grid">
                    {{-- Platform Option --}}
                    <button type="button"
                            wire:click="setTargetType('platform')"
                            class="hub-target-btn {{ $targetType === 'platform' ? 'active' : '' }}">
                        <div class="hub-target-icon">
                            🏛️
                        </div>
                        <div>
                            <p style="font-weight: 700; font-size: 0.875rem; margin: 0;">Platform</p>
                            <p class="hub-copy" style="margin: 0.15rem 0 0 0; font-size: 0.75rem;">Overall experience</p>
                        </div>
                    </button>

                    {{-- Course Option --}}
                    <button type="button"
                            wire:click="setTargetType('course')"
                            class="hub-target-btn {{ $targetType === 'course' ? 'active' : '' }}">
                        <div class="hub-target-icon">
                            📚
                        </div>
                        <div>
                            <p style="font-weight: 700; font-size: 0.875rem; margin: 0;">Course</p>
                            <p class="hub-copy" style="margin: 0.15rem 0 0 0; font-size: 0.75rem;">Specific curriculum</p>
                        </div>
                    </button>

                    {{-- Instructor Option --}}
                    <button type="button"
                            wire:click="setTargetType('instructor')"
                            class="hub-target-btn {{ $targetType === 'instructor' ? 'active' : '' }}">
                        <div class="hub-target-icon">
                            👨‍🏫
                        </div>
                        <div>
                            <p style="font-weight: 700; font-size: 0.875rem; margin: 0;">Instructor</p>
                            <p class="hub-copy" style="margin: 0.15rem 0 0 0; font-size: 0.75rem;">Tutor or mentor</p>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Step 1b: Dropdown for Course --}}
            @if ($targetType === 'course')
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem;">
                    <label for="selectedCourse" class="hub-input-label">
                        Choose Course to Review <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="selectedCourse"
                            wire:model="selectedCourseId"
                            class="hub-input-field">
                        <option value="">-- Select a course from the catalog --</option>
                        @foreach ($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->code ? "[$c->code] " : '' }}{{ $c->title }}</option>
                        @endforeach
                    </select>
                    @error('selectedCourseId')
                        <p style="color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.35rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Step 1c: Dropdown for Instructor --}}
            @if ($targetType === 'instructor')
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem;">
                    <label for="selectedInstructor" class="hub-input-label">
                        Choose Instructor to Review <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="selectedInstructor"
                            wire:model="selectedInstructorId"
                            class="hub-input-field">
                        <option value="">-- Select an instructor --</option>
                        @foreach ($instructors as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }} {{ $inst->occupation ? "({$inst->occupation})" : '' }}</option>
                        @endforeach
                    </select>
                    @error('selectedInstructorId')
                        <p style="color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.35rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Step 2: Star Rating (Optional) --}}
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label class="hub-input-label" style="margin-bottom: 0;">
                        2. Star Rating (Optional)
                    </label>
                    @if ($rating !== null)
                        <button type="button"
                                wire:click="clearRating"
                                style="background: none; border: none; font-size: 0.75rem; font-weight: 600; color: #0d9488; cursor: pointer; text-decoration: underline;">
                            Clear / Skip Rating
                        </button>
                    @endif
                </div>

                <div class="hub-star-box">
                    <div style="display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                        @for ($s = 1; $s <= 5; $s++)
                            <button type="button"
                                    wire:click="setRating({{ $s }})"
                                    class="hub-star-btn {{ $rating !== null && $s <= $rating ? 'hub-star-filled' : 'hub-star-empty' }}"
                                    title="{{ $s }} Star{{ $s > 1 ? 's' : '' }}">
                                ★
                            </button>
                        @endfor
                    </div>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; font-weight: 700; color: #0d9488;">
                        @if ($rating !== null)
                            {{ match($rating) {
                                5 => '⭐⭐⭐⭐⭐ 5.0 - Exceptional Quality',
                                4 => '⭐⭐⭐⭐ 4.0 - Great Experience',
                                3 => '⭐⭐⭐ 3.0 - Good / Satisfactory',
                                2 => '⭐⭐ 2.0 - Needs Improvement',
                                1 => '⭐ 1.0 - Poor Experience',
                                default => $rating . ' Stars'
                            } }}
                        @else
                            <span style="color: #64748b; font-weight: 500;">No star rating selected (Written review only)</span>
                        @endif
                    </p>
                    @error('rating')
                        <p style="color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Step 3: Written Details (Optional) --}}
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <label class="hub-input-label" style="margin-bottom: 0;">
                    3. Written Review &amp; Commentary (Optional)
                </label>
                
                <div>
                    <label for="reviewTitle" style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem;">
                        Headline / Title (Optional)
                    </label>
                    <input type="text" id="reviewTitle"
                           wire:model="title"
                           placeholder="e.g., Practical assignments and top-tier mentorship!"
                           maxlength="120"
                           class="hub-input-field">
                    @error('title')
                        <p style="color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                        <label for="reviewComment" style="font-size: 0.8rem; font-weight: 600; color: #475569;">
                            Detailed Review &amp; Feedback (Optional)
                        </label>
                        <span style="font-size: 0.7rem; color: #94a3b8;">Rate without review or vice versa</span>
                    </div>
                    <textarea id="reviewComment"
                              wire:model="comment"
                              rows="4"
                              placeholder="Share your detailed impressions, what you learned, and how it helped your journey (or leave empty to submit only a star rating)..."
                              class="hub-input-field"
                              style="resize: vertical;"></textarea>
                    @error('comment')
                        <p style="color: #dc2626; font-size: 0.75rem; font-weight: 600; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Step 4: Anonymous Checkbox & Submit Button --}}
            <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.8rem; color: #475569; font-weight: 500;">
                    <input type="checkbox"
                           wire:model="isAnonymous"
                           style="width: 1.1rem; height: 1.1rem; accent-color: #0f766e; cursor: pointer;">
                    <span>Post anonymously (hide my full name on public cards)</span>
                </label>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="hub-btn-submit">
                    <span wire:loading.remove>
                        ★ Submit Review (+10 XP)
                    </span>
                    <span wire:loading>
                        ⏳ Publishing Review...
                    </span>
                </button>
            </div>

        </form>
    </div>

    {{-- My Submitted Reviews Section --}}
    @if ($myReviews->isNotEmpty())
        <div class="hub-card"
             x-data="{
                 canScrollLeft: false,
                 canScrollRight: true,
                 updateScrollState() {
                     const el = this.$refs.myReviewsCarousel;
                     if (!el) return;
                     this.canScrollLeft = el.scrollLeft > 10;
                     this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
                 },
                 slideNext() {
                     const el = this.$refs.myReviewsCarousel;
                     if (!el) return;
                     el.scrollBy({ left: 340, behavior: 'smooth' });
                 },
                 slidePrev() {
                     const el = this.$refs.myReviewsCarousel;
                     if (!el) return;
                     el.scrollBy({ left: -340, behavior: 'smooth' });
                 }
             }"
             x-init="$nextTick(() => updateScrollState()); setTimeout(() => updateScrollState(), 200);"
             @resize.window="updateScrollState()">
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.85rem;">
                <div>
                    <h3 class="hub-title" style="font-size: 1.15rem;">Your Submitted Reviews</h3>
                    <p class="hub-copy">All ratings and feedback submitted from your account • Slide to view</p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="hub-chip hub-chip-teal">
                        {{ $myReviews->count() }} Total
                    </span>

                    <button type="button"
                            @click="slidePrev()"
                            :disabled="!canScrollLeft"
                            :style="!canScrollLeft ? 'opacity: 0.4; cursor: not-allowed;' : 'cursor: pointer;'"
                            class="hub-btn-muted"
                            style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.75rem;"
                            aria-label="Previous">
                        ‹
                    </button>

                    <button type="button"
                            @click="slideNext()"
                            :disabled="!canScrollRight"
                            :style="!canScrollRight ? 'opacity: 0.4; cursor: not-allowed;' : 'cursor: pointer;'"
                            class="hub-btn-muted"
                            style="width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.75rem;"
                            aria-label="Next">
                        ›
                    </button>
                </div>
            </div>

            <div x-ref="myReviewsCarousel"
                 @scroll.debounce.40ms="updateScrollState()"
                 class="hub-review-carousel">
                @foreach ($myReviews as $myRev)
                    <div class="hub-review-item">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                                <span class="hub-chip hub-chip-teal">
                                    @if ($myRev->reviewable_type === \App\Models\Course::class)
                                        Course: {{ $myRev->reviewable?->title ?: 'Course #' . $myRev->reviewable_id }}
                                    @elseif ($myRev->reviewable_type === \App\Models\User::class)
                                        Instructor: {{ $myRev->reviewable?->name ?: 'Instructor #' . $myRev->reviewable_id }}
                                    @else
                                        Platform Experience
                                    @endif
                                </span>

                                <div style="color: #f59e0b; font-size: 0.85rem; font-weight: 700;">
                                    @if ($myRev->rating !== null)
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span>{{ $i <= $myRev->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    @else
                                        <span style="color: #64748b; font-size: 0.75rem; font-weight: 600; font-style: italic;">Review Only</span>
                                    @endif
                                </div>
                            </div>

                            @if ($myRev->title)
                                <h4 style="margin: 0.6rem 0 0.25rem 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;">{{ $myRev->title }}</h4>
                            @endif

                            @if ($myRev->comment)
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: #475569; line-height: 1.4;">
                                    {{ $myRev->comment }}
                                </p>
                            @else
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                    Rating only (no written commentary)
                                </p>
                            @endif
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 0.6rem; font-size: 0.75rem; color: #94a3b8;">
                            <span>{{ $myRev->created_at->format('M d, Y') }}</span>
                            
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                @if ($myRev->is_verified)
                                    <span style="color: #059669; font-weight: 700; font-size: 0.7rem;">
                                        ✓ Verified
                                    </span>
                                @endif

                                <button type="button"
                                        wire:click="deleteReview({{ $myRev->id }})"
                                        wire:confirm="Are you sure you want to delete this review?"
                                        style="background: none; border: none; color: #dc2626; font-weight: 600; cursor: pointer; padding: 0;">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
