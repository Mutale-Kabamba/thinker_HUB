{{-- Inline MoMo phone input partial (used inside expandable provider rows) --}}
<div>
    <label class="form-label">Mobile Money Phone Number</label>
    <div class="input-prefix">
        <span class="input-prefix-label">+260</span>
        <input type="tel" id="phone_number" name="phone_number"
               x-model="phoneNumber"
               placeholder="97X XXX XXX"
               maxlength="10"
               class="form-input">
    </div>
    <p class="mt-1.5 text-[11px] text-slate-500">A payment authorization prompt will be sent to this phone number.</p>
</div>
