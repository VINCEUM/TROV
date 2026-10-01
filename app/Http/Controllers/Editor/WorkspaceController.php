<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\DevotionalSubmission;
use Illuminate\Support\Carbon;

class WorkspaceController extends Controller
{
    public function show()
    {
        // The devotional gate: no submission today means no workspace.
        $hasDevotional = DevotionalSubmission::where('worker_id', auth()->id())
            ->whereDate('workday', Carbon::today())
            ->exists();

        if (! $hasDevotional) {
            return redirect()->route('devotional');
        }

        return view('editor.workspace');
    }
}
