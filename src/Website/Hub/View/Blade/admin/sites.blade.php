<admin:module :title="__('hub::hub.workspaces.title')" :subtitle="__('hub::hub.workspaces.subtitle')">
    <x-slot:actions>
        <core:button icon="plus">{{ __('hub::hub.workspaces.add') }}</core:button>
    </x-slot:actions>

    @if($this->workspaces->isEmpty())
        <div class="text-center py-12">
            <core:icon name="layer-group" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
            <p class="text-gray-500 dark:text-gray-400">{{ __('hub::hub.workspaces.empty') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($this->workspaces as $workspace)
                @php
                    $isCurrent = $workspace->slug === $this->currentWorkspaceSlug;
                    $colorMap = [
                        'violet' => 'bg-violet-100 dark:bg-violet-500/20 text-violet-500',
                        'blue' => 'bg-blue-100 dark:bg-blue-500/20 text-blue-500',
                        'green' => 'bg-green-100 dark:bg-green-500/20 text-green-500',
                        'orange' => 'bg-orange-100 dark:bg-orange-500/20 text-orange-500',
                        'red' => 'bg-red-100 dark:bg-red-500/20 text-red-500',
                        'cyan' => 'bg-cyan-100 dark:bg-cyan-500/20 text-cyan-500',
                        'gray' => 'bg-gray-100 dark:bg-gray-500/20 text-gray-500',
                    ];
                    $color = $workspace->color ?? 'violet';
                    $iconClasses = $colorMap[$color] ?? $colorMap['violet'];
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg {{ $iconClasses }} flex items-center justify-center">
                                    <core:icon :name="$workspace->icon ?? 'folder'" class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $workspace->name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $workspace->domain ?? $workspace->slug }}</p>
                                </div>
                            </div>
                            @if($isCurrent)
                                <flux:badge color="green" size="sm" icon="check">
                                    {{ __('hub::hub.workspaces.active') }}
                                </flux:badge>
                            @else
                                <flux:button wire:click="activate('{{ $workspace->slug }}')" size="sm" variant="ghost">
                                    {{ __('hub::hub.workspaces.activate') }}
                                </flux:button>
                            @endif
                        </div>

                        @if($workspace->description)
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $workspace->description }}</p>
                        @endif
                    </div>

                    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($workspace->domain)
                                <flux:button href="https://{{ $workspace->domain }}" target="_blank" size="xs" variant="ghost" icon="arrow-top-right-on-square">
                                    Visit
                                </flux:button>
                            @endif
                        </div>
                        <flux:button href="{{ \Website\Hub\Boot::hubRoute('hub.sites.settings', ['workspace' => $workspace->slug]) }}" wire:navigate size="xs" variant="ghost" icon-trailing="chevron-right">
                            Settings
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</admin:module>
