@php
    $hm = fn ($s) => intdiv($s, 3600) . 'h ' . str_pad(intdiv($s % 3600, 60), 2, '0', STR_PAD_LEFT) . 'm';
    $initials = fn ($n) => collect(explode(' ', $n))->filter()->map(fn ($p) => $p[0])->take(2)->implode('');
    $pill = function ($status) {
        return match ($status) {
            'active' => '<span class="state s-green live"><i></i>Active</span>',
            'done'   => '<span class="state s-blue"><i></i>Completed</span>',
            default  => '<span class="state s-grey"><i></i>Not started</span>',
        };
    };
    $book = '<svg viewBox="0 0 64 64"><rect width="64" height="64" fill="#D9E4DC"/><path d="M0 44c10-6 18-6 32-2s22 2 32-4v26H0z" fill="#C3D6C8"/><path d="M32 22c-6-4-14-5-20-3v22c6-2 14-1 20 3 6-4 14-5 20-3V19c-6-2-14-1-20 3z" fill="#FBF8F2" stroke="#AFC2B4" stroke-width="1.2"/><path d="M32 22v22" stroke="#AFC2B4" stroke-width="1.2"/></svg>';
    $sections = [
        'dash' => ['i-grid', 'Dashboard'], 'emp' => ['i-users', 'Employees'],
        'dev' => ['i-book', 'Devotionals'], 'att' => ['i-clock', 'Attendance'],
        'live' => ['i-pulse', 'Live Activity'], 'shots' => ['i-cam', 'Screenshots'],
        'rep' => ['i-chart', 'Reports'], 'set' => ['i-gear', 'Settings'],
    ];
@endphp

