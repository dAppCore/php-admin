@php
    // Icon name to Font Awesome class mapping
    $iconMap = [
        'link' => 'fa-solid fa-link',
        'share-nodes' => 'fa-solid fa-share-nodes',
        'chart-line' => 'fa-solid fa-chart-line',
        'chart-simple' => 'fa-solid fa-chart-simple',
        'bell' => 'fa-solid fa-bell',
        'shield-check' => 'fa-solid fa-shield-check',
        'badge-check' => 'fa-solid fa-badge-check',
        'file' => 'fa-solid fa-file',
        'check-circle' => 'fa-solid fa-check-circle',
        'cursor-arrow-rays' => 'fa-solid fa-arrow-pointer',
        'folder' => 'fa-solid fa-folder',
        'globe' => 'fa-solid fa-globe',
        'eye' => 'fa-solid fa-eye',
        'users' => 'fa-solid fa-users',
        'bullhorn' => 'fa-solid fa-bullhorn',
        'paper-plane' => 'fa-solid fa-paper-plane',
        'megaphone' => 'fa-solid fa-bullhorn',
        'palette' => 'fa-solid fa-palette',
        'hand-raised' => 'fa-solid fa-hand',
        'x-mark' => 'fa-solid fa-xmark',
        'circle-stack' => 'fa-solid fa-layer-group',
        'plus' => 'fa-solid fa-plus',
        'calendar' => 'fa-solid fa-calendar',
        'headset' => 'fa-solid fa-headset',
        'shopping-cart' => 'fa-solid fa-shopping-cart',
        'inbox' => 'fa-solid fa-inbox',
        'gear' => 'fa-solid fa-gear',
        'receipt' => 'fa-solid fa-receipt',
        'rotate' => 'fa-solid fa-rotate',
        'ticket' => 'fa-solid fa-ticket',
        'gauge' => 'fa-solid fa-gauge',
        'pen-to-square' => 'fa-solid fa-pen-to-square',
        'bullseye' => 'fa-solid fa-bullseye',
        'chart-bar' => 'fa-solid fa-chart-bar',
        'globe-alt' => 'fa-solid fa-globe',
        'flag' => 'fa-solid fa-flag',
        'copy' => 'fa-solid fa-copy',
        'swatchbook' => 'fa-solid fa-swatchbook',
        'image' => 'fa-solid fa-image',
    ];
    $faIcon = fn($name) => $iconMap[$name] ?? 'fa-solid fa-circle';
@endphp

