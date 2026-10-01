<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityCapture;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Endpoints the desktop monitoring component calls: find the editor's open work
 * session, and upload a screen capture with keyboard/mouse activity for it.
 */
class CaptureController extends Controller
{
    /** The signed-in editor's currently open session, if any. */
    public function current(Request $request)
    {
        $session = AttendanceSession::where('worker_id', $request->user()->user_id)
            ->whereDate('clock_in_at', Carbon::today())
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        return response()->json([
            'session' => $session ? [
                'attendance_id' => $session->attendance_id,
                'clock_in_at'   => $session->clock_in_at->toIso8601String(),
            ] : null,
        ]);
    }

    /** Store one screen capture against the editor's open session. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'attendance_id'     => ['required', 'integer'],
            'screenshot'        => ['required', 'image', 'max:12288'], // up to 12 MB
            'keystroke_count'   => ['nullable', 'integer', 'min:0'],
            'mouse_event_count' => ['nullable', 'integer', 'min:0'],
            'activity_level'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        // The session must belong to this editor and still be open.
        $session = AttendanceSession::where('attendance_id', $data['attendance_id'])
            ->where('worker_id', $request->user()->user_id)
            ->whereNull('clock_out_at')
            ->first();

        if (! $session) {
            return response()->json(['message' => 'No matching open session.'], 422);
        }

        $path = $request->file('screenshot')->store('captures', 'local');

        $capture = ActivityCapture::create([
            'work_entry_id'     => null,
            'attendance_id'     => $session->attendance_id,
            'file_path'         => $path,
            'captured_at'       => now(),
            'keystroke_count'   => $data['keystroke_count'] ?? 0,
            'mouse_event_count' => $data['mouse_event_count'] ?? 0,
            'activity_level'    => $data['activity_level'] ?? 0,
        ]);

        return response()->json(['capture_id' => $capture->capture_id], 201);
    }
}
