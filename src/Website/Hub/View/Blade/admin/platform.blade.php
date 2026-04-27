<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Platform Admin</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage users, tiers, and platform operations</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-violet-500/20 text-violet-600 dark:text-violet-400">
                <core:icon name="crown" class="mr-1.5" />
                Hades Only
            </span>
        </div>
    </div>

    <!-- Action message -->
    @if($actionMessage)
    <div class="mb-6 p-4 rounded-lg {{ $actionType === 'success' ? 'bg-green-500/20 text-green-700 dark:text-green-400' : ($actionType === 'warning' ? 'bg-amber-500/20 text-amber-700 dark:text-amber-400' : 'bg-red-500/20 text-red-700 dark:text-red-400') }}">
        <div class="flex items-center">
            <core:icon name="{{ $actionType === 'success' ? 'check-circle' : ($actionType === 'warning' ? 'triangle-exclamation' : 'circle-xmark') }}" class="mr-2" />
            {{ $actionMessage }}
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($stats['total_users']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Users</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['verified_users']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Verified</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($stats['hades_users']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Hades</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['apollo_users']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Apollo</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ number_format($stats['free_users']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Free</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['users_today']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Today</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-4">
            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($stats['users_this_week']) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">This Week</div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- User Management -->
        <div class="col-span-full xl:col-span-8">
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">User Management</h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Search -->
                            <core:input
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search users..."
                                icon="magnifying-glass"
                                size="sm"
                                class="w-48"
                            />
                            <!-- Tier filter -->
                            <core:select wire:model.live="tierFilter" size="sm">
                                <core:select.option value="">All Tiers</core:select.option>
                                @foreach($tiers as $tier)
                                <core:select.option value="{{ $tier->value }}">{{ ucfirst($tier->value) }}</core:select.option>
                                @endforeach
                            </core:select>
                            <!-- Verified filter -->
                            <core:select wire:model.live="verifiedFilter" size="sm">
                                <core:select.option value="">All Status</core:select.option>
                                <core:select.option value="1">Verified</core:select.option>
                                <core:select.option value="0">Unverified</core:select.option>
                            </core:select>
                        </div>
                    </div>
                </header>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/20">
                                <th class="px-4 py-3 text-left font-medium cursor-pointer hover:text-gray-600 dark:hover:text-gray-300" wire:click="sortBy('name')">
                                    <div class="flex items-center gap-1">
                                        Name
                                        @if($sortField === 'name')
                                        <core:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="text-xs" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left font-medium cursor-pointer hover:text-gray-600 dark:hover:text-gray-300" wire:click="sortBy('email')">
                                    <div class="flex items-center gap-1">
                                        Email
                                        @if($sortField === 'email')
                                        <core:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="text-xs" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left font-medium">Tier</th>
                                <th class="px-4 py-3 text-left font-medium">Verified</th>
                                <th class="px-4 py-3 text-left font-medium cursor-pointer hover:text-gray-600 dark:hover:text-gray-300" wire:click="sortBy('created_at')">
                                    <div class="flex items-center gap-1">
                                        Joined
                                        @if($sortField === 'created_at')
                                        <core:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="text-xs" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ substr($user->name, 0, 2) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $tierColor = match($user->tier?->value ?? 'free') {
                                            'hades' => 'violet',
                                            'apollo' => 'blue',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $tierColor }}-500/20 text-{{ $tierColor }}-600 dark:text-{{ $tierColor }}-400">
                                        {{ ucfirst($user->tier?->value ?? 'free') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($user->email_verified_at)
                                    <span class="inline-flex items-center text-green-600 dark:text-green-400">
                                        <core:icon name="check-circle" class="mr-1" />
                                        <span class="text-xs">Verified</span>
                                    </span>
                                    @else
                                    <span class="inline-flex items-center text-amber-600 dark:text-amber-400">
                                        <core:icon name="clock" class="mr-1" />
                                        <span class="text-xs">Pending</span>
                                    </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$user->email_verified_at)
                                        <button wire:click="verifyEmail({{ $user->id }})" class="p-1.5 text-green-600 hover:bg-green-500/20 rounded-lg transition" title="Verify email">
                                            <core:icon name="check" />
                                        </button>
                                        @endif
                                        <a href="{{ \Website\Hub\Boot::hubRoute('hub.platform.user', $user->id) }}" wire:navigate class="p-1.5 text-violet-600 hover:bg-violet-500/20 rounded-lg transition" title="View user details">
                                            <core:icon name="arrow-right" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No users found matching your criteria.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($users->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700/60">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- System Info & DevOps -->
        <div class="col-span-full xl:col-span-4 space-y-6">
            <!-- System Info -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">System Info</h2>
                </header>
                <div class="p-5 space-y-3">
                    @foreach($systemInfo as $label => $value)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', ucwords($label, '_')) }}</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- DevOps Tools -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">DevOps Tools</h2>
                </header>
                <div class="p-5 space-y-3">
                    <button wire:click="clearCache" wire:loading.attr="disabled" class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="broom" class="mr-3 text-amber-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Clear Cache</span>
                        </div>
                        <core:icon name="chevron-right" class="text-gray-400" wire:loading.remove wire:target="clearCache" />
                        <core:icon name="spinner" class="text-gray-400 animate-spin" wire:loading wire:target="clearCache" />
                    </button>
                    <button wire:click="clearOpcache" wire:loading.attr="disabled" class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="microchip" class="mr-3 text-blue-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Clear OPcache</span>
                        </div>
                        <core:icon name="chevron-right" class="text-gray-400" wire:loading.remove wire:target="clearOpcache" />
                        <core:icon name="spinner" class="text-gray-400 animate-spin" wire:loading wire:target="clearOpcache" />
                    </button>
                    <button wire:click="restartQueue" wire:loading.attr="disabled" class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="rotate" class="mr-3 text-green-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Restart Queue</span>
                        </div>
                        <core:icon name="chevron-right" class="text-gray-400" wire:loading.remove wire:target="restartQueue" />
                        <core:icon name="spinner" class="text-gray-400 animate-spin" wire:loading wire:target="restartQueue" />
                    </button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">Quick Links</h2>
                </header>
                <div class="p-5 space-y-2">
                    <a href="/horizon" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="layer-group" class="mr-3 text-violet-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Horizon</span>
                        </div>
                        <core:icon name="arrow-up-right-from-square" class="text-gray-400" />
                    </a>
                    <a href="/telescope" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="satellite-dish" class="mr-3 text-blue-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Telescope</span>
                        </div>
                        <core:icon name="arrow-up-right-from-square" class="text-gray-400" />
                    </a>
                    <a href="/pulse" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center">
                            <core:icon name="heart-pulse" class="mr-3 text-red-500" />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Pulse</span>
                        </div>
                        <core:icon name="arrow-up-right-from-square" class="text-gray-400" />
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