<div class="space-y-6">
    {{-- Service Tabs (from each module's Boot.php via AdminMenuRegistry) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Services">
                @foreach ($this->services as $key => $svc)
                    <a
                        href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => $key]) }}"
                        wire:navigate
                        @class([
                            'group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap transition-colors',
                            'border-violet-500 text-violet-600 dark:text-violet-400' => $service === $key,
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' => $service !== $key,
                        ])
                    >
                        <i class="{{ $faIcon($svc['icon']) }}"></i>
                        {{ $svc['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Sub-tabs for current service (from module's Boot.php) --}}
        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-1" aria-label="{{ $this->currentServiceItem['label'] ?? 'Service' }} sections">
                @foreach ($this->serviceTabs as $tabItem)
                    <a
                        href="{{ $tabItem['href'] }}"
                        wire:navigate
                        @class([
                            'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                            'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' => $tabItem['active'] ?? false,
                            'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-700/50' => !($tabItem['active'] ?? false),
                        ])
                    >
                        @if (!empty($tabItem['icon']))
                            <i class="{{ $faIcon($tabItem['icon']) }} text-xs opacity-70"></i>
                        @endif
                        {{ $tabItem['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Content Panel --}}
    <div class="space-y-6">
        {{-- BIO SERVICE --}}
        @if ($service === 'bio')
            @if ($tab === 'dashboard')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->bioStatCards as $card)
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Coloured left border accent --}}
                            <div @class([
                                'absolute left-0 top-0 bottom-0 w-1',
                                'bg-violet-500' => $card['color'] === 'violet',
                                'bg-green-500' => $card['color'] === 'green',
                                'bg-blue-500' => $card['color'] === 'blue',
                                'bg-orange-500' => $card['color'] === 'orange',
                            ])></div>

                            <div class="p-5 pl-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Label first (smaller, secondary) --}}
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>

                                        {{-- Value (larger, bolder, primary) --}}
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $card['value'] }}</p>
                                    </div>

                                    {{-- Icon with background circle --}}
                                    <div @class([
                                        'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                        'bg-violet-100 dark:bg-violet-900/30' => $card['color'] === 'violet',
                                        'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                        'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                        'bg-orange-100 dark:bg-orange-900/30' => $card['color'] === 'orange',
                                    ])>
                                        <i @class([
                                            $faIcon($card['icon']),
                                            'text-xl',
                                            'text-violet-600 dark:text-violet-400' => $card['color'] === 'violet',
                                            'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                            'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            'text-orange-600 dark:text-orange-400' => $card['color'] === 'orange',
                                        ])></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Top Pages Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.your_bio_pages') }}</h3>
                        <div class="flex items-center gap-2">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'bio', 'tab' => 'pages']) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                                {{ __('hub::hub.services.actions.manage_biohost') }}
                            </a>
                        </div>
                    </div>
                    <div class="p-4">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('hub::hub.services.columns.namespace') }}</flux:table.column>
                                <flux:table.column>{{ __('hub::hub.services.columns.type') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                                <flux:table.column align="end">{{ __('hub::hub.services.columns.clicks') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @forelse ($this->bioPages->take(10) as $page)
                                    <flux:table.row>
                                        <flux:table.cell variant="strong">
                                            <a href="{{ $this->serviceMarketingUrl }}/{{ $page->url }}/settings" target="_blank" class="text-violet-600 dark:text-violet-400 hover:underline">
                                                {{ $page->url }}
                                            </a>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge color="violet" size="sm">{{ ucfirst($page->type) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell align="center">
                                            <flux:badge :color="$page->is_enabled ? 'green' : 'zinc'" size="sm">
                                                {{ $page->is_enabled ? __('hub::hub.services.status.active') : __('hub::hub.services.status.disabled') }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell align="end" variant="strong">{{ number_format($page->clicks) }}</flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="4" class="text-center py-8">
                                            {{ __('hub::hub.services.empty.bio_pages') }}
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            @elseif ($tab === 'pages')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.all_pages') }}</h3>
                        <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'bio', 'tab' => 'pages']) }}" wire:navigate variant="primary" size="sm" icon="plus">
                            {{ __('hub::hub.services.actions.create_page') }}
                        </flux:button>
                    </div>
                    <div class="p-4">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('hub::hub.services.columns.namespace') }}</flux:table.column>
                                <flux:table.column>{{ __('hub::hub.services.columns.type') }}</flux:table.column>
                                <flux:table.column>{{ __('hub::hub.services.columns.project') }}</flux:table.column>
                                <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                                <flux:table.column align="end">{{ __('hub::hub.services.columns.clicks') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @forelse ($this->bioPages as $page)
                                    <flux:table.row>
                                        <flux:table.cell variant="strong">
                                            <a href="{{ $this->serviceMarketingUrl }}/{{ $page->url }}/settings" target="_blank" class="text-violet-600 dark:text-violet-400 hover:underline">
                                                {{ $page->url }}
                                            </a>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge color="violet" size="sm">{{ ucfirst($page->type) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $page->project?->name ?? '-' }}</flux:table.cell>
                                        <flux:table.cell align="center">
                                            <flux:badge :color="$page->is_enabled ? 'green' : 'zinc'" size="sm">
                                                {{ $page->is_enabled ? __('hub::hub.services.status.active') : __('hub::hub.services.status.disabled') }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell align="end" variant="strong">{{ number_format($page->clicks) }}</flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="5" class="text-center py-8">
                                            {{ __('hub::hub.services.empty.pages') }}
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            @elseif ($tab === 'projects')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.projects') }}</h3>
                        <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'bio', 'tab' => 'projects']) }}" wire:navigate variant="primary" size="sm" icon="folder-plus">
                            {{ __('hub::hub.services.actions.manage_projects') }}
                        </flux:button>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.project') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.pages') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.created') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->bioProjects as $project)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">{{ $project->name }}</flux:table.cell>
                                    <flux:table.cell align="center">{{ $project->biolinks_count }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ $project->created_at->format('d M Y') }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="text-center py-8">
                                        {{ __('hub::hub.services.empty.projects') }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endif

        {{-- SOCIAL SERVICE --}}
        @if ($service === 'social')
            @if ($tab === 'dashboard')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->socialStatCards as $card)
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Coloured left border accent --}}
                            <div @class([
                                'absolute left-0 top-0 bottom-0 w-1',
                                'bg-violet-500' => $card['color'] === 'violet',
                                'bg-green-500' => $card['color'] === 'green',
                                'bg-blue-500' => $card['color'] === 'blue',
                                'bg-orange-500' => $card['color'] === 'orange',
                            ])></div>

                            <div class="p-5 pl-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Label first (smaller, secondary) --}}
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>

                                        {{-- Value (larger, bolder, primary) --}}
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $card['value'] }}</p>
                                    </div>

                                    {{-- Icon with background circle --}}
                                    <div @class([
                                        'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                        'bg-violet-100 dark:bg-violet-900/30' => $card['color'] === 'violet',
                                        'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                        'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                        'bg-orange-100 dark:bg-orange-900/30' => $card['color'] === 'orange',
                                    ])>
                                        <i @class([
                                            $faIcon($card['icon']),
                                            'text-xl',
                                            'text-violet-600 dark:text-violet-400' => $card['color'] === 'violet',
                                            'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                            'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            'text-orange-600 dark:text-orange-400' => $card['color'] === 'orange',
                                        ])></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Connected Accounts --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Connected Accounts</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'social', 'tab' => 'accounts']) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            Manage Accounts
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Account</flux:table.column>
                            <flux:table.column>Provider</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->socialAccounts->take(10) as $account)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            @if ($account->image_url)
                                                <img src="{{ $account->image_url }}" alt="{{ $account->name }}" class="w-8 h-8 rounded-full">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <i class="fa-solid fa-user text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $account->name }}</span>
                                                @if ($account->username)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">@{{ $account->username }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="blue" size="sm">{{ ucfirst($account->provider) }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge :color="$account->status === 'active' ? 'green' : 'red'" size="sm">
                                            {{ $account->status === 'active' ? __('hub::hub.services.status.active') : ucfirst($account->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="text-center py-8">
                                        No accounts connected yet. Connect your social media accounts to start scheduling posts.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'accounts')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Accounts</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'social', 'tab' => 'accounts']) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            <i class="fa-solid fa-plus text-xs"></i>
                            Connect Account
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Account</flux:table.column>
                            <flux:table.column>Provider</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                            <flux:table.column align="end">Last Synced</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->socialAccounts as $account)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            @if ($account->image_url)
                                                <img src="{{ $account->image_url }}" alt="{{ $account->name }}" class="w-8 h-8 rounded-full">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                    <i class="fa-solid fa-user text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $account->name }}</span>
                                                @if ($account->username)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">@{{ $account->username }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="blue" size="sm">{{ ucfirst($account->provider) }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge :color="$account->status === 'active' ? 'green' : 'red'" size="sm">
                                            {{ $account->status === 'active' ? __('hub::hub.services.status.active') : ucfirst($account->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">{{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-8">
                                        No accounts found
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'posts')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Posts</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'social', 'tab' => 'posts']) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            <i class="fa-solid fa-plus text-xs"></i>
                            Create Post
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Post</flux:table.column>
                            <flux:table.column>Accounts</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.created') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->socialPosts as $post)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">
                                        <span class="line-clamp-2">{{ Str::limit($post->content['body'] ?? $post->content['caption'] ?? 'No content', 100) }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex -space-x-2">
                                            @foreach ($post->accounts->take(3) as $account)
                                                @if ($account->image_url)
                                                    <img src="{{ $account->image_url }}" alt="{{ $account->name }}" class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-800" title="{{ $account->name }}">
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-800 flex items-center justify-center" title="{{ $account->name }}">
                                                        <i class="fa-solid fa-user text-gray-400 text-xs"></i>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if ($post->accounts->count() > 3)
                                                <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs text-gray-600 dark:text-gray-300">
                                                    +{{ $post->accounts->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge size="sm" :color="match($post->status) {
                                            \Core\Mod\Social\Enums\PostStatus::DRAFT => 'zinc',
                                            \Core\Mod\Social\Enums\PostStatus::SCHEDULED => 'blue',
                                            \Core\Mod\Social\Enums\PostStatus::PUBLISHING => 'cyan',
                                            \Core\Mod\Social\Enums\PostStatus::PUBLISHED => 'green',
                                            \Core\Mod\Social\Enums\PostStatus::FAILED => 'red',
                                            \Core\Mod\Social\Enums\PostStatus::NEEDS_APPROVAL => 'amber',
                                            default => 'zinc',
                                        }">
                                            {{ $post->status->label() }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">{{ $post->created_at->diffForHumans() }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-8">No posts found</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endif

        {{-- ANALYTICS SERVICE --}}
        @if ($service === 'analytics')
            @if ($tab === 'pages' && $this->isViewingPageDetails)
                {{-- Page Details View --}}
                <div class="space-y-6">
                    {{-- Header with back button --}}
                    <div class="flex items-center gap-4">
                        <button wire:click="closePageDetails" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white font-mono">{{ $this->pageDetailsPath }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->pageDetailsWebsite?->name }} &middot; {{ $this->pageDetailsWebsite?->host }}</p>
                        </div>
                        <flux:select wire:model.live="analyticsDateRange" size="sm">
                            <option value="7d">Last 7 days</option>
                            <option value="30d">Last 30 days</option>
                            <option value="90d">Last 90 days</option>
                        </flux:select>
                    </div>

                    {{-- Primary Stats --}}
                    @php $pageStats = $this->pageDetailsStats; @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Views</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($pageStats['views']) }}</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Visitors</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($pageStats['visitors']) }}</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Bounce Rate</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pageStats['bounce_rate'] }}%</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Views/Visitor</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pageStats['views_per_visitor'] }}</div>
                        </div>
                    </div>

                    {{-- Secondary Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Entries</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($pageStats['entries']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Sessions started here</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Exits</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($pageStats['exits']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Sessions ended here</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Exit Rate</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $pageStats['exit_rate'] }}%</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Of views that left</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Avg. Duration</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->formatDuration($pageStats['avg_duration']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Time on page</div>
                        </div>
                    </div>

                    {{-- Page Traffic Chart --}}
                    @if(! empty($this->pageDetailsChartData))
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Page Traffic</h3>
                            <flux:chart :value="$this->pageDetailsChartData" class="aspect-[3/1] max-h-48">
                                <flux:chart.svg>
                                    <flux:chart.axis axis="y">
                                        <flux:chart.axis.grid class="stroke-zinc-200 dark:stroke-zinc-700" />
                                        <flux:chart.axis.tick :format="['notation' => 'compact']" class="text-zinc-500" />
                                    </flux:chart.axis>

                                    <flux:chart.area field="views" class="text-cyan-500/20" />
                                    <flux:chart.line field="views" class="text-cyan-500" />

                                    <flux:chart.axis axis="x" field="date">
                                        <flux:chart.axis.tick class="text-zinc-500" />
                                    </flux:chart.axis>

                                    <flux:chart.cursor />
                                </flux:chart.svg>

                                <flux:chart.tooltip>
                                    <flux:chart.tooltip.heading field="date" />
                                    <flux:chart.tooltip.value field="views" label="Views" />
                                </flux:chart.tooltip>
                            </flux:chart>
                        </div>
                    @endif

                    {{-- Breakdowns Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Referrers --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Referrers</h3>
                            @if(count($this->pageDetailsReferrers) > 0)
                                <div class="space-y-3">
                                    @foreach($this->pageDetailsReferrers as $ref)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-900 dark:text-white truncate flex-1 mr-4">{{ $ref['referrer_host'] }}</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ number_format($ref['sessions']) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No referrer data</p>
                            @endif
                        </div>

                        {{-- Devices --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Devices</h3>
                            @if(count($this->pageDetailsDevices) > 0)
                                <div class="space-y-3">
                                    @foreach($this->pageDetailsDevices as $device => $count)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid {{ $device === 'desktop' ? 'fa-desktop' : ($device === 'mobile' ? 'fa-mobile' : 'fa-tablet') }} text-gray-400"></i>
                                                <span class="text-gray-900 dark:text-white capitalize">{{ $device ?? 'Unknown' }}</span>
                                            </div>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ number_format($count) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No data</p>
                            @endif
                        </div>

                        {{-- Browsers --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Browsers</h3>
                            @if(count($this->pageDetailsBrowsers) > 0)
                                <div class="space-y-3">
                                    @foreach($this->pageDetailsBrowsers as $browser => $count)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-900 dark:text-white">{{ $browser ?? 'Unknown' }}</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ number_format($count) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No data</p>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($tab === 'pages')
                {{-- Top Pages Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.top_pages') }}</h3>
                        <flux:select wire:model.live="analyticsDateRange" size="sm">
                            <option value="7d">{{ __('hub::hub.services.date_range.7d') }}</option>
                            <option value="30d">{{ __('hub::hub.services.date_range.30d') }}</option>
                            <option value="90d">{{ __('hub::hub.services.date_range.90d') }}</option>
                            <option value="all">{{ __('hub::hub.services.date_range.all') }}</option>
                        </flux:select>
                    </div>
                    <div class="p-4">
                        @if($this->analyticsTopPages->isNotEmpty())
                            @php $primaryWebsite = $this->analyticsWebsites->first(); @endphp
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Page</flux:table.column>
                                    <flux:table.column align="end">Views</flux:table.column>
                                    <flux:table.column align="end">Visitors</flux:table.column>
                                    <flux:table.column align="end">Bounce</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach($this->analyticsTopPages as $page)
                                        <flux:table.row>
                                            <flux:table.cell>
                                                @if($primaryWebsite)
                                                    <button wire:click="showPageDetails({{ $primaryWebsite->id }}, '{{ $page->path }}')" class="text-cyan-600 dark:text-cyan-400 hover:underline truncate block max-w-[250px] text-left" title="{{ $page->path }}">{{ $page->path }}</button>
                                                @else
                                                    <span class="truncate block max-w-[250px]" title="{{ $page->path }}">{{ $page->path }}</span>
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell align="end" variant="strong">{{ number_format($page->views) }}</flux:table.cell>
                                            <flux:table.cell align="end">{{ number_format($page->visitors) }}</flux:table.cell>
                                            <flux:table.cell align="end" class="text-gray-500 dark:text-gray-400">
                                                @if($page->bounce_rate !== null)
                                                    {{ $page->bounce_rate }}%
                                                @else
                                                    —
                                                @endif
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 px-4">
                                <div class="w-16 h-16 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4">
                                    <flux:icon name="document-text" class="size-8 text-cyan-500" />
                                </div>
                                <flux:heading size="lg" class="text-center">No page data yet</flux:heading>
                                <flux:subheading class="text-center mt-1 max-w-sm">
                                    {{ __('hub::hub.services.empty.page_data') }}
                                </flux:subheading>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'dashboard')
                @php
                    $summaryMetrics = $this->analyticsSummaryMetrics;
                @endphp

                {{-- Stats Card + Chart Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Combined Stats Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mr-4">Overview</h3>
                            <flux:select wire:model.live="analyticsDateRange" size="sm">
                                <option value="7d">{{ __('hub::hub.services.date_range.7d') }}</option>
                                <option value="30d">{{ __('hub::hub.services.date_range.30d') }}</option>
                                <option value="90d">{{ __('hub::hub.services.date_range.90d') }}</option>
                                <option value="all">{{ __('hub::hub.services.date_range.all') }}</option>
                            </flux:select>
                        </div>

                        {{-- Primary metrics --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-eye text-cyan-500 text-sm"></i>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pageviews</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($summaryMetrics['total_pageviews']) }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-users text-blue-500 text-sm"></i>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Visitors</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($summaryMetrics['unique_visitors']) }}</div>
                            </div>
                        </div>

                        {{-- Secondary metrics --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-amber-500 text-sm"></i>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bounce Rate</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $summaryMetrics['bounce_rate'] }}%</div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-clock text-purple-500 text-sm"></i>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg. Duration</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $this->formatDuration($summaryMetrics['avg_session_duration']) }}</div>
                            </div>
                        </div>

                        {{-- Mod stats --}}
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                        <i class="fa-solid fa-globe text-violet-600 dark:text-violet-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->analyticsStats['total_websites'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Websites</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <i class="fa-solid fa-check text-green-600 dark:text-green-400 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->analyticsStats['active_websites'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Active</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pageviews Chart --}}
                    @if(! empty($this->analyticsChartData))
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.pageviews_trend') }}</h3>
                            </div>

                            <flux:chart :value="$this->analyticsChartData" class="flex-1 min-h-[180px]">
                                <flux:chart.svg>
                                    <flux:chart.axis axis="y">
                                        <flux:chart.axis.grid class="stroke-zinc-200 dark:stroke-zinc-700" />
                                        <flux:chart.axis.tick :format="['notation' => 'compact']" class="text-zinc-500" />
                                    </flux:chart.axis>

                                    <flux:chart.area field="pageviews" class="text-cyan-500/20" />
                                    <flux:chart.line field="pageviews" class="text-cyan-500" />

                                    <flux:chart.axis axis="x" field="date">
                                        <flux:chart.axis.tick class="text-zinc-500" />
                                    </flux:chart.axis>

                                    <flux:chart.cursor />
                                </flux:chart.svg>

                                <flux:chart.tooltip>
                                    <flux:chart.tooltip.heading field="date" />
                                    <flux:chart.tooltip.value field="pageviews" label="Pageviews" />
                                </flux:chart.tooltip>
                            </flux:chart>
                        </div>
                    @endif
                </div>

                {{-- Acquisition Channels and Device Breakdown --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Acquisition Channels --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('hub::hub.services.headings.traffic_sources') }}</h3>

                        @if(! empty($this->analyticsAcquisitionChannels))
                            <div class="space-y-3">
                                @foreach($this->analyticsAcquisitionChannels as $channel)
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $channel['color'] }}"></div>
                                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $channel['name'] }}</span>
                                    <span class="text-sm font-medium tabular-nums text-gray-900 dark:text-white">{{ $channel['percentage'] }}%</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format($channel['count']) }}</span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-chart-pie text-3xl mb-2 opacity-50"></i>
                                <p>{{ __('hub::hub.services.empty.no_traffic_data') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Device Breakdown --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('hub::hub.services.headings.devices') }}</h3>

                        @if(! empty($this->analyticsDeviceBreakdown))
                            <div class="flex items-center justify-around py-4">
                                @foreach($this->analyticsDeviceBreakdown as $device)
                                <div class="text-center">
                                    <flux:icon :name="$device['icon']" class="size-8 mx-auto text-gray-400 dark:text-gray-500 mb-2" />
                                    <div class="text-2xl font-bold tabular-nums text-gray-900 dark:text-white">{{ $device['percentage'] }}%</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $device['name'] }}</div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-desktop text-3xl mb-2 opacity-50"></i>
                                <p>{{ __('hub::hub.services.empty.no_device_data') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'channels')
                {{-- Channels - All analytics sources grouped by type --}}
                <div class="space-y-6">
                    {{-- Header --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg">Channels</flux:heading>
                            <flux:subheading>All your analytics sources: websites, bio pages, social, and more</flux:subheading>
                        </div>
                        <flux:select wire:model.live="analyticsDateRange" size="sm">
                            <option value="7d">{{ __('hub::hub.services.date_range.7d') }}</option>
                            <option value="30d">{{ __('hub::hub.services.date_range.30d') }}</option>
                            <option value="90d">{{ __('hub::hub.services.date_range.90d') }}</option>
                            <option value="all">{{ __('hub::hub.services.date_range.all') }}</option>
                        </flux:select>
                    </div>

                    @if($this->analyticsChannels->isNotEmpty())
                        {{-- Channel list grouped by type --}}
                        @foreach($this->analyticsChannelsByType as $typeKey => $group)
                            @php $maxPageviews = $group['channels']->max('pageviews_count') ?: 1; @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-{{ $group['color'] }}-100 dark:bg-{{ $group['color'] }}-900/30 flex items-center justify-center">
                                        <i class="fa-solid fa-{{ $group['icon'] }} text-{{ $group['color'] }}-600 dark:text-{{ $group['color'] }}-400 text-sm"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $group['label'] }}</h3>
                                    <flux:badge size="sm" color="{{ $group['color'] }}">{{ $group['channels']->count() }}</flux:badge>
                                </div>
                                <div class="p-6 space-y-4">
                                    @foreach($group['channels'] as $channel)
                                        <div wire:click="selectWebsite({{ $channel->id }})" class="flex items-center gap-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-2 px-2 py-2 rounded-lg transition-colors {{ $this->selectedWebsiteId === $channel->id ? 'bg-gray-50 dark:bg-gray-700/50' : '' }}">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $channel->name }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:inline">{{ $channel->host }}</span>
                                                        <flux:badge :color="$channel->is_enabled ? 'green' : 'zinc'" size="sm">
                                                            {{ $channel->is_enabled ? 'Active' : 'Disabled' }}
                                                        </flux:badge>
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-2 tabular-nums">{{ number_format($channel->pageviews_count) }}</span>
                                                </div>
                                                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                                    <div class="h-full bg-{{ $group['color'] }}-500 rounded-full transition-all duration-300" style="width: {{ ($channel->pageviews_count / $maxPageviews) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Selected channel detail view (inline) --}}
                        @if($this->selectedWebsiteId)
                            @php $site = $this->selectedWebsite; @endphp
                            @if($site)
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                    {{-- Header --}}
                                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-{{ $site->channel_type?->color() ?? 'cyan' }}-100 dark:bg-{{ $site->channel_type?->color() ?? 'cyan' }}-900/30 flex items-center justify-center">
                                                <i class="fa-solid fa-{{ $site->channel_type?->icon() ?? 'globe' }} text-{{ $site->channel_type?->color() ?? 'cyan' }}-600 dark:text-{{ $site->channel_type?->color() ?? 'cyan' }}-400"></i>
                                            </div>
                                            <div>
                                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $site->name }}</h2>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $site->host }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" color="{{ $site->channel_type?->color() ?? 'cyan' }}">{{ $site->channel_type?->label() ?? 'Mod' }}</flux:badge>
                                            <flux:button wire:click="$set('selectedWebsiteId', null)" icon="x-mark" variant="ghost" size="sm" />
                                        </div>
                                    </div>

                                    <div class="p-6 space-y-6">
                                        {{-- Stats cards --}}
                                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Visitors</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($site->visitors_count) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Sessions</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($site->sessions_count) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pageviews</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($site->pageviews_count) }}</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Bounce Rate</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $site->bounce_rate }}%</div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Avg. Duration</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $this->formatDuration($site->avg_duration) }}</div>
                                            </div>
                                        </div>

                                        {{-- Chart --}}
                                        @if(! empty($this->selectedWebsiteChartData))
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Traffic Overview</h3>
                                                <flux:chart :value="$this->selectedWebsiteChartData" class="aspect-[3/1] max-h-48">
                                                    <flux:chart.svg>
                                                        <flux:chart.axis axis="y">
                                                            <flux:chart.axis.grid class="stroke-zinc-200 dark:stroke-zinc-700" />
                                                            <flux:chart.axis.tick :format="['notation' => 'compact']" class="text-zinc-500" />
                                                        </flux:chart.axis>
                                                        <flux:chart.area field="visitors" class="text-cyan-500/20" />
                                                        <flux:chart.line field="visitors" class="text-cyan-500" />
                                                        <flux:chart.axis axis="x" field="date">
                                                            <flux:chart.axis.tick class="text-zinc-500" />
                                                        </flux:chart.axis>
                                                        <flux:chart.cursor />
                                                    </flux:chart.svg>
                                                    <flux:chart.tooltip>
                                                        <flux:chart.tooltip.heading field="date" />
                                                        <flux:chart.tooltip.value field="visitors" label="Visitors" />
                                                    </flux:chart.tooltip>
                                                </flux:chart>
                                            </div>
                                        @endif

                                        {{-- Top pages --}}
                                        @if(count($this->selectedWebsiteTopPages) > 0)
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Pages</h3>
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                                                <th class="pb-2 font-medium">Page</th>
                                                                <th class="pb-2 font-medium text-right">Views</th>
                                                                <th class="pb-2 font-medium text-right">Visitors</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                            @foreach($this->selectedWebsiteTopPages as $page)
                                                                <tr>
                                                                    <td class="py-2 pr-4">
                                                                        <span class="truncate block max-w-[300px]" title="{{ $page['path'] }}">{{ $page['path'] }}</span>
                                                                    </td>
                                                                    <td class="py-2 text-right text-gray-900 dark:text-white font-medium tabular-nums">{{ number_format($page['views']) }}</td>
                                                                    <td class="py-2 text-right text-gray-600 dark:text-gray-300 tabular-nums">{{ number_format($page['visitors']) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        {{-- No channels yet --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex flex-col items-center justify-center py-12 px-4">
                                <div class="w-16 h-16 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4">
                                    <flux:icon name="signal" class="size-8 text-cyan-500" />
                                </div>
                                <flux:heading size="lg" class="text-center">No channels yet</flux:heading>
                                <flux:subheading class="text-center mt-1 max-w-sm">
                                    Channels are created automatically when you add websites, bio pages, or connect social accounts.
                                </flux:subheading>
                            </div>
                        </div>
                    @endif
                </div>
            @elseif ($tab === 'goals')
                {{-- Goals Header --}}
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('hub::hub.services.tabs.goals') }}</flux:heading>
                        <flux:subheading>{{ __('hub::hub.services.empty.no_goals_description') }}</flux:subheading>
                    </div>
                    <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.analytics') }}" wire:navigate icon="plus" variant="primary">
                        {{ __('hub::hub.services.actions.create_goal') }}
                    </flux:button>
                </div>

                @if($this->analyticsGoals->isNotEmpty())
                    {{-- Goals Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->analyticsGoals as $goal)
                            @php
                                $typeInfo = $this->analyticsGoalTypes[$goal->type] ?? ['label' => ucfirst($goal->type), 'color' => 'zinc', 'icon' => 'flag'];
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <flux:heading size="sm" class="truncate">{{ $goal->name }}</flux:heading>
                                        <div class="flex items-center gap-2 mt-1">
                                            <flux:badge :color="$typeInfo['color']" size="sm">{{ $typeInfo['label'] }}</flux:badge>
                                            @if($goal->website)
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $goal->website->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                        <flux:menu>
                                            <flux:menu.item icon="pencil" href="{{ \Website\Hub\Boot::hubRoute('hub.analytics') }}" wire:navigate>Edit</flux:menu.item>
                                            <flux:menu.item icon="{{ $goal->is_enabled ? 'eye-slash' : 'eye' }}">
                                                {{ $goal->is_enabled ? 'Disable' : 'Enable' }}
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>

                                {{-- Goal Configuration --}}
                                <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                                    @switch($goal->type)
                                        @case('pageview')
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="document-text" class="size-4" />
                                                <span class="truncate">{{ $goal->path ?? '/' }}</span>
                                            </div>
                                            @break
                                        @case('event')
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="bolt" class="size-4" />
                                                <span class="truncate">{{ $goal->key ?? 'custom_event' }}</span>
                                            </div>
                                            @break
                                        @case('duration')
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="clock" class="size-4" />
                                                <span>{{ $goal->threshold ?? 0 }}s minimum</span>
                                            </div>
                                            @break
                                        @case('pages_per_session')
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="document-duplicate" class="size-4" />
                                                <span>{{ $goal->threshold ?? 0 }} pages minimum</span>
                                            </div>
                                            @break
                                    @endswitch
                                </div>

                                {{-- Stats Row --}}
                                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <div class="flex items-center gap-4 text-sm">
                                        <div>
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.columns.conversions') }}</span>
                                            <span class="ml-1 font-semibold text-gray-900 dark:text-white tabular-nums">{{ number_format($goal->conversions_count ?? 0) }}</span>
                                        </div>
                                    </div>
                                    <flux:badge :color="$goal->is_enabled ? 'green' : 'zinc'" size="sm">
                                        {{ $goal->is_enabled ? __('hub::hub.services.status.active') : __('hub::hub.services.status.disabled') }}
                                    </flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex flex-col items-center justify-center py-12 px-4">
                            <div class="w-16 h-16 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4">
                                <flux:icon name="flag" class="size-8 text-cyan-500" />
                            </div>
                            <flux:heading size="lg" class="text-center">{{ __('hub::hub.services.empty.no_goals_title') }}</flux:heading>
                            <flux:subheading class="text-center mt-1 max-w-sm">
                                {{ __('hub::hub.services.empty.no_goals_description') }}
                            </flux:subheading>
                            <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.analytics') }}" wire:navigate icon="plus" class="mt-4">
                                {{ __('hub::hub.services.actions.create_goal') }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            @elseif ($tab === 'settings')
                @php $primaryWebsite = $this->analyticsWebsites->first(); @endphp
                @if($primaryWebsite)
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- General Settings --}}
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">General Settings</h3>
                            <form wire:submit="saveAnalyticsSettings" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <flux:input wire:model="analyticsSettingsName" label="Website Name" />
                                    </div>
                                    <div>
                                        <flux:input wire:model="analyticsSettingsHost" label="Domain" />
                                    </div>
                                </div>

                                <div>
                                    <flux:select wire:model.live="analyticsSettingsTrackingType" label="Tracking Type">
                                        <option value="lightweight">No GDPR Notice Required</option>
                                        <option value="normal">GDPR Notice Required</option>
                                    </flux:select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @if($analyticsSettingsTrackingType === 'lightweight')
                                            Privacy-first: anonymised IPs, no cookies, no personal data.
                                        @else
                                            Full tracking: session replay, scroll depth. Requires consent.
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-6">
                                    <flux:checkbox wire:model="analyticsSettingsEnabled" label="Tracking enabled" />
                                    <flux:checkbox wire:model="analyticsSettingsPublicStats" label="Public statistics page" />
                                </div>

                                <div>
                                    <flux:textarea wire:model="analyticsSettingsExcludedIps" label="Excluded IPs" rows="2" placeholder="One IP per line" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Visits from these IPs won't be tracked</p>
                                </div>

                                <div class="pt-2">
                                    <flux:button type="submit" variant="primary">Save Settings</flux:button>
                                </div>
                            </form>
                        </div>

                        {{-- Tracking Code --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tracking Code</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Add this to your website's <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">&lt;head&gt;</code>:</p>
                            <div class="relative">
                                <pre class="bg-gray-900 text-gray-100 p-3 rounded-lg text-xs overflow-x-auto"><code>&lt;script defer data-key="{{ $primaryWebsite->pixel_key }}" src="{{ asset('js/analytics.js') }}"&gt;&lt;/script&gt;</code></pre>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Pixel Key:</p>
                                <div class="flex items-center gap-2">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded flex-1 truncate">{{ $primaryWebsite->pixel_key }}</code>
                                    <flux:button wire:click="regenerateAnalyticsPixelKey" wire:confirm="Regenerate pixel key? You'll need to update your website." size="xs" variant="ghost">
                                        <i class="fa-solid fa-rotate"></i>
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 rounded-full bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-globe text-2xl text-cyan-500"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No website configured</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4 max-w-sm">Add a website to configure analytics settings.</p>
                            <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.analytics.create') }}" wire:navigate icon="plus">
                                Add Website
                            </flux:button>
                        </div>
                    </div>
                @endif
            @endif
        @endif

        {{-- NOTIFY SERVICE --}}
        @if ($service === 'notify')
            @if ($tab === 'dashboard')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->notifyStatCards as $card)
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Coloured left border accent --}}
                            <div @class([
                                'absolute left-0 top-0 bottom-0 w-1',
                                'bg-indigo-500' => $card['color'] === 'indigo' || $card['color'] === 'purple',
                                'bg-blue-500' => $card['color'] === 'blue',
                                'bg-orange-500' => $card['color'] === 'orange',
                                'bg-green-500' => $card['color'] === 'green',
                            ])></div>

                            <div class="p-5 pl-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Label first (smaller, secondary) --}}
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>

                                        {{-- Value (larger, bolder, primary) --}}
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $card['value'] }}</p>
                                    </div>

                                    {{-- Icon with background circle --}}
                                    <div @class([
                                        'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                        'bg-indigo-100 dark:bg-indigo-900/30' => $card['color'] === 'indigo' || $card['color'] === 'purple',
                                        'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                        'bg-orange-100 dark:bg-orange-900/30' => $card['color'] === 'orange',
                                        'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                    ])>
                                        <i @class([
                                            $faIcon($card['icon']),
                                            'text-xl',
                                            'text-indigo-600 dark:text-indigo-400' => $card['color'] === 'indigo' || $card['color'] === 'purple',
                                            'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            'text-orange-600 dark:text-orange-400' => $card['color'] === 'orange',
                                            'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                        ])></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Websites by Subscribers --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.websites_by_subscribers') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.notify') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            {{ __('hub::hub.services.actions.manage_notifyhost') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.website') }}</flux:table.column>
                            <flux:table.column>{{ __('hub::hub.services.columns.host') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.subscribers') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->notifyWebsites as $website)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">{{ $website->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $website->host }}</flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:badge color="indigo" size="sm">{{ number_format($website->subscribers_count) }}</flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3">
                                        <div class="flex flex-col items-center py-12">
                                            <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                                                <flux:icon name="bell" class="size-8 text-indigo-500" />
                                            </div>
                                            <flux:heading size="lg">{{ __('hub::hub.services.empty.no_websites_title') }}</flux:heading>
                                            <flux:subheading class="mt-1">{{ __('hub::hub.services.empty.websites') }}</flux:subheading>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'subscribers')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.recent_subscribers') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.notify') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            {{ __('hub::hub.services.actions.view_all') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.endpoint') }}</flux:table.column>
                            <flux:table.column>{{ __('hub::hub.services.columns.website') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.subscribed') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->notifySubscribers as $sub)
                                <flux:table.row>
                                    <flux:table.cell class="font-mono truncate max-w-xs">{{ Str::limit($sub->endpoint, 50) }}</flux:table.cell>
                                    <flux:table.cell>{{ $sub->website?->name ?? __('hub::hub.services.misc.na') }}</flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge :color="$sub->is_subscribed ? 'green' : 'zinc'" size="sm">
                                            {{ $sub->is_subscribed ? __('hub::hub.services.status.active') : __('hub::hub.services.status.inactive') }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">{{ $sub->subscribed_at?->diffForHumans() ?? __('hub::hub.services.misc.na') }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        <div class="flex flex-col items-center py-12">
                                            <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                                                <flux:icon name="users" class="size-8 text-indigo-500" />
                                            </div>
                                            <flux:heading size="lg">{{ __('hub::hub.services.empty.no_subscribers_title') }}</flux:heading>
                                            <flux:subheading class="mt-1">{{ __('hub::hub.services.empty.subscribers') }}</flux:subheading>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'campaigns')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.campaigns') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.notify') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-500 text-white hover:bg-indigo-600">
                            <i class="fa-solid fa-plus text-xs"></i>
                            {{ __('hub::hub.services.actions.create_campaign') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.campaign') }}</flux:table.column>
                            <flux:table.column>{{ __('hub::hub.services.columns.website') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.stats') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->notifyCampaigns as $campaign)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">{{ $campaign->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $campaign->website?->name ?? __('hub::hub.services.misc.na') }}</flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge size="sm" :color="match($campaign->status) {
                                            'sent' => 'green',
                                            'sending' => 'blue',
                                            'scheduled' => 'indigo',
                                            'draft' => 'zinc',
                                            'failed' => 'red',
                                            default => 'zinc',
                                        }">
                                            {{ __('hub::hub.services.status.' . $campaign->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        @if ($campaign->status === 'sent')
                                            <div class="flex flex-wrap justify-end gap-1">
                                                <flux:badge color="green" size="sm" icon="check-circle">{{ number_format($campaign->delivery_rate ?? 0, 1) }}%</flux:badge>
                                                <flux:badge color="indigo" size="sm" icon="cursor-arrow-rays">{{ number_format($campaign->click_through_rate ?? 0, 1) }}%</flux:badge>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4">
                                        <div class="flex flex-col items-center py-12">
                                            <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-4">
                                                <flux:icon name="bell" class="size-8 text-indigo-500" />
                                            </div>
                                            <flux:heading size="lg">{{ __('hub::hub.services.empty.no_campaigns_title') }}</flux:heading>
                                            <flux:subheading class="mt-1">{{ __('hub::hub.services.empty.campaigns') }}</flux:subheading>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endif

        {{-- TRUST SERVICE --}}
        @if ($service === 'trust')
            @if ($tab === 'dashboard')
                {{-- Aggregated Campaign Metrics Summary --}}
                <div class="grid grid-cols-3 md:grid-cols-5 gap-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 mb-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($this->trustAggregatedMetrics['impressions']) }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.trust.metrics.impressions') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($this->trustAggregatedMetrics['clicks']) }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.trust.metrics.clicks') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format($this->trustAggregatedMetrics['conversions']) }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.trust.metrics.conversions') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $this->trustAggregatedMetrics['ctr'] }}%</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.trust.metrics.ctr') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $this->trustAggregatedMetrics['cvr'] }}%</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('hub::hub.services.trust.metrics.cvr') }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->trustStatCards as $card)
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Coloured left border accent --}}
                            <div @class([
                                'absolute left-0 top-0 bottom-0 w-1',
                                'bg-blue-500' => $card['color'] === 'blue',
                                'bg-green-500' => $card['color'] === 'green',
                                'bg-purple-500' => $card['color'] === 'purple',
                                'bg-orange-500' => $card['color'] === 'orange',
                            ])></div>

                            <div class="p-5 pl-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        {{-- Label first (smaller, secondary) --}}
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>

                                        {{-- Value (larger, bolder, primary) --}}
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ $card['value'] }}</p>
                                    </div>

                                    {{-- Icon with background circle --}}
                                    <div @class([
                                        'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                        'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                        'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                        'bg-purple-100 dark:bg-purple-900/30' => $card['color'] === 'purple',
                                        'bg-orange-100 dark:bg-orange-900/30' => $card['color'] === 'orange',
                                    ])>
                                        <i @class([
                                            $faIcon($card['icon']),
                                            'text-xl',
                                            'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                            'text-purple-600 dark:text-purple-400' => $card['color'] === 'purple',
                                            'text-orange-600 dark:text-orange-400' => $card['color'] === 'orange',
                                        ])></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Campaigns Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.campaigns') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.trust') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            {{ __('hub::hub.services.actions.manage_trusthost') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.campaign') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.widgets') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.performance') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->trustCampaigns->take(5) as $campaign)
                                @php
                                    // Calculate CVR for performance colour
                                    $impressions = $campaign->notifications->sum('impressions');
                                    $conversions = $campaign->notifications->sum('conversions');
                                    $cvr = $impressions > 0 ? ($conversions / $impressions) * 100 : 0;
                                    $perfClass = match(true) {
                                        $cvr >= 5 => 'border-l-4 border-l-green-500',
                                        $cvr >= 1 => 'border-l-4 border-l-yellow-500',
                                        $impressions > 0 => 'border-l-4 border-l-red-500',
                                        default => '',
                                    };
                                    $perfBadgeColor = match(true) {
                                        $cvr >= 5 => 'green',
                                        $cvr >= 1 => 'yellow',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:table.row class="{{ $perfClass }}">
                                    <flux:table.cell variant="strong">
                                        <div class="flex items-center gap-2">
                                            <flux:icon :name="$campaign->is_enabled ? 'check-circle' : 'pause-circle'" :class="$campaign->is_enabled ? 'text-green-500' : 'text-zinc-400'" class="size-4" />
                                            {{ $campaign->name }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">{{ $campaign->notifications_count }}</flux:table.cell>
                                    <flux:table.cell align="center">
                                        @if($impressions > 0)
                                            <flux:badge :color="$perfBadgeColor" size="sm">{{ number_format($cvr, 1) }}% CVR</flux:badge>
                                        @else
                                            <span class="text-zinc-400 text-sm">-</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge :color="$campaign->is_enabled ? 'green' : 'zinc'" size="sm">
                                            {{ $campaign->is_enabled ? __('hub::hub.services.status.active') : __('hub::hub.services.status.disabled') }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-8">{{ __('hub::hub.services.empty.campaigns') }}</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'campaigns')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.all_campaigns') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.trust') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-orange-500 text-white hover:bg-orange-600">
                            <i class="fa-solid fa-plus text-xs"></i>
                            {{ __('hub::hub.services.actions.create_campaign') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.campaign') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.widgets') }}</flux:table.column>
                            <flux:table.column align="center">{{ __('hub::hub.services.columns.status') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->trustCampaigns as $campaign)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">
                                        <div class="flex items-center gap-2">
                                            <flux:icon :name="$campaign->is_enabled ? 'check-circle' : 'pause-circle'" :class="$campaign->is_enabled ? 'text-green-500' : 'text-zinc-400'" class="size-4" />
                                            {{ $campaign->name }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell align="center">{{ $campaign->notifications_count }}</flux:table.cell>
                                    <flux:table.cell align="center">
                                        <flux:badge :color="$campaign->is_enabled ? 'green' : 'zinc'" size="sm">
                                            {{ $campaign->is_enabled ? __('hub::hub.services.status.active') : __('hub::hub.services.status.disabled') }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="text-center py-8">{{ __('hub::hub.services.empty.campaigns') }}</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @elseif ($tab === 'notifications')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.headings.widgets_by_impressions') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.trust') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            {{ __('hub::hub.services.actions.view_all') }}
                        </a>
                    </div>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('hub::hub.services.columns.widget') }}</flux:table.column>
                            <flux:table.column>{{ __('hub::hub.services.columns.campaign') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.impressions') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.clicks') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('hub::hub.services.columns.conversions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->trustNotifications as $notification)
                                <flux:table.row>
                                    <flux:table.cell variant="strong">{{ $notification->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $notification->campaign?->name ?? __('hub::hub.services.misc.na') }}</flux:table.cell>
                                    <flux:table.cell align="end" variant="strong">{{ number_format($notification->impressions) }}</flux:table.cell>
                                    <flux:table.cell align="end" variant="strong">{{ number_format($notification->clicks) }}</flux:table.cell>
                                    <flux:table.cell align="end" variant="strong">{{ number_format($notification->conversions) }}</flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" class="text-center py-8">{{ __('hub::hub.services.empty.widgets') }}</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        @endif

        {{-- SUPPORT SERVICE --}}
        @if ($service === 'support')
            @if ($tab === 'dashboard')
                {{-- Inbox Health Section --}}
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('hub::hub.services.support.inbox_health') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($this->supportInboxHealthCards as $card)
                            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div @class([
                                    'absolute left-0 top-0 bottom-0 w-1',
                                    'bg-blue-500' => $card['color'] === 'blue',
                                    'bg-green-500' => $card['color'] === 'green',
                                ])></div>
                                <div class="p-5 pl-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div @class([
                                                'text-2xl font-bold tabular-nums',
                                                'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                                'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                            ])>{{ $card['value'] }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                                        </div>
                                        <div @class([
                                            'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                            'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                            'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                        ])>
                                            <i @class([
                                                $faIcon($card['icon']),
                                                'text-xl',
                                                'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                                'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                            ])></i>
                                        </div>
                                    </div>
                                    @if(isset($card['oldest']) && $card['oldest'])
                                        <div class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                            {{ __('hub::hub.services.support.oldest') }}: {{ $card['oldest']->created_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Today's Activity Section --}}
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('hub::hub.services.support.todays_activity') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($this->supportActivityCards as $card)
                            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div @class([
                                    'absolute left-0 top-0 bottom-0 w-1',
                                    'bg-violet-500' => $card['color'] === 'violet',
                                    'bg-green-500' => $card['color'] === 'green',
                                    'bg-blue-500' => $card['color'] === 'blue',
                                ])></div>
                                <div class="p-5 pl-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div @class([
                                                'text-2xl font-bold tabular-nums',
                                                'text-violet-600 dark:text-violet-400' => $card['color'] === 'violet',
                                                'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                                'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            ])>{{ $card['value'] }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                                        </div>
                                        <div @class([
                                            'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                            'bg-violet-100 dark:bg-violet-900/30' => $card['color'] === 'violet',
                                            'bg-green-100 dark:bg-green-900/30' => $card['color'] === 'green',
                                            'bg-blue-100 dark:bg-blue-900/30' => $card['color'] === 'blue',
                                        ])>
                                            <i @class([
                                                $faIcon($card['icon']),
                                                'text-xl',
                                                'text-violet-600 dark:text-violet-400' => $card['color'] === 'violet',
                                                'text-green-600 dark:text-green-400' => $card['color'] === 'green',
                                                'text-blue-600 dark:text-blue-400' => $card['color'] === 'blue',
                                            ])></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Performance Section --}}
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('hub::hub.services.support.performance') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($this->supportPerformanceCards as $card)
                            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div @class([
                                    'absolute left-0 top-0 bottom-0 w-1',
                                    'bg-amber-500' => $card['color'] === 'amber',
                                    'bg-teal-500' => $card['color'] === 'teal',
                                ])></div>
                                <div class="p-5 pl-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div @class([
                                                'text-2xl font-bold tabular-nums',
                                                'text-amber-600 dark:text-amber-400' => $card['color'] === 'amber',
                                                'text-teal-600 dark:text-teal-400' => $card['color'] === 'teal',
                                            ])>{{ $card['value'] }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                                        </div>
                                        <div @class([
                                            'w-12 h-12 rounded-full flex items-center justify-center shrink-0',
                                            'bg-amber-100 dark:bg-amber-900/30' => $card['color'] === 'amber',
                                            'bg-teal-100 dark:bg-teal-900/30' => $card['color'] === 'teal',
                                        ])>
                                            <i @class([
                                                $faIcon($card['icon']),
                                                'text-xl',
                                                'text-amber-600 dark:text-amber-400' => $card['color'] === 'amber',
                                                'text-teal-600 dark:text-teal-400' => $card['color'] === 'teal',
                                            ])></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Recent Conversations --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.services.support.recent_conversations') }}</h3>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'support', 'tab' => 'inbox']) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300">
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            {{ __('hub::hub.services.support.view_inbox') }}
                        </a>
                    </div>
                    @if($this->supportRecentConversations->isEmpty())
                        <div class="p-6 text-center">
                            <div class="w-16 h-16 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-inbox text-2xl text-teal-500"></i>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-1">{{ __('hub::hub.services.support.empty_inbox') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('hub::hub.services.support.empty_inbox_description') }}</p>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($this->supportRecentConversations as $conversation)
                                <li class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="shrink-0">
                                            <div @class([
                                                'w-10 h-10 rounded-full flex items-center justify-center',
                                                'bg-green-100 dark:bg-green-900/30' => $conversation->status === 'active',
                                                'bg-yellow-100 dark:bg-yellow-900/30' => $conversation->status === 'pending',
                                                'bg-zinc-100 dark:bg-zinc-900/30' => $conversation->status === 'closed',
                                                'bg-red-100 dark:bg-red-900/30' => $conversation->status === 'spam',
                                            ])>
                                                <i @class([
                                                    'fa-solid fa-user text-sm',
                                                    'text-green-600 dark:text-green-400' => $conversation->status === 'active',
                                                    'text-yellow-600 dark:text-yellow-400' => $conversation->status === 'pending',
                                                    'text-zinc-600 dark:text-zinc-400' => $conversation->status === 'closed',
                                                    'text-red-600 dark:text-red-400' => $conversation->status === 'spam',
                                                ])></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $conversation->customer?->name ?? $conversation->customer?->email ?? __('hub::hub.services.support.unknown') }}
                                                </span>
                                                <flux:badge size="sm" :color="$this->supportStatusColor($conversation->status)">
                                                    {{ ucfirst($conversation->status) }}
                                                </flux:badge>
                                            </div>
                                            <p class="text-sm text-gray-900 dark:text-gray-100 truncate mt-0.5">{{ $conversation->subject }}</p>
                                            @if($conversation->latestThread)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                    {{ Str::limit(strip_tags($conversation->latestThread->body ?? ''), 60) }}
                                                </p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                <span>#{{ $conversation->number }}</span>
                                                <span>{{ $conversation->mailbox?->name ?? __('hub::hub.services.support.na') }}</span>
                                                <span>{{ $conversation->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @elseif ($tab === 'inbox')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'support', 'tab' => 'inbox']) }}" wire:navigate class="text-teal-600 hover:text-teal-700">{{ __('hub::hub.services.support.open_full_inbox') }}</a>
                        </p>
                    </div>
                </div>
            @elseif ($tab === 'settings')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'support', 'tab' => 'settings']) }}" wire:navigate class="text-teal-600 hover:text-teal-700">{{ __('hub::hub.services.support.open_settings') }}</a>
                        </p>
                    </div>
                </div>
            @endif
        @endif

        {{-- COMMERCE SERVICE --}}
        @if ($service === 'commerce')
            @if ($tab === 'dashboard')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <i class="fa-solid fa-shopping-cart text-4xl text-green-500 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Commerce Dashboard</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Manage orders, subscriptions, and coupons.</p>
                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'commerce']) }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fa-solid fa-gauge"></i>
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            @elseif ($tab === 'orders')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'commerce', 'tab' => 'orders']) }}" wire:navigate class="text-green-600 hover:text-green-700">Open orders →</a>
                        </p>
                    </div>
                </div>
            @elseif ($tab === 'subscriptions')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'commerce', 'tab' => 'subscriptions']) }}" wire:navigate class="text-green-600 hover:text-green-700">Open subscriptions →</a>
                        </p>
                    </div>
                </div>
            @elseif ($tab === 'coupons')
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">
                            <a href="{{ \Website\Hub\Boot::hubRoute('hub.services', ['service' => 'commerce', 'tab' => 'coupons']) }}" wire:navigate class="text-green-600 hover:text-green-700">Open coupons →</a>
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
