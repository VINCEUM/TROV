<?php

namespace App\Livewire\Editor;

use App\Models\AttendanceSession;
use App\Models\DevotionalSubmission;
use App\Models\ShiftAssignment;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * The work session (Chapter 1, Problems 2 and 4). Time in/out are stamped by
 * the server; Work/Break splits the session into active and idle time, which
 * accrue through a 30-second heartbeat while the page is open.
 */
class Workspace extends Component
{
    public ?AttendanceSession $session = null;
    public bool $working = true;

    public function mount(): void
    {
        $this->loadSession();
    }

    private function loadSession(): void
    {
        $this->session = AttendanceSession::where('worker_id', auth()->id())
            ->whereDate('clock_in_at', Carbon::today())
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();
    }

    public function timeIn()
    {
        if ($this->session) {
            return null;
        }
        $user  = auth()->user();
        $today = Carbon::today();

        $devotional = DevotionalSubmission::where('worker_id', $user->user_id)
            ->whereDate('workday', $today)->latest('submitted_at')->first();

        // The devotional gate still stands; the shift is auto-provided if missing.
        if (! $devotional) {
            return null;
        }
        $shift = ShiftAssignment::ensureForToday($user->user_id);

        AttendanceSession::create([
            'worker_id'           => $user->user_id,
            'shift_assignment_id' => $shift->shift_assignment_id,
            'devotional_id'       => $devotional->devotional_id,
            'clock_in_at'         => now(),
            'active_seconds'      => 0,
            'idle_seconds'        => 0,
        ]);

        // Reload the page so the timer starts from a clean render, then Alpine
        // (guarded by wire:ignore) owns it without further Livewire morphs.
        return $this->redirectRoute('workspace', navigate: true);
    }

    public function setWorking(bool $working): void
    {
        $this->working = $working;
    }

    /** Called by wire:poll every 30 seconds while the workspace is open. */
    public function heartbeat(): void
    {
        if (! $this->session || $this->session->clock_out_at) {
            return;
        }
        if ($this->working) {
            $this->session->increment('active_seconds', 30);
        } else {
            $this->session->increment('idle_seconds', 30);
        }
        $this->loadSession();
    }

    public function timeOut()
    {
        if ($this->session) {
            $this->session->update(['clock_out_at' => now()]);
            return $this->redirectRoute('summary', navigate: true);
        }
    }

    public function render()
    {
        $devotional = DevotionalSubmission::where('worker_id', auth()->id())
            ->whereDate('workday', Carbon::today())
            ->latest('submitted_at')->first();

        $captures = $this->session
            ? $this->session->activityCaptures()->latest('captured_at')->take(8)->get()
            : collect();

        return view('livewire.editor.workspace', [
            'devotional' => $devotional,
            'captures'   => $captures,
        ]);
    }
}
