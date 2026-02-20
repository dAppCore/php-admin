<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Models\WaitlistEntry;
use Core\Tenant\Notifications\WaitlistInviteNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Website\Hub\Concerns\HasRateLimiting;

#[Title('Waitlist')]
class WaitlistManager extends Component
{
    use HasRateLimiting;
    use WithPagination;

    // Filters
    public string $search = '';

    public string $statusFilter = '';

    public string $interestFilter = '';

    // Bulk actions
    public array $selected = [];

    public bool $selectAll = false;

    // Stats
    public int $totalCount = 0;

    public int $pendingCount = 0;

    public int $invitedCount = 0;

    public int $convertedCount = 0;

    /**
     * Authorize access - Hades tier only.
     */
    public function mount(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for waitlist management.');
        }

        $this->refreshStats();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getFilteredQuery()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Send invite to a single entry.
     */
    public function sendInvite(int $id): void
    {
        $this->rateLimit('admin-mutation', 10, function () use ($id) {
            $entry = WaitlistEntry::findOrFail($id);

            if ($entry->isInvited()) {
                session()->flash('error', 'This person has already been invited.');

                return;
            }

            $entry->generateInviteCode();
            $entry->notify(new WaitlistInviteNotification($entry));

            session()->flash('message', "Invite sent to {$entry->email}");
            $this->refreshStats();
        });
    }

    /**
     * Send invites to selected entries.
     */
    public function sendBulkInvites(): void
    {
        $this->rateLimit('admin-mutation', 10, function () {
            $entries = WaitlistEntry::whereIn('id', $this->selected)
                ->whereNull('invited_at')
                ->get();

            if ($entries->isEmpty()) {
                session()->flash('error', 'No pending entries selected.');

                return;
            }

            $count = 0;
            foreach ($entries as $entry) {
                $entry->generateInviteCode();
                $entry->notify(new WaitlistInviteNotification($entry));
                $count++;
            }

            $this->selected = [];
            $this->selectAll = false;

            session()->flash('message', "Sent {$count} invite(s) successfully.");
            $this->refreshStats();
        });
    }

    /**
     * Resend invite to an already-invited entry.
     */
    public function resendInvite(int $id): void
    {
        $entry = WaitlistEntry::findOrFail($id);

        if (! $entry->isInvited()) {
            session()->flash('error', 'This person has not been invited yet.');

            return;
        }

        if ($entry->hasConverted()) {
            session()->flash('error', 'This person has already registered.');

            return;
        }

        $entry->notify(new WaitlistInviteNotification($entry));

        session()->flash('message', "Invite resent to {$entry->email}");
    }

    /**
     * Delete a waitlist entry.
     */
    public function delete(int $id): void
    {
        $this->rateLimit('admin-mutation', 10, function () use ($id) {
            $entry = WaitlistEntry::findOrFail($id);

            if ($entry->hasConverted()) {
                session()->flash('error', 'Cannot delete entries that have converted to users.');

                return;
            }

            $entry->delete();

            session()->flash('message', 'Entry deleted.');
            $this->refreshStats();
        });
    }

    /**
     * Add manual note to entry.
     */
    public function addNote(int $id, string $note): void
    {
        $entry = WaitlistEntry::findOrFail($id);
        $entry->update(['notes' => $note]);

        session()->flash('message', 'Note saved.');
    }

