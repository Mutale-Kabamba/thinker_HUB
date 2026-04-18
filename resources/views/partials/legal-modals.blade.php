{{-- Legal Modals (Privacy, Cookies, T&Cs) + Cookie Consent Banner --}}
<div x-data="{
    legalModal: null,
    cookieConsent: localStorage.getItem('thub_cookie_consent'),
    acceptCookies() {
        localStorage.setItem('thub_cookie_consent', 'accepted');
        document.cookie = 'thub_cookie_consent=accepted;path=/;max-age=' + (365*24*60*60) + ';SameSite=Lax';
        this.cookieConsent = 'accepted';
    },
    declineCookies() {
        localStorage.setItem('thub_cookie_consent', 'declined');
        document.cookie = 'thub_cookie_consent=declined;path=/;max-age=' + (365*24*60*60) + ';SameSite=Lax';
        this.cookieConsent = 'declined';
    }
}" @open-legal.window="legalModal = $event.detail">

    {{-- ========== COOKIE CONSENT BANNER ========== --}}
    <div
        x-show="!cookieConsent"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 inset-x-0 z-[10000] p-4 sm:p-6"
    >
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:flex sm:items-center sm:gap-6">
            <div class="flex-1 text-sm text-slate-600 leading-relaxed">
                <p class="font-semibold text-slate-900 mb-1">We value your privacy</p>
                <p>We use essential cookies to keep this platform running and optional analytics cookies to improve your experience. By clicking "Accept All", you consent to our use of cookies as described in our
                    <button type="button" @click="legalModal = 'cookies'" class="text-teal-700 underline underline-offset-2 hover:text-teal-900">Cookie Policy</button>.
                </p>
            </div>
            <div class="mt-4 flex gap-3 sm:mt-0 sm:shrink-0">
                <button
                    type="button"
                    @click="declineCookies()"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >Essential Only</button>
                <button
                    type="button"
                    @click="acceptCookies()"
                    class="rounded-xl bg-[#0a2d27] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#11443c]"
                >Accept All</button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL OVERLAY ========== --}}
    <div
        x-show="legalModal"
        x-cloak
        @keydown.escape.window="legalModal = null"
        class="fixed inset-0 z-[10001] flex items-center justify-center bg-slate-950/60 p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.outside="legalModal = null"
            class="relative w-full max-w-2xl max-h-[85vh] overflow-hidden rounded-2xl bg-white shadow-2xl flex flex-col"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            {{-- Modal header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">
                    <span x-show="legalModal === 'privacy'">Privacy Policy</span>
                    <span x-show="legalModal === 'cookies'">Cookie Policy</span>
                    <span x-show="legalModal === 'terms'">Terms &amp; Conditions</span>
                </h2>
                <button type="button" @click="legalModal = null" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5 text-sm text-slate-700 leading-relaxed space-y-4">

                {{-- ==================== PRIVACY POLICY ==================== --}}
                <template x-if="legalModal === 'privacy'">
                    <div>
                        <p class="text-xs text-slate-500">Last updated: {{ now()->format('F j, Y') }}</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">1. Introduction</h3>
                        <p>think.er HUB ("we", "us", "our") is committed to protecting the personal data of all users in compliance with the <strong>Data Protection Act No. 3 of 2021</strong> of the Republic of Zambia and the <strong>Cyber Security and Cyber Crimes Act No. 2 of 2021</strong>.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">2. Data We Collect</h3>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Account information:</strong> full name, email address, phone number, and role (student/instructor).</li>
                            <li><strong>Academic data:</strong> course enrolments, assignment and assessment submissions, grades, and learning progress.</li>
                            <li><strong>Technical data:</strong> IP address, browser type, device information, and access logs collected automatically.</li>
                            <li><strong>Communication data:</strong> messages sent through our contact forms or support channels.</li>
                        </ul>

                        <h3 class="mt-4 text-base font-bold text-slate-900">3. Legal Basis &amp; Purpose</h3>
                        <p>Under the Data Protection Act 2021, we process your personal data on the following lawful bases:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Consent</strong> — when you create an account or accept cookies.</li>
                            <li><strong>Contractual necessity</strong> — to deliver the educational services you enrol for.</li>
                            <li><strong>Legitimate interest</strong> — to improve platform security and prevent fraud as outlined in the Cyber Security and Cyber Crimes Act 2021.</li>
                        </ul>

                        <h3 class="mt-4 text-base font-bold text-slate-900">4. Your Rights</h3>
                        <p>In accordance with Part IV of the Data Protection Act 2021, you have the right to:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Access the personal data we hold about you.</li>
                            <li>Request correction of inaccurate data.</li>
                            <li>Request erasure of your data (subject to legal retention requirements).</li>
                            <li>Object to or restrict processing of your data.</li>
                            <li>Data portability — receive your data in a structured, machine-readable format.</li>
                            <li>Lodge a complaint with the <strong>Office of the Data Protection Commissioner</strong> of Zambia.</li>
                        </ul>

                        <h3 class="mt-4 text-base font-bold text-slate-900">5. Data Security</h3>
                        <p>We implement appropriate technical and organisational measures, including encrypted connections (TLS), secure password hashing, role-based access controls, and regular security audits as guided by the Cyber Security and Cyber Crimes Act 2021.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">6. Data Sharing &amp; Transfers</h3>
                        <p>We do not sell your personal data. Data may be shared only with:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Authorised instructors and administrators for educational delivery.</li>
                            <li>Law enforcement agencies where required by Zambian law.</li>
                        </ul>
                        <p>Any cross-border data transfer complies with Part VII of the Data Protection Act 2021.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">7. Data Retention</h3>
                        <p>We retain personal data only for as long as necessary to fulfil the purposes described above, or as required by applicable Zambian legislation. Academic records may be retained for a reasonable period after account closure for certification verification.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">8. Contact</h3>
                        <p>For any data protection enquiries, contact us at: <a href="mailto:thinker.learn@gmail.com" class="text-teal-700 underline">thinker.learn@gmail.com</a></p>
                        <p>Address: 10A Off Natwange Street, Airport, Livingstone, Zambia</p>
                    </div>
                </template>

                {{-- ==================== COOKIE POLICY ==================== --}}
                <template x-if="legalModal === 'cookies'">
                    <div>
                        <p class="text-xs text-slate-500">Last updated: {{ now()->format('F j, Y') }}</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">1. What Are Cookies?</h3>
                        <p>Cookies are small text files stored on your device when you visit a website. They help us provide a functional and personalised experience.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">2. Cookies We Use</h3>
                        <div class="hub-legal-table-wrap mt-2 overflow-x-auto">
                        <table class="w-full text-xs border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Cookie</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-700">Purpose</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr>
                                    <td class="px-3 py-2">Session cookie</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Keeps you logged in and protects against CSRF attacks.</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2">XSRF-TOKEN</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Cross-site request forgery protection.</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2">thub_cookie_consent</td>
                                    <td class="px-3 py-2">Essential</td>
                                    <td class="px-3 py-2">Remembers your cookie consent choice.</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <h3 class="mt-4 text-base font-bold text-slate-900">3. Managing Cookies</h3>
                        <p>You can control cookies through your browser settings. Disabling essential cookies may prevent parts of the platform from functioning correctly.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">4. Consent</h3>
                        <p>In accordance with the <strong>Data Protection Act No. 3 of 2021</strong>, we obtain your consent before setting any non-essential cookies. You may change your preference at any time by clearing your browser cookies and revisiting the site.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">5. Contact</h3>
                        <p>Questions about our cookie practices? Email us at <a href="mailto:thinker.learn@gmail.com" class="text-teal-700 underline">thinker.learn@gmail.com</a>.</p>
                    </div>
                </template>

                {{-- ==================== TERMS & CONDITIONS ==================== --}}
                <template x-if="legalModal === 'terms'">
                    <div>
                        <p class="text-xs text-slate-500">Last updated: {{ now()->format('F j, Y') }}</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">1. Acceptance of Terms</h3>
                        <p>By accessing or using think.er HUB, you agree to be bound by these Terms &amp; Conditions. If you do not agree, please do not use the platform.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">2. Eligibility</h3>
                        <p>You must be at least 16 years old to create an account. Users under 18 must have parental or guardian consent.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">3. User Accounts</h3>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                            <li>You agree to provide accurate and complete information during registration.</li>
                            <li>We reserve the right to suspend or terminate accounts that violate these terms.</li>
                        </ul>

                        <h3 class="mt-4 text-base font-bold text-slate-900">4. Acceptable Use</h3>
                        <p>In compliance with the <strong>Cyber Security and Cyber Crimes Act No. 2 of 2021</strong>, you agree not to:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Attempt unauthorised access to any part of the platform or other users' accounts.</li>
                            <li>Upload malicious software, engage in phishing, or distribute harmful content.</li>
                            <li>Use the platform for any unlawful purpose under Zambian law.</li>
                            <li>Share, copy, or redistribute course materials without written permission.</li>
                            <li>Engage in cyberbullying, harassment, or hate speech.</li>
                        </ul>

                        <h3 class="mt-4 text-base font-bold text-slate-900">5. Intellectual Property</h3>
                        <p>All course content, materials, logos, and platform code are the property of think.er HUB or its content creators. Unauthorised reproduction is prohibited under the <strong>Copyright and Performance Rights Act</strong> of Zambia.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">6. Fees &amp; Payments</h3>
                        <p>Course fees are displayed on each course page. All payments are non-refundable unless otherwise stated. We reserve the right to change fees with reasonable notice.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">7. Limitation of Liability</h3>
                        <p>think.er HUB is provided "as is." We make no warranties regarding uninterrupted access or error-free operation. Our liability is limited to the fees paid for the specific course in question.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">8. Governing Law</h3>
                        <p>These terms are governed by the laws of the Republic of Zambia. Any disputes shall be subject to the jurisdiction of the courts of Zambia.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">9. Changes to Terms</h3>
                        <p>We may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>

                        <h3 class="mt-4 text-base font-bold text-slate-900">10. Contact</h3>
                        <p>For questions about these terms, contact: <a href="mailto:thinker.learn@gmail.com" class="text-teal-700 underline">thinker.learn@gmail.com</a></p>
                        <p>Address: 10A Off Natwange Street, Airport, Livingstone, Zambia</p>
                    </div>
                </template>

            </div>

            {{-- Modal footer --}}
            <div class="border-t border-slate-200 px-6 py-4 flex justify-end">
                <button type="button" @click="legalModal = null" class="rounded-xl bg-[#0a2d27] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#11443c]">Close</button>
            </div>
        </div>
    </div>

</div>
