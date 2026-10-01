<?php

namespace Tests\Feature;

use App\Livewire\Editor\Workspace;
use App\Models\AttendanceSession;
use App\Models\DevotionalSubmission;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifies the core rule: owners skip the devotional and go to the admin
 * panel; video editors are gated until they submit today's devotional, and
 * the gate is enforced on the server.
 */
class DevotionalGateTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function makeUser(string $role): User
    {
        $this->seq++;

        return User::forceCreate([
            'role'          => $role,
            'name'          => $role . ' ' . $this->seq,
            'email'         => 'user' . $this->seq . '@kpc.test',
            'password_hash' => bcrypt('password'),
            'is_active'     => true,
        ]);
    }

    private function clearGate(User $editor): void
    {
        $shift = ShiftAssignment::create([
            'worker_id'       => $editor->user_id,
            'shift_type'      => 'Morning',
            'scheduled_start' => Carbon::today()->setTime(8, 0),
            'scheduled_end'   => Carbon::today()->setTime(17, 0),
            'assigned_by'     => $editor->user_id,
        ]);
        DevotionalSubmission::create([
            'worker_id'           => $editor->user_id,
            'shift_assignment_id' => $shift->shift_assignment_id,
            'workday'             => Carbon::today(),
            'file_path'           => 'devotionals/test.jpg',
            'submitted_at'        => now(),
        ]);
    }

    // ---- owners never touch the devotional flow ------------------------

    public function test_owner_login_lands_on_admin_not_devotional(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $this->actingAs($owner)->get('/home')->assertRedirect(route('admin.dashboard'));
    }

    public function test_owner_can_open_admin_without_any_devotional(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $this->actingAs($owner)->get('/admin')->assertOk();
    }

    public function test_owner_cannot_reach_devotional_or_workspace(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $this->actingAs($owner)->get('/devotional')->assertRedirect(route('home'));
        $this->actingAs($owner)->get('/workspace')->assertRedirect(route('home'));
    }

    // ---- editors are gated by the devotional ---------------------------

    public function test_editor_without_devotional_is_gated(): void
    {
        $editor = $this->makeUser(User::ROLE_EDITOR);
        $this->actingAs($editor)->get('/workspace')->assertRedirect(route('devotional'));
    }

    public function test_editor_with_devotional_can_open_workspace(): void
    {
        $editor = $this->makeUser(User::ROLE_EDITOR);
        $this->clearGate($editor);
        $this->actingAs($editor)->get('/workspace')->assertOk();
    }

    public function test_editor_cannot_time_in_without_a_devotional(): void
    {
        $editor = $this->makeUser(User::ROLE_EDITOR);

        Livewire::actingAs($editor)->test(Workspace::class)->call('timeIn');

        $this->assertSame(0, AttendanceSession::count());
    }

    // ---- role separation and auth --------------------------------------

    public function test_editor_cannot_reach_owner_admin(): void
    {
        $editor = $this->makeUser(User::ROLE_EDITOR);
        $this->actingAs($editor)->get('/admin')->assertRedirect(route('home'));
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/workspace')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }
}
