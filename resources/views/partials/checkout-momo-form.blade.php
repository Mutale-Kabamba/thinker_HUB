{{-- Inline MoMo phone input partial (used inside expandable provider rows) --}}
<div>
    <label class="form-label">Mobile Money Number</label>
    <div class="input-prefix">
        <span class="input-prefix-label">+260</span>
        <input type="tel" id="phone_number" name="phone_number"
               x-model="phoneNumber"
               placeholder="977 264 054"
               class="form-input">
    </div>
    <p class="mt-1.5 text-[10px] text-slate-400">A simulated USSD push will be sent to this number.</p>
</div>