    /**
     * Export waitlist as CSV.
     */
    public function export()
    {
        return $this->rateLimit('admin-export', 5, function () {
            $entries = $this->getFilteredQuery()->get();

            $csv = "Email,Name,Interest,Source,Status,Signed Up,Invited,Registered\n";

            foreach ($entries as $entry) {
                $status = $entry->hasConverted() ? 'Converted' : ($entry->isInvited() ? 'Invited' : 'Pending');
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $entry->email,
                    $entry->name ?? '',
                    $entry->interest ?? '',
                    $entry->source ?? '',
                    $status,
                    $entry->created_at->format('Y-m-d'),
                    $entry->invited_at?->format('Y-m-d') ?? '',
                    $entry->registered_at?->format('Y-m-d') ?? ''
                );
            }

            return response()->streamDownload(function () use ($csv) {
                echo $csv;
            }, 'waitlist-export-'.now()->format('Y-m-d').'.csv', [
                'Content-Type' => 'text/csv',
            ]);
        });
    }

    protected function refreshStats(): void
    {
        $this->totalCount = WaitlistEntry::count();
        $this->pendingCount = WaitlistEntry::pending()->count();
        $this->invitedCount = WaitlistEntry::invited()->count();
        $this->convertedCount = WaitlistEntry::converted()->count();
    }

    protected function getFilteredQuery()
    {
        return WaitlistEntry::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('email', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'pending', fn ($q) => $q->pending())
            ->when($this->statusFilter === 'invited', fn ($q) => $q->invited())
            ->when($this->statusFilter === 'converted', fn ($q) => $q->converted())
            ->when($this->interestFilter, fn ($q) => $q->where('interest', $this->interestFilter))
            ->latest();
    }

    #[Computed]
    public function entries()
    {
        return $this->getFilteredQuery()->paginate(25);
    }

    #[Computed]
    public function interests(): array
    {
        return WaitlistEntry::select('interest')
            ->whereNotNull('interest')
            ->distinct()
            ->pluck('interest')
            ->mapWithKeys(fn ($i) => [$i => ucfirst($i)])
            ->all();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            'pending' => 'Pending invite',
            'invited' => 'Invited (not registered)',
            'converted' => 'Converted to user',
        ];
    }

    #[Computed]
    public function tableColumns(): array
    {
        return [
            ['label' => '', 'width' => 'w-12'],
            'Email',
            'Name',
            'Interest',
            'Source',
            ['label' => 'Status', 'align' => 'center'],
            'Signed up',
            ['label' => 'Actions', 'align' => 'center'],
        ];
    }

    #[Computed]
    public function tableRows(): array
    {
        return $this->entries->map(function ($e) {
            // Status badge
            if ($e->hasConverted()) {
                $statusBadge = ['badge' => 'Converted', 'color' => 'green'];
                $statusExtra = $e->user ? ['muted' => $e->registered_at->diffForHumans()] : null;
            } elseif ($e->isInvited()) {
                $statusBadge = ['badge' => 'Invited', 'color' => 'blue'];
                $statusExtra = ['muted' => $e->invited_at->diffForHumans()];
            } else {
                $statusBadge = ['badge' => 'Pending', 'color' => 'amber'];
                $statusExtra = null;
            }

            // Actions
            $actions = [];
            if ($e->hasConverted()) {
                if ($e->user) {
                    $actions[] = ['icon' => 'user', 'href' => route('admin.platform.user', $e->user_id), 'title' => 'View user'];
                }
            } elseif ($e->isInvited()) {
                $actions[] = ['icon' => 'arrow-path', 'click' => "resendInvite({$e->id})", 'title' => 'Resend invite'];
            } else {
                $actions[] = ['icon' => 'paper-airplane', 'click' => "sendInvite({$e->id})", 'title' => 'Send invite', 'variant' => 'primary'];
            }
            if (! $e->hasConverted()) {
                $actions[] = ['icon' => 'trash', 'click' => "delete({$e->id})", 'confirm' => 'Are you sure you want to delete this waitlist entry?', 'title' => 'Delete', 'class' => 'text-red-600'];
            }

            // Checkbox cell (custom HTML)
            $checkboxCell = ! $e->hasConverted()
                ? ['html' => '<input type="checkbox" wire:model.live="selected" value="'.$e->id.'" class="rounded">']
                : '';

            return [
                $checkboxCell,
                [
                    'lines' => array_filter([
                        ['bold' => $e->email],
                        $e->invite_code ? ['mono' => $e->invite_code] : null,
                    ]),
                ],
                $e->name ?? ['muted' => '-'],
                $e->interest ? ['badge' => ucfirst($e->interest), 'color' => 'purple'] : ['muted' => '-'],
                ['muted' => $e->source ?? 'direct'],
                $statusExtra ? ['lines' => [$statusBadge, $statusExtra]] : $statusBadge,
                [
                    'lines' => [
                        ['bold' => $e->created_at->format('d M Y')],
                        ['muted' => $e->created_at->diffForHumans()],
                    ],
                ],
                ['actions' => $actions],
            ];
        })->all();
    }

    public function render()
    {
        return view('hub::admin.waitlist-manager')
            ->layout('hub::admin.layouts.app', ['title' => 'Waitlist']);
    }
}
