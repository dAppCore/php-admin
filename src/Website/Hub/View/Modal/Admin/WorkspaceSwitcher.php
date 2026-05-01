<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Services\WorkspaceService;
use Livewire\Attributes\On;
use Livewire\Component;
use Website\Hub\Boot;

class WorkspaceSwitcher extends Component
{
    public array $workspaces = [];

    public array $current = [];

    public bool $open = false;

    /**
     * The URL to redirect to after switching workspaces.
     * Captured on mount since request()->url() returns /livewire/update during updates.
     */
    public string $returnUrl = '';

    protected WorkspaceService $workspaceService;

    public function boot(WorkspaceService $workspaceService): void
    {
        $this->workspaceService = $workspaceService;
    }

    public function mount(): void
    {
        $this->workspaces = $this->workspaceService->all();
        $this->current = $this->workspaceService->current();

        // Capture the current URL on mount (initial page load)
        // This is the page URL, not the Livewire endpoint
        $this->returnUrl = url()->current();
    }

    /**
     * Refresh workspace data when a workspace is activated elsewhere.
     */
    #[On('workspace-activated')]
    public function refreshWorkspaces(): void
    {
        $this->workspaces = $this->workspaceService->all();
        $this->current = $this->workspaceService->current();
    }

    public function switchWorkspace(string $slug): void
    {
        $result = $this->workspaceService->setCurrent($slug);

        if (! $result) {
            // User doesn't have access to this workspace
            return;
        }

        $this->current = $this->workspaceService->current();
        $this->open = false;

        // Dispatch event to refresh any workspace-aware components
        $this->dispatch('workspace-changed', workspace: $slug);

        // Redirect to the page we were on (captured during mount)
        $this->redirect($this->returnUrl ?: Boot::hubRoute('hub.dashboard'));
    }

    public function render()
    {
        return view('hub::admin.workspace-switcher');
    }
}
