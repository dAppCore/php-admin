<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Livewire\Component;

class Console extends Component
{
    public array $servers = [];

    public ?int $selectedServer = null;

    public function mount(): void
    {
        $this->servers = [
            [
                'id' => 1,
                'name' => 'Bio (Production)',
                'type' => 'WordPress',
                'status' => 'online',
            ],
            [
                'id' => 2,
                'name' => 'Social (Production)',
                'type' => 'Laravel',
                'status' => 'online',
            ],
            [
                'id' => 3,
                'name' => 'Analytics (Production)',
                'type' => 'Node.js',
                'status' => 'online',
            ],
            [
                'id' => 4,
                'name' => 'Host Hub (Development)',
                'type' => 'Laravel',
                'status' => 'online',
            ],
        ];
    }

    public function selectServer(int $serverId): void
    {
        $this->selectedServer = $serverId;
    }

    public function render()
    {
        return view('hub::admin.console')
            ->layout('hub::admin.layouts.app', ['title' => 'Console']);
    }
}
