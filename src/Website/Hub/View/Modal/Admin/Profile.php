<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Enums\UserTier;
use Core\Tenant\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    public string $userName = '';

    public string $userEmail = '';

    public string $userInitials = '';

    public string $userTier = '';

    public string $tierColor = '';

    public ?string $memberSince = null;

    // Quotas - read from cached_stats or defaults
    public array $quotas = [];

    // Recent Activity
    public array $recentActivity = [];

    // Service Stats
    public array $serviceStats = [];

    public function mount(): void
    {
        $user = User::findOrFail(Auth::id());
        $appUser = \Core\Tenant\Models\User::find(Auth::id());

        $this->userName = $user->name ?? 'User';
        $this->userEmail = $user->email ?? '';
        $this->userInitials = collect(explode(' ', $this->userName))
            ->map(fn ($n) => strtoupper(substr($n, 0, 1)))
            ->take(2)
            ->join('');

        // Get tier info
        $tier = $appUser?->getTier() ?? UserTier::FREE;
        $this->userTier = $tier->label();
        $this->tierColor = match ($tier) {
            UserTier::HADES => 'from-red-500 to-orange-500',
            UserTier::APOLLO => 'from-violet-500 to-purple-500',
            default => 'from-gray-500 to-gray-600',
        };

        $this->memberSince = $user->created_at?->format('F Y');

        // Use cached stats if available, otherwise defaults
        // Stats are computed by background job, not on page load
        $cached = $appUser?->cached_stats;

        $this->quotas = $cached['quotas'] ?? $this->getDefaultQuotas($tier);
        $this->serviceStats = $cached['services'] ?? $this->getDefaultServiceStats();
        $this->recentActivity = $cached['activity'] ?? [];
    }

    protected function getDefaultQuotas(UserTier $tier): array
    {
        return match ($tier) {
            UserTier::HADES => [
                'workspaces' => ['used' => 0, 'limit' => null, 'label' => 'Workspaces'],
                'social_accounts' => ['used' => 0, 'limit' => null, 'label' => 'Social Accounts'],
                'scheduled_posts' => ['used' => 0, 'limit' => null, 'label' => 'Scheduled Posts'],
                'storage' => ['used' => 0, 'limit' => null, 'label' => 'Storage (GB)'],
            ],
            UserTier::APOLLO => [
                'workspaces' => ['used' => 0, 'limit' => 5, 'label' => 'Workspaces'],
                'social_accounts' => ['used' => 0, 'limit' => 25, 'label' => 'Social Accounts'],
                'scheduled_posts' => ['used' => 0, 'limit' => 500, 'label' => 'Scheduled Posts'],
                'storage' => ['used' => 0, 'limit' => 10, 'label' => 'Storage (GB)'],
            ],
            default => [
                'workspaces' => ['used' => 0, 'limit' => 1, 'label' => 'Workspaces'],
                'social_accounts' => ['used' => 0, 'limit' => 5, 'label' => 'Social Accounts'],
                'scheduled_posts' => ['used' => 0, 'limit' => 50, 'label' => 'Scheduled Posts'],
                'storage' => ['used' => 0, 'limit' => 1, 'label' => 'Storage (GB)'],
            ],
        };
    }

    protected function getDefaultServiceStats(): array
    {
        return [
            [
                'name' => 'Social',
                'icon' => 'fa-share-nodes',
                'color' => 'bg-blue-500',
                'status' => 'inactive',
                'stat' => 'Not configured',
            ],
            [
                'name' => 'Bio',
                'icon' => 'fa-id-card',
                'color' => 'bg-violet-500',
                'status' => 'inactive',
                'stat' => 'Not configured',
            ],
            [
                'name' => 'Analytics',
                'icon' => 'fa-chart-line',
                'color' => 'bg-green-500',
                'status' => 'inactive',
                'stat' => 'Not configured',
            ],
            [
                'name' => 'Trust',
                'icon' => 'fa-shield-check',
                'color' => 'bg-amber-500',
                'status' => 'inactive',
                'stat' => 'Not configured',
            ],
        ];
    }

    public function render()
    {
        return view('hub::admin.profile')
            ->layout('hub::admin.layouts.app', ['title' => 'Profile']);
    }
}
