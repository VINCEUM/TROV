<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\ShiftAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Seeds Kingdom Production Company as described in Chapter 1: two owners who
 * jointly hold the CEO position, and fourteen video editors. Every account is
 * created with the same demo password so the screens can be signed into.
 */
class DatabaseSeeder extends Seeder
{
    /** The demo password for every seeded account. */
    private const PASSWORD = 'password';

    public function run(): void
    {
        // ---- owners --------------------------------------------------------
        $aileen = $this->user('Owner', 'Aileen Villa', 'aileen@kpc.test');
        $jemeul = $this->user('Owner', 'Jemeul Villa', 'jemeul@kpc.test');

        // ---- fourteen video editors ---------------------------------------
        $names = ['Vince', 'Janreks', 'Jetroy', 'Aiien', 'Kim', 'Rosie', 'Terrence',
                  'Wenchell', 'Jomar', 'Cherry', 'Renjie', 'Colliene', 'Owen', 'Althea'];
        $editors = [];
        foreach ($names as $n) {
            $editors[] = $this->user('Video Editor', $n, strtolower($n) . '@kpc.test');
        }

        // ---- a few clients, projects and tasks for the workspace ----------
        $clients = [
            'Nordic Reels'    => ['Winter Campaign Ads', 'Product Launch Teasers'],
            'Bavaria Media'   => ['Weekly Social Cutdowns'],
            'Lisbon Creative' => ['Brand Story Series', 'Testimonial Edits'],
        ];
        foreach ($clients as $clientName => $projectNames) {
            $client = Client::create(['client_name' => $clientName, 'is_active' => true]);
            foreach ($projectNames as $pn) {
                $project = Project::create([
                    'client_id'    => $client->client_id,
                    'project_name' => $pn,
                    'status'       => 'Active',
                    'created_by'   => $aileen->user_id,
                ]);
                Task::create([
                    'project_id' => $project->project_id,
                    'title'      => $pn . ' - first cut',
                    'brief'      => 'Assemble the first cut from supplied footage and AI-generated segments.',
                    'status'     => 'Assigned',
                ]);
            }
        }

        // ---- a morning shift for each editor for today --------------------
        $today = Carbon::today();
        foreach ($editors as $editor) {
            ShiftAssignment::create([
                'worker_id'       => $editor->user_id,
                'shift_type'      => 'Morning',
                'scheduled_start' => $today->copy()->setTime(8, 0),
                'scheduled_end'   => $today->copy()->setTime(17, 0),
                'assigned_by'     => $aileen->user_id,
            ]);
        }

        $this->command->info('Seeded 2 owners and 14 editors. Password for every account: ' . self::PASSWORD);
    }

    private function user(string $role, string $name, string $email): User
    {
        return User::forceCreate([
            'role'          => $role,
            'name'          => $name,
            'email'         => $email,
            'password_hash' => Hash::make(self::PASSWORD),
            'is_active'     => true,
        ]);
    }
}
