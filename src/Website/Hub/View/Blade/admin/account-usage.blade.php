<div>
    <admin:page-header title="Usage & Billing" description="Monitor your usage, manage boosts, and configure AI services." />

    {{-- Card with sidebar --}}
    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
        <div class="flex flex-col md:flex-row md:-mr-px">

            {{-- Sidebar navigation --}}
            <div class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700/60 min-w-60 md:space-y-3">
                {{-- Usage group --}}
                <div>
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 hidden md:block">Usage</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'overview')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'overview',
                                ])
                            >
                                <core:icon name="chart-pie" @class(['shrink-0 mr-2', 'text-violet-400' => $activeSection === 'overview', 'text-gray-400 dark:text-gray-500' => $activeSection !== 'overview']) />
                                <span class="text-sm font-medium {{ $activeSection === 'overview' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">Overview</span>
                            </button>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'workspaces')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'workspaces',
                                ])
                            >
                                <core:icon name="buildings" @class(['shrink-0 mr-2', 'text-violet-400' => $activeSection === 'workspaces', 'text-gray-400 dark:text-gray-500' => $activeSection !== 'workspaces']) />
                                <span class="text-sm font-medium {{ $activeSection === 'workspaces' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">Workspaces</span>
                            </button>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'entitlements')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'entitlements',
                                ])
                            >
                                <core:icon name="key" @class(['shrink-0 mr-2', 'text-violet-400' => $activeSection === 'entitlements', 'text-gray-400 dark:text-gray-500' => $activeSection !== 'entitlements']) />
                                <span class="text-sm font-medium {{ $activeSection === 'entitlements' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">Entitlements</span>
                            </button>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'boosts')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'boosts',
                                ])
                            >
                                <core:icon name="bolt" @class(['shrink-0 mr-2', 'text-violet-400' => $activeSection === 'boosts', 'text-gray-400 dark:text-gray-500' => $activeSection !== 'boosts']) />
                                <span class="text-sm font-medium {{ $activeSection === 'boosts' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">Boosts</span>
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Integrations group --}}
                <div class="md:mt-6">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 hidden md:block">Integrations</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'ai')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'ai',
                                ])
                            >
                                <core:icon name="microchip" @class(['shrink-0 mr-2', 'text-violet-400' => $activeSection === 'ai', 'text-gray-400 dark:text-gray-500' => $activeSection !== 'ai']) />
                                <span class="text-sm font-medium {{ $activeSection === 'ai' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">AI Services</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Content panel --}}
            <div class="grow p-6">
                {{-- Overview Section --}}
                @if($activeSection === 'overview')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">Usage Overview</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Monitor your current usage and limits.</p>
                        </div>

                        {{-- Active Packages --}}
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Active Packages</h3>
                            @if(empty($activePackages))
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <core:icon name="box" class="size-6 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm">No active packages</p>
                                </div>
                            @else
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach($activePackages as $workspacePackage)
                                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                            @if($workspacePackage['package']['icon'] ?? null)
                                                <div class="shrink-0 w-8 h-8 rounded-lg bg-{{ $workspacePackage['package']['color'] ?? 'blue' }}-500/10 flex items-center justify-center">
                                                    <core:icon :name="$workspacePackage['package']['icon']" class="size-4 text-{{ $workspacePackage['package']['color'] ?? 'blue' }}-500" />
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $workspacePackage['package']['name'] ?? 'Unknown' }}</p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if($workspacePackage['package']['is_base_package'] ?? false)
                                                        <flux:badge size="sm" color="purple">Base</flux:badge>
                                                    @else
                                                        <flux:badge size="sm" color="blue">Addon</flux:badge>
                                                    @endif
                                                    <flux:badge size="sm" color="green">Active</flux:badge>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Usage by Category - Accordion --}}
                        @if(!empty($usageSummary))
                            <flux:accordion transition class="space-y-2">
                                @foreach($usageSummary as $category => $features)
                                    @php
                                        $categoryIcon = match($category) {
                                            'social' => 'share-nodes',
                                            'bio', 'biolink' => 'link',
                                            'analytics' => 'chart-line',
                                            'notify' => 'bell',
                                            'trust' => 'shield-check',
                                            'support' => 'headset',
                                            'ai' => 'microchip',
                                            'mcp', 'api' => 'plug',
                                            'host', 'service' => 'server',
                                            default => 'cubes',
                                        };
                                        $categoryColor = match($category) {
                                            'social' => 'pink',
                                            'bio', 'biolink' => 'emerald',
                                            'analytics' => 'blue',
                                            'notify' => 'amber',
                                            'trust' => 'green',
                                            'support' => 'violet',
                                            'ai' => 'purple',
                                            'mcp', 'api' => 'indigo',
                                            'host', 'service' => 'sky',
                                            default => 'gray',
                                        };
                                        $allowedCount = collect($features)->where('allowed', true)->count();
                                        $totalCount = count($features);
                                    @endphp
                                    <flux:accordion.item class="bg-gray-50 dark:bg-gray-700/30 rounded-lg !border-0">
                                        <flux:accordion.heading>
                                            <div class="flex items-center justify-between w-full pr-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-7 h-7 rounded-md bg-{{ $categoryColor }}-500/10 flex items-center justify-center">
                                                        <core:icon :name="$categoryIcon" class="text-{{ $categoryColor }}-500 text-sm" />
                                                    </span>
                                                    <span class="capitalize">{{ $category ?? 'General' }}</span>
                                                </div>
                                                <flux:badge size="sm" :color="$allowedCount > 0 ? 'green' : 'zinc'">
                                                    {{ $allowedCount }}/{{ $totalCount }}
                                                </flux:badge>
                                            </div>
                                        </flux:accordion.heading>
                                        <flux:accordion.content>
                                            <div class="space-y-1 pt-2">
                                                @foreach($features as $feature)
                                                    <div class="flex items-center justify-between py-1.5 px-1 rounded hover:bg-gray-100 dark:hover:bg-gray-600/30">
                                                        <span class="text-sm {{ $feature['allowed'] ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">{{ $feature['name'] }}</span>
                                                        @if(!$feature['allowed'])
                                                            <flux:badge size="sm" color="zinc">Not included</flux:badge>
                                                        @elseif($feature['unlimited'])
                                                            <flux:badge size="sm" color="purple">Unlimited</flux:badge>
                                                        @elseif($feature['type'] === 'limit' && isset($feature['limit']))
                                                            @php
                                                                $percentage = min($feature['percentage'] ?? 0, 100);
                                                                $badgeColor = match(true) {
                                                                    $percentage >= 90 => 'red',
                                                                    $percentage >= 75 => 'amber',
                                                                    default => 'green',
                                                                };
                                                            @endphp
                                                            <flux:badge size="sm" :color="$badgeColor">{{ $feature['used'] }}/{{ $feature['limit'] }}</flux:badge>
                                                        @elseif($feature['type'] === 'boolean')
                                                            <flux:badge size="sm" color="green" icon="check">Active</flux:badge>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </flux:accordion.content>
                                    </flux:accordion.item>
                                @endforeach
                            </flux:accordion>
                        @else
                            <div class="text-center py-6 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <core:icon name="chart-bar" class="size-6 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No usage data available</p>
                            </div>
                        @endif

                        {{-- Active Boosts --}}
                        @if(!empty($activeBoosts))
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Active Boosts</h3>
                                <div class="space-y-2">
                                    @foreach($activeBoosts as $boost)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                            <div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $boost['feature_code'] }}</span>
                                                <div class="flex items-center gap-2 mt-1">
                                                    @switch($boost['boost_type'])
                                                        @case('add_limit')
                                                            <flux:badge size="sm" color="blue">+{{ number_format($boost['limit_value']) }}</flux:badge>
                                                            @break
                                                        @case('unlimited')
                                                            <flux:badge size="sm" color="purple">Unlimited</flux:badge>
                                                            @break
                                                        @case('enable')
                                                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                                                            @break
                                                    @endswitch
                                                </div>
                                            </div>
                                            @if($boost['boost_type'] === 'add_limit' && $boost['limit_value'])
                                                <div class="text-right">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($boost['remaining_limit'] ?? $boost['limit_value']) }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">remaining</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Workspaces Section --}}
                @if($activeSection === 'workspaces')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">Workspaces</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">View all your workspaces and their subscription details.</p>
                        </div>

                        @php $workspaces = $this->userWorkspaces; @endphp

                        @if(count($workspaces) > 0)
                            {{-- Cost Summary --}}
                            @php
                                $totalMonthly = collect($workspaces)->sum('price');
                                $activeCount = collect($workspaces)->where('status', 'active')->count();
                            @endphp
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                                            <core:icon name="sterling-sign" class="text-green-500" />
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">£{{ number_format($totalMonthly, 2) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Monthly total</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                            <core:icon name="buildings" class="text-blue-500" />
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ count($workspaces) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Total workspaces</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                                            <core:icon name="circle-check" class="text-violet-500" />
                                        </div>
                                        <div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeCount }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Active subscriptions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Workspace List --}}
                            <div class="space-y-4">
                                @foreach($workspaces as $ws)
                                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ strtoupper(substr($ws['workspace']->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $ws['workspace']->name }}</h3>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <flux:badge size="sm" :color="$ws['status'] === 'active' ? 'green' : 'zinc'">
                                                            {{ ucfirst($ws['status']) }}
                                                        </flux:badge>
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $ws['plan'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4 sm:text-right">
                                                <div>
                                                    @if($ws['price'] > 0)
                                                        <p class="font-semibold text-gray-900 dark:text-gray-100">£{{ number_format($ws['price'], 2) }}/mo</p>
                                                    @else
                                                        <p class="font-semibold text-gray-500 dark:text-gray-400">Free</p>
                                                    @endif
                                                    @if($ws['renewsAt'])
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            Renews {{ $ws['renewsAt']->format('j M Y') }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ \Website\Hub\Boot::hubRoute('hub.sites.settings', $ws['workspace']->slug) }}" class="text-violet-500 hover:text-violet-600">
                                                        <core:icon name="gear" />
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @if($ws['serviceCount'] > 0)
                                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Active Services</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($ws['services'] as $service)
                                                        <a href="{{ $service['href'] ?? '#' }}" class="inline-flex items-center px-2 py-1 bg-white dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-300 hover:text-violet-500 transition-colors">
                                                            <core:icon :name="$service['icon']" class="mr-1 text-{{ $service['color'] ?? 'gray' }}-500" size="fa-sm" />
                                                            {{ $service['label'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <core:icon name="buildings" class="size-8 mx-auto mb-2 opacity-50" />
                                <p>No workspaces found</p>
                                <p class="text-sm mt-1">Create a workspace to get started.</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Entitlements Section --}}
                @if($activeSection === 'entitlements')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">Entitlements</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">View all available features and your current access levels.</p>
                        </div>

                        @forelse($this->allFeatures as $category => $features)
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3 capitalize flex items-center">
                                    @php
                                        $categoryIcon = match($category) {
                                            'social' => 'share-nodes',
                                            'bio' => 'link',
                                            'analytics' => 'chart-line',
                                            'notify' => 'bell',
                                            'trust' => 'shield-check',
                                            'support' => 'headset',
                                            'ai' => 'microchip',
                                            'mcp' => 'plug',
                                            default => 'cubes',
                                        };
                                        $categoryColor = match($category) {
                                            'social' => 'pink',
                                            'bio' => 'emerald',
                                            'analytics' => 'blue',
                                            'notify' => 'amber',
                                            'trust' => 'green',
                                            'support' => 'violet',
                                            'ai' => 'purple',
                                            'mcp' => 'indigo',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <span class="w-6 h-6 rounded bg-{{ $categoryColor }}-500/10 flex items-center justify-center mr-2">
                                        <core:icon :name="$categoryIcon" class="text-{{ $categoryColor }}-500 text-xs" />
                                    </span>
                                    {{ $category ?? 'General' }}
                                </h3>
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg overflow-hidden">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-2">Feature</th>
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-2">Code</th>
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-2">Type</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-2">Your Access</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            @foreach($features as $feature)
                                                @php
                                                    $workspace = auth()->user()?->defaultHostWorkspace();
                                                    $check = $workspace ? app(\Core\Tenant\Services\EntitlementService::class)->can($workspace, $feature['code']) : null;
                                                    $allowed = $check?->isAllowed() ?? false;
                                                    $limit = $check?->effectiveLimit ?? null;
                                                    $unlimited = $check?->isUnlimited ?? false;
                                                @endphp
                                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/50">
                                                    <td class="px-4 py-2">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $feature['name'] }}</span>
                                                        @if($feature['description'] ?? null)
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($feature['description'], 50) }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <code class="text-xs bg-gray-200 dark:bg-gray-600 px-1.5 py-0.5 rounded">{{ $feature['code'] }}</code>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <flux:badge size="sm" :color="$feature['type'] === 'limit' ? 'blue' : 'purple'">
                                                            {{ ucfirst($feature['type']) }}
                                                        </flux:badge>
                                                    </td>
                                                    <td class="px-4 py-2 text-right">
                                                        @if(!$allowed)
                                                            <flux:badge size="sm" color="zinc">Not included</flux:badge>
                                                        @elseif($unlimited)
                                                            <flux:badge size="sm" color="purple">Unlimited</flux:badge>
                                                        @elseif($feature['type'] === 'boolean')
                                                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                                                        @elseif($limit !== null)
                                                            <flux:badge size="sm" color="blue">{{ number_format($limit) }}</flux:badge>
                                                        @else
                                                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <core:icon name="key" class="size-8 mx-auto mb-2 opacity-50" />
                                <p>No features defined</p>
                            </div>
                        @endforelse

                        {{-- Upgrade prompt --}}
                        @if(!auth()->user()?->isHades())
                            <div class="bg-gradient-to-r from-violet-500/10 to-purple-500/10 border border-violet-500/20 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-gray-100">Need more access?</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Upgrade your plan to unlock additional features and higher limits.</p>
                                    </div>
                                    <a href="{{ route('pricing') }}" class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors text-sm font-medium">
                                        View Plans
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Boosts Section --}}
                @if($activeSection === 'boosts')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">Purchase Boosts</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Add extra capacity to your account.</p>
                        </div>

                        @if(count($boostOptions) > 0)
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach($boostOptions as $boost)
                                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $boost['feature_name'] }}</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $boost['description'] }}</p>
                                            </div>
                                            @switch($boost['boost_type'])
                                                @case('add_limit')
                                                    <flux:badge color="blue">+{{ number_format($boost['limit_value']) }}</flux:badge>
                                                    @break
                                                @case('unlimited')
                                                    <flux:badge color="purple">Unlimited</flux:badge>
                                                    @break
                                                @case('enable')
                                                    <flux:badge color="green">Enable</flux:badge>
                                                    @break
                                            @endswitch
                                        </div>
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                                @switch($boost['duration_type'])
                                                    @case('cycle_bound')
                                                        <core:icon name="clock" class="size-3 mr-1" /> Billing cycle
                                                        @break
                                                    @case('duration')
                                                        <core:icon name="calendar" class="size-3 mr-1" /> Limited time
                                                        @break
                                                    @case('permanent')
                                                        <core:icon name="infinity" class="size-3 mr-1" /> Permanent
                                                        @break
                                                @endswitch
                                            </div>
                                            <flux:button wire:click="purchaseBoost('{{ $boost['blesta_id'] }}')" size="sm" variant="primary">
                                                Purchase
                                            </flux:button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <core:icon name="rocket" class="size-8 mx-auto mb-2 opacity-50" />
                                <p>No boosts available</p>
                                <p class="text-sm mt-1">Check back later for available boosts.</p>
                            </div>
                        @endif

                        {{-- Info box --}}
                        <div class="bg-blue-500/10 dark:bg-blue-500/20 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2 flex items-center">
                                <core:icon name="circle-info" class="size-4 mr-2" /> About Boosts
                            </h4>
                            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 ml-6">
                                <li><strong>Billing cycle:</strong> Resets with your billing period</li>
                                <li><strong>Limited time:</strong> Expires after a set duration</li>
                                <li><strong>Permanent:</strong> Never expires</li>
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- AI Services Section --}}
                @if($activeSection === 'ai')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">AI Services</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Configure your AI provider API keys.</p>
                        </div>

                        {{-- AI Provider Tabs --}}
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex space-x-4">
                                <button
                                    wire:click="$set('activeAiTab', 'claude')"
                                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeAiTab === 'claude' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-[#D97757]" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M13.827 3.52c-.592-1.476-2.672-1.476-3.264 0L5.347 16.756c-.464 1.16.464 2.404 1.632 2.404h3.264l1.632-4.068h.25l1.632 4.068h3.264c1.168 0 2.096-1.244 1.632-2.404L13.827 3.52zM12 11.636l-1.224 3.048h2.448L12 11.636z"/>
                                        </svg>
                                        Claude
                                    </span>
                                </button>
                                <button
                                    wire:click="$set('activeAiTab', 'gemini')"
                                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeAiTab === 'gemini' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24">
                                            <defs>
                                                <linearGradient id="gemini-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#4285F4"/>
                                                    <stop offset="50%" style="stop-color:#9B72CB"/>
                                                    <stop offset="100%" style="stop-color:#D96570"/>
                                                </linearGradient>
                                            </defs>
                                            <path fill="url(#gemini-grad)" d="M12 2C12 2 12.5 7 15.5 10C18.5 13 24 12 24 12C24 12 18.5 13 15.5 16C12.5 19 12 24 12 24C12 24 11.5 19 8.5 16C5.5 13 0 12 0 12C0 12 5.5 11 8.5 8C11.5 5 12 2 12 2Z"/>
                                        </svg>
                                        Gemini
                                    </span>
                                </button>
                                <button
                                    wire:click="$set('activeAiTab', 'openai')"
                                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeAiTab === 'openai' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                >
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-[#10A37F]" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494z"/>
                                        </svg>
                                        OpenAI
                                    </span>
                                </button>
                            </nav>
                        </div>

                        {{-- Claude Panel --}}
                        @if($activeAiTab === 'claude')
                            <form wire:submit="saveClaude" class="space-y-4">
                                <flux:field>
                                    <flux:label>API Key</flux:label>
                                    <flux:input wire:model="claudeApiKey" type="password" placeholder="sk-ant-..." />
                                    <flux:description>
                                        <a href="https://console.anthropic.com/settings/keys" target="_blank" class="text-violet-500 hover:text-violet-600">Get your API key from Anthropic</a>
                                    </flux:description>
                                    <flux:error name="claudeApiKey" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Model</flux:label>
                                    <flux:select wire:model="claudeModel">
                                        @foreach($this->claudeModelsComputed as $value => $label)
                                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>

                                <flux:checkbox wire:model="claudeActive" label="Enable Claude" />

                                <div class="flex justify-end pt-2">
                                    <flux:button type="submit" variant="primary">Save Claude Settings</flux:button>
                                </div>
                            </form>
                        @endif

                        {{-- Gemini Panel --}}
                        @if($activeAiTab === 'gemini')
                            <form wire:submit="saveGemini" class="space-y-4">
                                <flux:field>
                                    <flux:label>API Key</flux:label>
                                    <flux:input wire:model="geminiApiKey" type="password" placeholder="AIza..." />
                                    <flux:description>
                                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-violet-500 hover:text-violet-600">Get your API key from Google AI Studio</a>
                                    </flux:description>
                                    <flux:error name="geminiApiKey" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Model</flux:label>
                                    <flux:select wire:model="geminiModel">
                                        @foreach($this->geminiModelsComputed as $value => $label)
                                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>

                                <flux:checkbox wire:model="geminiActive" label="Enable Gemini" />

                                <div class="flex justify-end pt-2">
                                    <flux:button type="submit" variant="primary">Save Gemini Settings</flux:button>
                                </div>
                            </form>
                        @endif

                        {{-- OpenAI Panel --}}
                        @if($activeAiTab === 'openai')
                            <form wire:submit="saveOpenAI" class="space-y-4">
                                <flux:field>
                                    <flux:label>Secret Key</flux:label>
                                    <flux:input wire:model="openaiSecretKey" type="password" placeholder="sk-..." />
                                    <flux:description>
                                        <a href="https://platform.openai.com/api-keys" target="_blank" class="text-violet-500 hover:text-violet-600">Get your API key from OpenAI</a>
                                    </flux:description>
                                    <flux:error name="openaiSecretKey" />
                                </flux:field>

                                <flux:checkbox wire:model="openaiActive" label="Enable OpenAI" />

                                <div class="flex justify-end pt-2">
                                    <flux:button type="submit" variant="primary">Save OpenAI Settings</flux:button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
