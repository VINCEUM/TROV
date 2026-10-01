<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use Illuminate\Support\Carbon;

class SummaryController extends Controller
{
    public function show()
    {
        $session = AttendanceSession::where('worker_id', auth()->id())
            ->whereDate('clock_in_at', Carbon::today())
            ->whereNotNull('clock_out_at')
            ->latest('clock_out_at')
            ->first();

        // No completed session today -> nothing to summarise, go back to work.
        if (! $session) {
            return redirect()->route('workspace');
        }

        return view('editor.summary', compact('session'));
    }
}
