<x-layouts.shell title="Devotional - TROV">
    <section class="screen">
        <div class="greet">
            <div>
                <h2>Daily devotional</h2>
                <p class="sub">Submit today&rsquo;s devotional to open your workspace.</p>
            </div>
            <span class="chip">
                <svg class="cal"><use href="#i-cal"/></svg>
                <span><span class="d">{{ now()->format('l, M j, Y') }}</span><br><span class="t">{{ now()->format('g:i A') }}</span></span>
            </span>
        </div>

        <livewire:editor.devotional />
    </section>
</x-layouts.shell>
