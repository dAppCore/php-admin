<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Enums\UserTier;
use Core\Tenant\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class Platform extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tierFilter = '';

    public string $verifiedFilter = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    // Action messages
    public string $actionMessage = '';

    public string $actionType = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'tierFilter' => ['except' => ''],
        'verifiedFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Ensure only Hades users can access
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for platform administration.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function verifyEmail(int $userId): void
    {
        $user = User::find($userId);
        if ($user && ! $user->email_verified_at) {
            $user->markEmailAsVerified();
            $this->actionMessage = "Email verified for {$user->email}.";
            $this->actionType = 'success';
        }
    }

    public function clearCache(): void
    {
        Cache::flush();
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        $this->actionMessage = 'All caches cleared successfully.';
        $this->actionType = 'success';
    }

    public function clearOpcache(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->actionMessage = 'OPcache cleared successfully.';
            $this->actionType = 'success';
        } else {
            $this->actionMessage = 'OPcache is not available.';
            $this->actionType = 'warning';
        }
    }

    public function restartQueue(): void
    {
        Artisan::call('queue:restart');
        $this->actionMessage = 'Queue workers will restart after their current job completes.';
        $this->actionType = 'success';
    }

    public function getPlatformStats(): array
    {
        return [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'hades_users' => User::where('tier', 'hades')->count(),
            'apollo_users' => User::where('tier', 'apollo')->count(),
            'free_users' => User::where('tier', 'free')->orWhereNull('tier')->count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
        ];
    }

    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'db_connection' => config('database.default'),
        ];
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->tierFilter, function ($query) {
                if ($this->tierFilter === 'free') {
                    $query->where(function ($q) {
                        $q->where('tier', 'free')->orWhereNull('tier');
                    });
                } else {
                    $query->where('tier', $this->tierFilter);
                }
            })
            ->when($this->verifiedFilter !== '', function ($query) {
                if ($this->verifiedFilter === '1') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('hub::admin.platform', [
            'users' => $users,
            'stats' => $this->getPlatformStats(),
            'systemInfo' => $this->getSystemInfo(),
            'tiers' => UserTier::cases(),
        ])->layout('hub::admin.layouts.app', ['title' => 'Platform Admin']);
    }
}
