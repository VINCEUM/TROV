<?php

namespace App\Http\Controllers;

use App\Models\ActivityCapture;
use Illuminate\Support\Facades\Storage;

/**
 * Serves a stored screen capture from the private disk, only to an owner or to
 * the editor whose session it belongs to.
 */
class CaptureImageController extends Controller
{
    public function show(ActivityCapture $capture)
    {
        $user = auth()->user();
        $ownsIt = $capture->attendance && $capture->attendance->worker_id === $user->user_id;

        abort_unless($user->isOwner() || $ownsIt, 403);
        abort_unless(Storage::disk('local')->exists($capture->file_path), 404);

        return Storage::disk('local')->response($capture->file_path);
    }
}
