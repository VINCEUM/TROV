@php
    $book = '<svg viewBox="0 0 64 64" aria-hidden="true"><rect width="64" height="64" fill="#D9E4DC"/><path d="M0 44c10-6 18-6 32-2s22 2 32-4v26H0z" fill="#C3D6C8"/><path d="M32 22c-6-4-14-5-20-3v22c6-2 14-1 20 3 6-4 14-5 20-3V19c-6-2-14-1-20 3z" fill="#FBF8F2" stroke="#AFC2B4" stroke-width="1.2" stroke-linejoin="round"/><path d="M32 22v22" stroke="#AFC2B4" stroke-width="1.2"/><path d="M17 26h9M17 30h9M17 34h7M38 26h9M38 30h9M38 34h7" stroke="#CBD8CD" stroke-width="1.4" stroke-linecap="round"/><circle cx="52" cy="14" r="6" fill="#B9D8C4"/></svg>';
    $activeSec = $session->active_seconds ?? 0;
    $idleSec   = $session->idle_seconds ?? 0;
    $pct       = ($activeSec + $idleSec) > 0 ? round($activeSec / ($activeSec + $idleSec) * 100) : 0;
@endphp

<div class="screen" @if($session && !$session->clock_out_at) wire:poll.30s="heartbeat" @endif>
    <div class="greet">
        <div>
            <h2><span>{{ now()->hour < 12 ? 'Good morning,' : (now()->hour < 18 ? 'Good afternoon,' : 'Good evening,') }}</span>{{ auth()->user()->name }} &#128075;</h2>
            <p class="sub">Stay focused. You&rsquo;re doing great!</p>
        </div>
        <span class="chip">
            <svg class="cal"><use href="#i-cal"/></svg>
            <span><span class="d">{{ now()->format('l, M j, Y') }}</span><br><span class="t">{{ now()->format('g:i A') }}</span></span>
        </span>
    </div>

    {{-- ---- session card + stats (shared work/break timer) ------------ --}}
    <div style="display:contents"
        @if ($session) wire:ignore x-data="sessionCard({ sid: {{ $session->clock_in_at->timestamp }}, workBase: {{ $activeSec }}, breakBase: {{ $idleSec }}, working: {{ $working ? 'true' : 'false' }} })" x-init="boot()" @endif>

        <div class="session">
            @if ($session)
                <span class="state" :class="working ? 's-green live' : 's-amber'"><i></i><span x-text="working ? 'Working' : 'On break'">Working</span></span>
                <span class="timer" x-text="fmt(work)">00:00:00</span>
                <span class="lbl">Work Session</span>
                <div class="toggle">
                    <button type="button" @click="setWorking(true)" :aria-pressed="working ? 'true' : 'false'"><svg><use href="#i-play"/></svg>Working</button>
                    <button type="button" @click="setWorking(false)" :aria-pressed="working ? 'false' : 'true'"><svg><use href="#i-pause"/></svg>Break</button>
                </div>
                <p class="note" x-text="working ? 'Your time is running. Hit Break to pause it.' : 'On break — the work timer is paused. Hit Working to resume.'">Your time is running. Hit Break to pause it.</p>
            @else
                <span class="state s-grey"><i></i>Not started</span>
                <span class="timer">00:00:00</span>
                <span class="lbl">Work Session</span>
                <p class="note">You are cleared to start. Timing in stamps the exact server time against today&rsquo;s shift.</p>
                <button type="button" class="btn primary" style="margin-top:12px;min-width:200px" wire:click="timeIn">Time in</button>
            @endif
        </div>

        <div class="stats">
            <div class="stat"><span class="h"><svg><use href="#i-clock"/></svg>Time In</span><span class="v">{{ $session ? $session->clock_in_at->format('g:i A') : '—' }}</span></div>
            <div class="stat"><span class="h"><svg><use href="#i-clock"/></svg>Total Session</span><span class="v">@if ($session)<span x-text="fmt(total)">00:00:00</span>@else — @endif</span></div>
            <div class="stat"><span class="h"><svg><use href="#i-target"/></svg>Active Time</span><span class="v">@if ($session)<span x-text="fmt(work)">00:00:00</span>@else — @endif</span></div>
            <div class="stat"><span class="h"><svg><use href="#i-moon"/></svg>Idle Time</span><span class="v">@if ($session)<span x-text="fmt(brk)">00:00:00</span>@else — @endif</span></div>
        </div>
    </div>

    {{-- ---- devotional row --------------------------------------------- --}}
    <div class="row-card">
        <span class="thumb">{!! $book !!}</span>
        <span>
            <span class="ss">Today&rsquo;s Devotional</span><br>
            <span class="tt">{{ $devotional?->title ?: 'Devotional submitted' }}</span><br>
            <span class="ss">Submitted &middot; {{ $devotional?->submitted_at?->format('M j · g:i A') }}</span>
        </span>
        @if ($devotional)
            <span class="right"><a class="btn sm" href="{{ route('devotional.photo', $devotional->devotional_id) }}" target="_blank" rel="noopener"><svg><use href="#i-eye"/></svg>View</a></span>
        @endif
    </div>

    {{-- ---- activity drawer -------------------------------------------- --}}
    <div x-data="{ open:false }">
        <button class="row-card" type="button" @click="open=!open" :aria-expanded="open">
            <span class="ic"><svg><use href="#i-laptop"/></svg></span>
            <span><span class="tt">Activity Tracking</span><br><span class="ss">Mouse &bull; Keyboard &bull; App activity</span></span>
            <span class="right">
                @if ($session && $working)<span class="state s-green"><i></i>Active</span>@else<span class="state s-grey"><i></i>Paused</span>@endif
                <svg class="chev" :class="open && 'open'"><use href="#i-chev"/></svg>
            </span>
        </button>
        <div class="drawer" x-show="open" x-collapse>
            <div class="barline" style="margin-bottom:14px">
                <div class="lb"><span>Activity level this session</span><strong class="num">{{ $pct }}%</strong></div>
                <div class="meter {{ $pct < 70 ? 'warn' : '' }}"><span style="width:{{ $pct }}%"></span></div>
            </div>
            <div class="split" style="gap:12px">
                <div class="list">
                    <div class="li"><span class="ss">Active time recorded</span><span class="right mono">{{ gmdate('H:i:s', $activeSec) }}</span></div>
                    <div class="li"><span class="ss">Idle threshold</span><span class="right mono">5 minutes</span></div>
                    <div class="li"><span class="ss">Heartbeat to server</span><span class="right mono">every 30 s</span></div>
                </div>
                <p class="tiny">trov records that input happened, not what you typed. Activity level is a signal of time at the desk &mdash; it is not a productivity score.</p>
            </div>
        </div>
    </div>

    {{-- ---- screenshots drawer ----------------------------------------- --}}
    <div x-data="{ open:false }">
        <button class="row-card accent" type="button" @click="open=!open" :aria-expanded="open">
            <span class="ic"><svg><use href="#i-cam"/></svg></span>
            <span><span class="tt">Screenshots</span><br><span class="ss">Captured every 10 minutes</span></span>
            <span class="right">
                <span class="state s-green"><svg style="width:14px;height:14px"><use href="#i-clock"/></svg>{{ $session && $working ? 'Next in ~10 min' : 'Paused' }}</span>
                <svg class="chev" :class="open && 'open'"><use href="#i-chev"/></svg>
            </span>
        </button>
        <div class="drawer" x-show="open" x-collapse>
            @if ($captures->isNotEmpty())
                <div class="shot-grid" id="shotGrid">
                    @foreach ($captures as $c)
                        <figure class="shot">
                            <img src="{{ route('capture.image', $c->capture_id) }}" alt="Screen capture" loading="lazy" style="display:block;width:100%;height:auto">
                            <figcaption><span class="mono">{{ $c->captured_at->format('g:i A') }}</span><span class="mono">{{ $c->activity_level }}%</span></figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="tiny">No captures yet. While your session runs, the desktop monitoring component sends one screenshot about every 10 minutes; they will appear here and in the admin panel.</p>
            @endif
            <p class="tiny" style="margin-top:12px">Captures pause while you are on break and stop at time out. Kept 14 days, then deleted. Only you and an authorised admin can open them.</p>
        </div>
    </div>

    {{-- ---- footer ------------------------------------------------------ --}}
    <div class="foot">
        <span class="fb">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 1.6c.9 4.4 2.6 6.2 7 7.1-3.1.6-4.9 1.8-6 3.7-1.1 1.9-1 4.2-1 6.4-.5-3-1.3-5.1-2.9-6.5C7.5 10.9 5.2 10.2 2 9.7c3.5-.7 5.5-1.6 6.9-3.1C10.2 5.2 11 3.7 12 1.6z" fill="currentColor"/></svg>
            trov
        </span>
        <span class="fs">Work with purpose.</span>
        <span class="fr">
            @if ($session)
                <button class="btn sm" type="button" wire:click="timeOut">Time out</button>
            @endif
            <button class="btn sm quiet" type="button" onclick="document.getElementById('logoutForm').submit()"><svg><use href="#i-out"/></svg>Sign out</button>
        </span>
    </div>

    <form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden>@csrf</form>
</div>
