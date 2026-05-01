<div>
    <!-- Page header -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ __('hub::hub.boosts.title') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.boosts.subtitle') }}</p>
    </div>

    <div class="space-y-6">
        @if(count($boostOptions) > 0)
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($boostOptions as $boost)
                    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $boost['feature_name'] }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $boost['description'] }}
                                </p>
                            </div>
                            @switch($boost['boost_type'])
                                @case('add_limit')
                                    <core:badge color="blue">+{{ number_format($boost['limit_value']) }}</core:badge>
                                    @break
                                @case('unlimited')
                                    <core:badge color="purple">{{ __('hub::hub.boosts.types.unlimited') }}</core:badge>
                                    @break
                                @case('enable')
                                    <core:badge color="green">{{ __('hub::hub.boosts.types.enable') }}</core:badge>
                                    @break
                            @endswitch
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                @switch($boost['duration_type'])
                                    @case('cycle_bound')
                                        <core:icon name="clock" class="size-4 mr-1" />
                                        {{ __('hub::hub.boosts.duration.cycle_bound') }}
                                        @break
                                    @case('duration')
                                        <core:icon name="calendar" class="size-4 mr-1" />
                                        {{ __('hub::hub.boosts.duration.limited') }}
                                        @break
                                    @case('permanent')
                                        <core:icon name="infinity" class="size-4 mr-1" />
                                        {{ __('hub::hub.boosts.duration.permanent') }}
                                        @break
                                @endswitch
                            </div>
                            <core:button wire:click="purchaseBoost('{{ $boost['blesta_id'] }}')" size="sm" variant="primary">
                                {{ __('hub::hub.boosts.actions.purchase') }}
                            </core:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <core:icon name="rocket" class="size-8 mx-auto mb-2 opacity-50" />
                    <p>{{ __('hub::hub.boosts.empty.title') }}</p>
                    <p class="text-sm mt-1">{{ __('hub::hub.boosts.empty.hint') }}</p>
                </div>
            </div>
        @endif

        <!-- Info Section -->
        <div class="bg-blue-500/10 dark:bg-blue-500/20 rounded-xl p-6">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">
                <core:icon name="circle-info" class="size-5 mr-2" />
                {{ __('hub::hub.boosts.info.title') }}
            </h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2 ml-7">
                <li><strong>{{ __('hub::hub.boosts.labels.cycle_bound') }}</strong> {{ __('hub::hub.boosts.info.cycle_bound') }}</li>
                <li><strong>{{ __('hub::hub.boosts.labels.duration_based') }}</strong> {{ __('hub::hub.boosts.info.duration_based') }}</li>
                <li><strong>{{ __('hub::hub.boosts.labels.permanent') }}</strong> {{ __('hub::hub.boosts.info.permanent') }}</li>
            </ul>
        </div>

        <!-- Back Link -->
        <div class="flex justify-start">
            <core:button href="{{ \Website\Hub\Boot::hubRoute('hub.usage') }}" variant="ghost">
                <core:icon name="arrow-left" class="mr-2" />
                {{ __('hub::hub.boosts.actions.back') }}
            </core:button>
        </div>
    </div>
</div>
