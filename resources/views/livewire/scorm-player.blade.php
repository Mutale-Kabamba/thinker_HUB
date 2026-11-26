<div
    x-data="{
        status: @js($attempt?->lesson_status ?? 'not attempted'),
        scoreRaw: @js($attempt?->score_raw),
        location: @js($attempt?->lesson_location ?? ''),
        suspendData: @js($attempt?->suspend_data ?? ''),
        commitUrl: @js($commitUrl),
        xapiUrl: @js($xapiUrl),
        csrfToken: @js(csrf_token()),
        cmiStore: {},
        dirtyCmi: {},
        saving: false,
        ready: false,
        launchUrl: @js($launchUrl),
        init() {
            this.cmiStore = {
                'cmi.core.lesson_status': this.status,
                'cmi.completion_status': this.status,
                'cmi.core.lesson_location': this.location || '',
                'cmi.location': this.location || '',
                'cmi.suspend_data': this.suspendData || '',
                'cmi.core.score.raw': this.scoreRaw !== null ? String(this.scoreRaw) : '',
                'cmi.score.raw': this.scoreRaw !== null ? String(this.scoreRaw) : '',
                'cmi.core.student_name': @js(auth()->user()?->name ?? 'Learner'),
                'cmi.core.student_id': @js((string) (auth()->id() ?? '0')),
            };

            const self = this;
            const commitToServer = () => {
                if (!self.commitUrl) return 'false';
                const payloadCmi = Object.keys(self.dirtyCmi).length > 0 ? { ...self.dirtyCmi } : { ...self.cmiStore };
                self.dirtyCmi = {};
                self.saving = true;
                fetch(self.commitUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': self.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ cmi: payloadCmi }),
                })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    if (data.lesson_status) self.status = data.lesson_status;
                    if (data.score_raw !== undefined && data.score_raw !== null) self.scoreRaw = data.score_raw;
                    if (data.completed) {
                        window.dispatchEvent(new CustomEvent('scorm-completed'));
                        if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                            window.Livewire.dispatch('scormCompleted');
                        }
                    }
                })
                .catch(() => {
                    if (window.ThinkerOffline && @js($attempt?->id)) {
                        window.ThinkerOffline.queueAction('scorm_commit', {
                            attempt_id: @js($attempt?->id),
                            cmi: payloadCmi,
                        }).catch(() => {});
                    }
                })
                .finally(() => { self.saving = false; });
                return 'true';
            };

            // SCORM 1.2 Runtime API
            window.API = {
                LMSInitialize: () => 'true',
                LMSFinish: () => commitToServer(),
                LMSGetValue: (el) => self.cmiStore[el] ?? '',
                LMSSetValue: (el, val) => {
                    const str = String(val ?? '');
                    self.cmiStore[el] = str;
                    self.dirtyCmi[el] = str;
                    if (el === 'cmi.core.lesson_status') {
                        self.status = str;
                        self.cmiStore['cmi.completion_status'] = str;
                    }
                    if (el === 'cmi.core.score.raw') {
                        self.scoreRaw = str;
                        self.cmiStore['cmi.score.raw'] = str;
                    }
                    if (el === 'cmi.core.lesson_location') {
                        self.location = str;
                        self.cmiStore['cmi.location'] = str;
                    }
                    return 'true';
                },
                LMSCommit: () => commitToServer(),
                LMSGetLastError: () => '0',
                LMSGetErrorString: () => 'No error',
                LMSGetDiagnostic: () => '',
            };

            // SCORM 2004 Runtime API
            window.API_1484_11 = {
                Initialize: () => 'true',
                Terminate: () => commitToServer(),
                GetValue: (el) => self.cmiStore[el] ?? '',
                SetValue: (el, val) => {
                    const str = String(val ?? '');
                    self.cmiStore[el] = str;
                    self.dirtyCmi[el] = str;
                    if (el === 'cmi.completion_status' || el === 'cmi.success_status') {
                        self.status = str;
                        self.cmiStore['cmi.core.lesson_status'] = str;
                    }
                    if (el === 'cmi.score.raw') {
                        self.scoreRaw = str;
                        self.cmiStore['cmi.core.score.raw'] = str;
                    }
                    if (el === 'cmi.location') {
                        self.location = str;
                        self.cmiStore['cmi.core.lesson_location'] = str;
                    }
                    return 'true';
                },
                Commit: () => commitToServer(),
                GetLastError: () => '0',
                GetErrorString: () => 'No error',
                GetDiagnostic: () => '',
            };

            // xAPI Statement Emitter
            window.thinkerXapi = {
                sendStatement: (statement) => {
                    if (!self.xapiUrl) return Promise.resolve(null);
                    return fetch(self.xapiUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': self.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(statement),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.lesson_status) self.status = data.lesson_status;
                        if (data.score_raw !== undefined && data.score_raw !== null) self.scoreRaw = data.score_raw;
                        if (data.completed && window.Livewire && typeof window.Livewire.dispatch === 'function') {
                            window.Livewire.dispatch('scormCompleted');
                        }
                        return data;
                    });
                },
            };

            this.ready = true;
        }
    }"
    class="space-y-3"
>
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-900/90 px-4 py-2.5 text-xs text-slate-300">
        <div class="flex items-center gap-2">
            <span class="rounded-md bg-teal-900/60 px-2 py-0.5 font-semibold uppercase tracking-wider text-teal-300">
                {{ strtoupper(str_replace('_', ' ', $package->standard)) }}
            </span>
            <span class="font-medium text-white">{{ $package->title }}</span>
        </div>
        <div class="flex items-center gap-3">
            <span>Status: <strong class="text-emerald-400" x-text="status"></strong></span>
            <template x-if="scoreRaw !== null && scoreRaw !== ''">
                <span>Score: <strong class="text-amber-300" x-text="scoreRaw"></strong></span>
            </template>
            <span x-show="saving" class="text-slate-400">Syncing...</span>
        </div>
    </div>

    <div class="relative aspect-video w-full overflow-hidden rounded-xl border border-slate-800 bg-white shadow-2xl">
        <iframe
            :src="ready ? launchUrl : 'about:blank'"
            title="{{ $package->title }}"
            class="h-full w-full border-0"
            allow="autoplay; fullscreen"
            allowfullscreen
        ></iframe>
    </div>
</div>
