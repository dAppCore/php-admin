<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Services\EntitlementService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UsageDashboard extends Component
{
    public Collection $usageSummary;

    public Collection $activePackages;

    public Collection $activeBoosts;

    public function mount(EntitlementService $entitlementService): void
    {
        // Get the authenticated user's default workspace (the Workspace model, not WorkspaceService config)
        $workspace = Auth::user()?->defaultHostWorkspace();

        if (! $workspace) {
            $this->usageSummary = collect();
            $this->activePackages = collect();
            $this->activeBoosts = collect();

            return;
        }

        $this->usageSummary = $entitlementService->getUsageSummary($workspace);
        $this->activePackages = $entitlementService->getActivePackages($workspace);
        $this->activeBoosts = $entitlementService->getActiveBoosts($workspace);
    }

    public function render()
    {
        return view('hub::admin.usage-dashboard')
            ->layout('hub::admin.layouts.app', ['title' => 'Usage']);
    }
}
