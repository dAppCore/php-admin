<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Livewire\Component;

class Analytics extends Component
{
    public array $metrics = [];

    public array $chartData = [];

    public function mount(): void
    {
        // Placeholder metrics
        $this->metrics = [
            [
                'label' => 'Total Visitors',
                'value' => '—',
                'change' => null,
                'icon' => 'users',
            ],
            [
                'label' => 'Page Views',
                'value' => '—',
                'change' => null,
                'icon' => 'eye',
            ],
            [
                'label' => 'Bounce Rate',
                'value' => '—',
                'change' => null,
                'icon' => 'arrow-right-from-bracket',
            ],
            [
                'label' => 'Avg. Session',
                'value' => '—',
                'change' => null,
                'icon' => 'clock',
            ],
        ];

        // Placeholder chart sections
        $this->chartData = [
            'visitors' => [
                'title' => 'Visitors Over Time',
                'description' => 'Daily unique visitors across all sites',
            ],
            'pages' => [
                'title' => 'Top Pages',
                'description' => 'Most visited pages this period',
            ],
            'sources' => [
                'title' => 'Traffic Sources',
                'description' => 'Where your visitors come from',
            ],
            'devices' => [
                'title' => 'Devices',
                'description' => 'Device breakdown of your audience',
            ],
        ];
    }

    public function render()
    {
        return view('hub::admin.analytics')
            ->layout('hub::admin.layouts.app', ['title' => 'Analytics']);
    }
}
