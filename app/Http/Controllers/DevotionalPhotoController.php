<?php

namespace App\Http\Controllers;

use App\Models\DevotionalSubmission;
use Illuminate\Support\Facades\Storage;

/**
 * Serves a devotional photo from the private disk, but only to the editor who
 * uploaded it or to an owner. This keeps personal daily photos off the public
 * web root while still letting them be viewed inside the app.
 */
class DevotionalPhotoController extends Controller
{
    public function show(DevotionalSubmission $devotional)
    {
        $user = auth()->user();

        abort_unless($user->isOwner() || $devotional->worker_id === $user->user_id, 403);
        abort_unless(Storage::disk('local')->exists($devotional->file_path), 404);

        return Storage::disk('local')->response($devotional->file_path);
    }
}
