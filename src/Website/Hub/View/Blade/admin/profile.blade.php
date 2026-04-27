<div>
    <!-- Profile Header -->
    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-6 overflow-hidden">
        <!-- Gradient banner -->
        <div class="h-32 bg-gradient-to-r {{ $tierColor }}"></div>

        <div class="px-6 pb-6">
            <!-- Avatar and basic info -->
            <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-12">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br {{ $tierColor }} flex items-center justify-center text-white text-3xl font-bold ring-4 ring-white dark:ring-gray-800 shadow-lg">
                    {{ $userInitials }}
                </div>
                <div class="flex-1 sm:pb-2">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $userName }}</h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r {{ $tierColor }} text-white w-fit">
                            {{ $userTier }}
                        </span>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $userEmail }}</p>
                    @if($memberSince)
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('hub::hub.profile.member_since', ['date' => $memberSince]) }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ \Website\Hub\Boot::hubRoute('hub.account.settings') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition-colors text-sm font-medium">
                        <core:icon name="gear" class="mr-2" /> {{ __('hub::hub.profile.actions.settings') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column: Quotas -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Usage Quotas -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                        <core:icon name="gauge-high" class="text-violet-500 mr-2" />{{ __('hub::hub.profile.sections.quotas') }}
                    </h2>
                </header>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($quotas as $key => $quota)
                            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $quota['label'] }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($quota['limit'])
                                            {{ $quota['used'] }} / {{ $quota['limit'] }}
                                        @else
                                            {{ $quota['used'] }} <span class="text-xs text-violet-500">({{ __('hub::hub.profile.quotas.unlimited') }})</span>
                                        @endif
                                    </span>
                                </div>
                                @if($quota['limit'])
                                    @php
                                        $percentage = min(100, ($quota['used'] / $quota['limit']) * 100);
                                        $barColor = $percentage > 90 ? 'bg-red-500' : ($percentage > 70 ? 'bg-amber-500' : 'bg-violet-500');
                                    @endphp
                                    <div class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full h-2 bg-gradient-to-r from-violet-500 to-purple-500 rounded-full"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($userTier !== 'Hades')
                        <div class="mt-4 p-4 bg-gradient-to-r from-violet-500/10 to-purple-500/10 border border-violet-500/20 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ __('hub::hub.profile.quotas.need_more') }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('hub::hub.profile.quotas.need_more_description') }}</p>
                                </div>
                                <a href="{{ route('pricing') }}" class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors text-sm font-medium">
                                    {{ __('hub::hub.profile.actions.upgrade') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Service Status -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                        <core:icon name="cubes" class="text-violet-500 mr-2" />{{ __('hub::hub.profile.sections.services') }}
                    </h2>
                </header>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($serviceStats as $service)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <div class="w-12 h-12 {{ $service['color'] }} rounded-lg flex items-center justify-center text-white">
                                    <core:icon :name="ltrim($service['icon'], 'fa-')" class="text-xl" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $service['name'] }}</span>
                                        @if($service['status'] === 'active')
                                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        @else
                                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $service['stat'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column: Activity -->
        <div class="space-y-6">
            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                        <core:icon name="clock-rotate-left" class="text-violet-500 mr-2" />{{ __('hub::hub.profile.sections.activity') }}
                    </h2>
                </header>
                <div class="p-5">
                    @if(count($recentActivity) > 0)
                        <div class="space-y-4">
                            @foreach($recentActivity as $activity)
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                        <core:icon :name="ltrim($activity['icon'], 'fa-')" class="{{ $activity['color'] }} text-sm" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $activity['message'] }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $activity['time'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('hub::hub.profile.activity.no_activity') }}</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                        <core:icon name="bolt" class="text-violet-500 mr-2" />{{ __('hub::hub.profile.sections.quick_actions') }}
                    </h2>
                </header>
                <div class="p-5 space-y-2">
                    <a href="{{ \Website\Hub\Boot::hubRoute('hub.account.settings') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <core:icon name="user-pen" class="text-gray-400 group-hover:text-violet-500 transition-colors" />
                        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ __('hub::hub.profile.actions.edit_profile') }}</span>
                    </a>
                    <a href="{{ \Website\Hub\Boot::hubRoute('hub.account.settings') }}#password" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <core:icon name="key" class="text-gray-400 group-hover:text-violet-500 transition-colors" />
                        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ __('hub::hub.profile.actions.change_password') }}</span>
                    </a>
                    <a href="{{ \Website\Hub\Boot::hubRoute('hub.account.settings') }}#delete-account" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <core:icon name="file-export" class="text-gray-400 group-hover:text-violet-500 transition-colors" />
                        <span class="text-sm text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ __('hub::hub.profile.actions.export_data') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
