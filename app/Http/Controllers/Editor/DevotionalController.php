<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\DevotionalSubmission;
use App\Models\ShiftAssignment;
use Illuminate\Support\Carbon;

class DevotionalController extends Controller
{
    public function show()
    {
        $user  = auth()->user();
        $today = Carbon::today();

        // Already submitted for today? The gate is cleared - go to work.
        $done = DevotionalSubmission::where('worker_id', $user->user_id)
            ->whereDate('workday', $today)
            ->exists();

        if ($done) {
            return redirect()->route('workspace');
        }

        // Give the editor a shift for today if an owner has not assigned one.
        $shift = ShiftAssignment::ensureForToday($user->user_id);

        return view('editor.devotional', compact('shift'));
    }
}
