<?php

namespace App\Console\Commands;

use App\Models\ActivityCapture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Enforces the screenshot retention period: deletes activity captures older
 * than the given number of days (default 14) together with their stored image
 * files, so the "kept 14 days, then deleted" promise is actually kept.
 */
class PruneCaptures extends Command
{
    protected $signature = 'captures:prune {--days=14 : Retention period in days}';

    protected $description = 'Delete screen captures older than the retention period and their files';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $old = ActivityCapture::where('captured_at', '<', $cutoff)->get();

        $files = 0;
        foreach ($old as $capture) {
            if ($capture->file_path && Storage::disk('local')->exists($capture->file_path)) {
                Storage::disk('local')->delete($capture->file_path);
                $files++;
            }
            $capture->delete();
        }

        $this->info("Pruned {$old->count()} capture(s) older than {$days} day(s); deleted {$files} file(s).");

        return self::SUCCESS;
    }
}
