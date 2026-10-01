<?php

namespace App\Livewire\Editor;

use App\Models\DevotionalSubmission;
use App\Models\ShiftAssignment;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class Devotional extends Component
{
    use WithFileUploads;

    public $photo;
    public string $title = '';

    public function submit()
    {
        $this->validate([
            'photo' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'title' => ['nullable', 'string', 'max:150'],
        ]);

        $user  = auth()->user();
        $today = Carbon::today();

        // Give the editor a shift for today if an owner has not assigned one.
        $shift = ShiftAssignment::ensureForToday($user->user_id);

        // One submission per workday; the DB unique index is the hard guarantee.
        $already = DevotionalSubmission::where('worker_id', $user->user_id)
            ->whereDate('workday', $today)->exists();
        if ($already) {
            return $this->redirectRoute('workspace', navigate: true);
        }

        $path = $this->photo->store('devotionals', 'local');

        DevotionalSubmission::create([
            'worker_id'           => $user->user_id,
            'shift_assignment_id' => $shift->shift_assignment_id,
            'workday'             => $today,
            'title'               => $this->title ?: null,
            'file_path'           => $path,
            'submitted_at'        => now(),
        ]);

        return $this->redirectRoute('workspace', navigate: true);
    }

    public function render()
    {
        return view('livewire.editor.devotional');
    }
}
