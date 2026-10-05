<?php

namespace App\Livewire\Owner;

use App\Models\ActivityCapture;
use App\Models\AttendanceSession;
use App\Models\DevotionalSubmission;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The owner admin panel (Chapter 3, use cases: monitor live status and
 * attendance, review devotionals, review screenshots, reports). One Livewire
 * component with a sidebar; every section reads live data from the database.
 */
class AdminPanel extends Component
{
    #[Url]
    public string $section = 'dash';

    public function go(string $section): void
    {
        $this->section = $section;
    }

    public function render()
    {
        $today = Carbon::today();

        $devotionals = DevotionalSubmission::whereDate('workday', $today)->get()->keyBy('worker_id');
        $sessions    = AttendanceSession::whereDate('clock_in_at', $today)->get()->groupBy('worker_id');

        $rows = User::where('role', User::ROLE_EDITOR)->orderBy('name')->get()->map(function ($e) use ($devotionals, $sessions) {
            $dev  = $devotionals->get($e->user_id);
            $sess = $sessions->get($e->user_id)?->sortByDesc('clock_in_at')->first();

            $status = 'none';
            $totalSec = $activeSec = $idleSec = 0;
            $tin = $tout = null;
            if ($sess) {
                $tin  = $sess->clock_in_at;
                $tout = $sess->clock_out_at;
                $totalSec  = ($tout ?? now())->diffInSeconds($sess->clock_in_at);
                $activeSec = $sess->active_seconds;
                $idleSec   = $sess->idle_seconds;
                $status    = $tout ? 'done' : 'active';
            }

            return (object) [
                'id'       => $e->user_id,
                'name'     => $e->name,
                'email'    => $e->email,
                'dev'      => $dev?->submitted_at,
                'devId'    => $dev?->devotional_id,
                'tin'      => $tin,
                'tout'     => $tout,
                'status'   => $status,
                'total'    => $totalSec,
                'active'   => $activeSec,
                'idle'     => $idleSec,
                'shots'    => $sess ? $sess->activityCaptures()->count() : 0,
            ];
        });

        $kpis = [
            'employees' => $rows->count(),
            'active'    => $rows->where('status', 'active')->count(),
            'devs'      => $rows->whereNotNull('dev')->count(),
            'done'      => $rows->where('status', 'done')->count(),
        ];

        $totalActive = $rows->sum('active');
        $totalIdle   = $rows->sum('idle');
        $actPct = ($totalActive + $totalIdle) > 0 ? round($totalActive / ($totalActive + $totalIdle) * 100) : 0;

        $captures = ActivityCapture::whereHas('attendance', fn ($q) => $q->whereDate('clock_in_at', $today))
            ->with('attendance.worker')
            ->latest('captured_at')->take(12)->get();

        return view('livewire.owner.admin-panel', [
            'rows'     => $rows,
            'kpis'     => $kpis,
            'actPct'   => $actPct,
            'shots'    => $rows->sum('shots'),
            'captures' => $captures,
        ]);
    }
}