<section class="screen admin" wire:poll.15s>
    <nav class="side" aria-label="Admin sections">
        <p class="cap">Admin &middot; {{ auth()->user()->name }}</p>
        @foreach ($sections as $key => [$icon, $label])
            <button type="button" wire:click="go('{{ $key }}')" @if($section === $key) aria-current="true" @endif>
                <svg><use href="#{{ $icon }}"/></svg>{{ $label }}
            </button>
        @endforeach
        <span class="sp"></span>
        <button type="button" onclick="document.getElementById('logoutForm').submit()"><svg><use href="#i-out"/></svg>Sign out</button>
        <form id="logoutForm" method="POST" action="{{ route('logout') }}" hidden>@csrf</form>
    </nav>

    <div class="apanel">
        @switch($section)

        {{-- ============================ DASHBOARD ============================ --}}
        @case('dash')
            <div class="ahead"><div><h2>Dashboard</h2><p class="sub">{{ now()->format('l, j F Y') }} &middot; live</p></div></div>
            <div class="kpis">
                <div class="kpi"><span class="k">Employees</span><span class="n">{{ $kpis['employees'] }}</span></div>
                <div class="kpi ok"><span class="k">Active now</span><span class="n">{{ $kpis['active'] }}<small>/{{ $kpis['employees'] }}</small></span></div>
                <div class="kpi"><span class="k">Devotionals submitted</span><span class="n">{{ $kpis['devs'] }}<small>/{{ $kpis['employees'] }}</small></span></div>
                <div class="kpi"><span class="k">Sessions completed</span><span class="n">{{ $kpis['done'] }}</span></div>
            </div>
            <div class="split">
                <section>
                    <h3 style="font-size:14px;margin-bottom:10px">Live employee status</h3>
                    <div class="list">
                        @foreach ($rows as $r)
                            <div class="li"><span class="av">{{ $initials($r->name) }}</span>
                                <span><span class="nm" style="font-weight:600">{{ $r->name }}</span><br>
                                    <span class="tiny">{{ $r->tin ? 'Time in ' . $r->tin->format('g:i A') : 'No session today' }}</span></span>
                                <span class="right">{!! $pill($r->status) !!}<br><span class="tiny mono">{{ $r->total ? $hm($r->total) : '—' }}</span></span>
                            </div>
                        @endforeach
                    </div>
                </section>
                <section>
                    <h3 style="font-size:14px;margin-bottom:10px">Today at a glance</h3>
                    <div class="bars">
                        <div class="barline"><div class="lb"><span>Active time</span><strong class="num">{{ $actPct }}%</strong></div><div class="meter"><span style="width:{{ $actPct }}%"></span></div></div>
                        <div class="barline"><div class="lb"><span>Idle time</span><strong class="num">{{ 100 - $actPct }}%</strong></div><div class="meter warn"><span style="width:{{ 100 - $actPct }}%"></span></div></div>
                        <div class="li"><span class="ss">Sessions completed</span><span class="right">{{ $kpis['done'] }}</span></div>
                        <div class="li"><span class="ss">Not started</span><span class="right">{{ $rows->where('status', 'none')->count() }}</span></div>
                        <div class="li"><span class="ss">Screenshots captured today</span><span class="right mono">{{ $shots }}</span></div>
                    </div>
                </section>
            </div>
            @break

        {{-- ============================ EMPLOYEES ============================ --}}
        @case('emp')
            <div class="ahead"><div><h2>Employees</h2><p class="sub">{{ $kpis['employees'] }} accounts &middot; role-based access</p></div></div>
            <div class="tablewrap"><table>
                <thead><tr><th>Employee</th><th>Role</th><th>Status today</th><th>Devotional</th><th>Time in</th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td><div class="person"><span class="av">{{ $initials($r->name) }}</span><span><span class="nm">{{ $r->name }}</span><br><span class="em">{{ $r->email }}</span></span></div></td>
                            <td>Video Editor</td><td>{!! $pill($r->status) !!}</td>
                            <td class="mono">{{ $r->dev?->format('g:i A') ?? '—' }}</td>
                            <td class="mono">{{ $r->tin?->format('g:i A') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            @break

        {{-- =========================== DEVOTIONALS =========================== --}}
        @case('dev')
            <div class="ahead"><div><h2>Devotional submissions</h2><p class="sub">{{ now()->format('l, j F Y') }}</p></div></div>
            <div class="tablewrap"><table>
                <thead><tr><th>Employee</th><th>Submitted</th><th>Status</th><th>Photo</th><th></th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td><div class="person"><span class="av">{{ $initials($r->name) }}</span><span><span class="nm">{{ $r->name }}</span></span></div></td>
                            <td class="mono">{{ $r->dev?->format('g:i A') ?? '—' }}</td>
                            <td>@if($r->dev)<span class="state s-green"><i></i>Submitted</span>@else<span class="state s-red"><i></i>Missing</span>@endif</td>
                            <td>@if($r->dev && $r->devId)<a class="thumb" href="{{ route('devotional.photo', $r->devId) }}" target="_blank" title="View full photo" style="display:block;width:56px;height:56px"><img src="{{ route('devotional.photo', $r->devId) }}" alt="Devotional photo" loading="lazy" style="width:100%;height:100%;object-fit:cover"></a>@else — @endif</td>
                            <td>@if($r->dev)<span class="tiny">recorded {{ $r->dev->format('g:i A') }}</span>@else<span class="tiny">Workspace locked</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <p class="tiny">An employee with no submission cannot open the workspace, so no time-in record can exist for them. The rule is enforced on the server, not just hidden in the interface.</p>
            @break

        {{-- =========================== ATTENDANCE =========================== --}}
        @case('att')
            <div class="ahead"><div><h2>Attendance</h2><p class="sub">Time in and time out records</p></div></div>
            <div class="tablewrap"><table>
                <thead><tr><th>Employee</th><th>Devotional</th><th>Time in</th><th>Time out</th><th>Total</th><th>Active</th><th>Idle</th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td><div class="person"><span class="av">{{ $initials($r->name) }}</span><span><span class="nm">{{ $r->name }}</span></span></div></td>
                            <td>@if($r->dev)<span class="state s-green"><i></i>{{ $r->dev->format('g:i A') }}</span>@else<span class="state s-red"><i></i>Missing</span>@endif</td>
                            <td class="mono">{{ $r->tin?->format('g:i A') ?? '—' }}</td>
                            <td class="mono">{{ $r->tout?->format('g:i A') ?? ($r->tin ? 'in session' : '—') }}</td>
                            <td class="mono">{{ $r->total ? $hm($r->total) : '—' }}</td>
                            <td class="mono">{{ $r->total ? $hm($r->active) : '—' }}</td>
                            <td class="mono">{{ $r->total ? $hm($r->idle) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            @break

        {{-- ========================== LIVE ACTIVITY ========================= --}}
        @case('live')
            <div class="ahead"><div><h2>Live activity</h2><p class="sub">Updated from each session &middot; heartbeat every 30 s</p></div></div>
            <div class="tablewrap"><table>
                <thead><tr><th>Employee</th><th>Status</th><th>Session</th><th>Active</th><th>Idle</th><th>Activity level</th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        @php $lvl = ($r->active + $r->idle) > 0 ? round($r->active / ($r->active + $r->idle) * 100) : 0; @endphp
                        <tr>
                            <td><div class="person"><span class="av">{{ $initials($r->name) }}</span><span><span class="nm">{{ $r->name }}</span></span></div></td>
                            <td>{!! $pill($r->status) !!}</td>
                            <td class="mono">@if($r->status === 'active' && $r->tin)<span wire:ignore x-data="{t:{{ $r->tin->timestamp }},n:Math.floor(Date.now()/1000)}" x-init="setInterval(()=>n=Math.floor(Date.now()/1000),1000)" x-text="(()=>{let d=Math.max(0,n-t),h=Math.floor(d/3600),m=Math.floor(d%3600/60),s=d%60;return String(h).padStart(2,'0')+'h '+String(m).padStart(2,'0')+'m '+String(s).padStart(2,'0')+'s';})()">0h 00m 00s</span>@elseif($r->total){{ $hm($r->total) }}@else — @endif</td>
                            <td class="mono">{{ $r->total ? $hm($r->active) : '—' }}</td>
                            <td class="mono">{{ $r->total ? $hm($r->idle) : '—' }}</td>
                            <td class="num"><span class="mini {{ $lvl < 80 ? 'warn' : '' }}"><span style="width:{{ $lvl }}%"></span></span>{{ $r->total ? $lvl . '%' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <p class="tiny">A closed browser is not treated as a time out. When heartbeats stop the session stays open and resumes when the employee reconnects.</p>
            @break

        {{-- =========================== SCREENSHOTS ========================== --}}
        @case('shots')
            <div class="ahead"><div><h2>Screenshots</h2><p class="sub">Captured every 10 minutes while a session runs</p></div></div>
            @if ($captures->isNotEmpty())
                <div class="shot-grid">
                    @foreach ($captures as $c)
                        <figure class="shot">
                            <img src="{{ route('capture.image', $c->capture_id) }}" alt="Screen capture" loading="lazy" style="display:block;width:100%;height:auto">
                            <figcaption><span class="mono">{{ $c->captured_at->format('g:i A') }}</span><span class="mono">{{ $c->attendance?->worker?->name }}</span></figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="tiny">No captures recorded yet. Screenshots are produced by the desktop monitoring component while editors&rsquo; sessions run; they will appear here.</p>
            @endif
            <p class="tiny" style="margin-top:12px">Stored in private storage and opened only through this panel. Retention 14 days. Employees see their own captures in the workspace.</p>
            @break

        {{-- ============================= REPORTS ============================ --}}
        @case('rep')
            <div class="ahead"><div><h2>Reports</h2><p class="sub">Attendance and activity &middot; {{ now()->format('j F Y') }}</p></div></div>
            <div class="kpis">
                <div class="kpi"><span class="k">Devotional compliance</span><span class="n">{{ $kpis['employees'] ? round($kpis['devs'] / $kpis['employees'] * 100) : 0 }}<small>%</small></span></div>
                <div class="kpi"><span class="k">Sessions today</span><span class="n">{{ $kpis['active'] + $kpis['done'] }}</span></div>
                <div class="kpi ok"><span class="k">Overall activity level</span><span class="n">{{ $actPct }}<small>%</small></span></div>
            </div>
            <div class="bars" style="max-width:520px">
                <div class="barline"><div class="lb"><span>Active time</span><strong class="num">{{ $actPct }}%</strong></div><div class="meter"><span style="width:{{ $actPct }}%"></span></div></div>
                <div class="barline"><div class="lb"><span>Idle time</span><strong class="num">{{ 100 - $actPct }}%</strong></div><div class="meter warn"><span style="width:{{ 100 - $actPct }}%"></span></div></div>
            </div>
            <p class="tiny">Reports are generated from the same records shown in Attendance and Live Activity. Export to Excel or PDF can be added on top of these figures.</p>
            @break

        {{-- ============================ SETTINGS ============================ --}}
        @case('set')
            <div class="ahead"><div><h2>Settings</h2><p class="sub">Workspace rules and retention</p></div></div>
            <div class="set">
                <div class="card" style="padding:16px"><span class="ss">Roles</span><br><span class="tt">Owner &middot; Video Editor</span><p class="tiny" style="margin-top:6px">Permissions are enforced on the server for every request.</p></div>
                <div class="card" style="padding:16px"><span class="ss">Devotional rule</span><br><span class="tt">Required before time in</span><p class="tiny" style="margin-top:6px">A work session cannot start until the day&rsquo;s devotional is recorded.</p></div>
                <div class="card" style="padding:16px"><span class="ss">Screenshot retention</span><br><span class="tt">14 days</span><p class="tiny" style="margin-top:6px">Captures are deleted automatically after the retention period.</p></div>
                <div class="card" style="padding:16px"><span class="ss">Idle threshold</span><br><span class="tt">5 minutes</span><p class="tiny" style="margin-top:6px">Heartbeat to the server every 30 seconds.</p></div>
            </div>
            @break
        @endswitch
    </div>
</section>
