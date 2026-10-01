<div class="card" style="padding:20px;display:flex;flex-direction:column;gap:16px">
    <label class="row-card" style="flex-direction:column;align-items:stretch;gap:14px;padding:22px;border-style:dashed;background:var(--win);cursor:pointer">
        <input type="file" wire:model="photo" accept="image/*" hidden>
        <span style="display:flex;align-items:center;gap:14px">
            <span class="ic"><svg><use href="#i-cam"/></svg></span>
            <span>
                <span class="tt">Upload today&rsquo;s devotional photo</span><br>
                <span class="ss">A photo of your journal page or reading-plan screen &mdash; the same proof you used to send on Discord</span>
            </span>
        </span>
        @if ($photo)
            <span style="display:flex;align-items:center;gap:12px;border-top:1px solid var(--line);padding-top:14px">
                <span class="thumb"><img src="{{ $photo->temporaryUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover"></span>
                <span>
                    <span class="tt" style="font-size:13.5px">{{ $photo->getClientOriginalName() }}</span><br>
                    <span class="ss mono">ready to submit</span>
                </span>
            </span>
        @endif
    </label>

    <div wire:loading wire:target="photo" class="tiny">Uploading preview&hellip;</div>
    @error('photo') <p class="tiny" style="color:var(--red)">{{ $message }}</p> @enderror

    <div class="field">
        <label for="devTitle">Title or passage</label>
        <input type="text" id="devTitle" wire:model="title" placeholder="e.g. The Strength to Keep Going &mdash; Psalm 34:1&ndash;10">
    </div>

    <button class="btn primary block" type="button" wire:click="submit" @disabled(! $photo) wire:loading.attr="disabled" wire:target="submit">
        <span wire:loading.remove wire:target="submit">Submit devotional</span>
        <span wire:loading wire:target="submit">Recording&hellip;</span>
    </button>
    <p class="tiny">Your work session cannot start until this is submitted. The submission time is recorded by the server, so it cannot be back-dated.</p>
</div>
