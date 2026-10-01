@php
    $total  = $session->clock_out_at->diffInSeconds($session->clock_in_at);
    $active = $session->active_seconds;
    $idle   = $session->idle_seconds;
    $shots  = $session->activityCaptures()->count();
@endphp
<x-layouts.shell title="Session complete - TROV">
    <section class="screen">
        <div class="greet">
            <div>
                <h2>Session complete</h2>
                <p class="sub">Your work session has been saved and sent to the admin dashboard.</p>
            </div>
            <span class="chip">
                <svg class="cal"><use href="#i-cal"/></svg>
                <span><span class="d">{{ $session->clock_out_at->format('l, M j, Y') }}</span><br><span class="t">{{ $session->clock_out_at->format('g:i A') }}</span></span>
            </span>
        </div>

        <div class="sum-grid">
            <div class="sum"><span class="k">Time In</span><span class="v">{{ $session->clock_in_at->format('g:i A') }}</span></div>
            <div class="sum"><span class="k">Time Out</span><span class="v">{{ $session->clock_out_at->format('g:i A') }}</span></div>
            <div class="sum"><span class="k">Total Session</span><span class="v">{{ gmdate('H:i:s', $total) }}</span></div>
            <div class="sum"><span class="k">Active</span><span class="v">{{ gmdate('H:i:s', $active) }}</span></div>
            <div class="sum"><span class="k">Idle</span><span class="v">{{ gmdate('H:i:s', $idle) }}</span></div>
        </div>

        <div class="row-card">
            <span class="ic"><svg><use href="#i-cam"/></svg></span>
            <span><span class="tt">{{ $shots }} screenshot{{ $shots === 1 ? '' : 's' }} captured</span><br>
                <span class="ss">Devotional submitted &middot; session marked COMPLETED</span></span>
        </div>

        <p class="tiny">This record is now read-only. Corrections have to be made by an admin, and the change is logged against their account.</p>

        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn primary" href="{{ route('workspace') }}">Start a new session</a>
            <button class="btn quiet" type="button" onclick="document.getElementById('logoutForm').submit()"><svg><use href="#i-out"/></svg>Sign out</button>
        </div>
        <form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden>@csrf</form>
    </section>
</x-layouts.shell>
