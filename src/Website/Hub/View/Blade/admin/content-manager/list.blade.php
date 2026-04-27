<!-- Filters Bar -->
<core:card class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <core:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('hub::hub.content_manager.list.search_placeholder') }}"
                icon="magnifying-glass"
                class="w-64"
            />

            <!-- Type Filter -->
            <core:select wire:model.live="type" placeholder="{{ __('hub::hub.content_manager.list.filters.all_types') }}" class="w-32">
                <core:select.option value="">{{ __('hub::hub.content_manager.list.filters.all_types') }}</core:select.option>
                <core:select.option value="post">{{ __('hub::hub.content_manager.list.filters.posts') }}</core:select.option>
                <core:select.option value="page">{{ __('hub::hub.content_manager.list.filters.pages') }}</core:select.option>
            </core:select>

            <!-- Status Filter -->
            <core:select wire:model.live="status" placeholder="{{ __('hub::hub.content_manager.list.filters.all_status') }}" class="w-36">
                <core:select.option value="">{{ __('hub::hub.content_manager.list.filters.all_status') }}</core:select.option>
                <core:select.option value="publish">{{ __('hub::hub.content_manager.list.filters.published') }}</core:select.option>
                <core:select.option value="draft">{{ __('hub::hub.content_manager.list.filters.draft') }}</core:select.option>
                <core:select.option value="pending">{{ __('hub::hub.content_manager.list.filters.pending') }}</core:select.option>
                <core:select.option value="future">{{ __('hub::hub.content_manager.list.filters.scheduled') }}</core:select.option>
                <core:select.option value="private">{{ __('hub::hub.content_manager.list.filters.private') }}</core:select.option>
            </core:select>

            <!-- Sync Status Filter -->
            <core:select wire:model.live="syncStatus" placeholder="{{ __('hub::hub.content_manager.list.filters.all_sync') }}" class="w-36">
                <core:select.option value="">{{ __('hub::hub.content_manager.list.filters.all_sync') }}</core:select.option>
                <core:select.option value="synced">{{ __('hub::hub.content_manager.list.filters.synced') }}</core:select.option>
                <core:select.option value="pending">{{ __('hub::hub.content_manager.list.filters.pending') }}</core:select.option>
                <core:select.option value="stale">{{ __('hub::hub.content_manager.list.filters.stale') }}</core:select.option>
                <core:select.option value="failed">{{ __('hub::hub.content_manager.list.filters.failed') }}</core:select.option>
            </core:select>

            <!-- Content Type Filter -->
            <core:select wire:model.live="contentType" placeholder="{{ __('hub::hub.content_manager.list.filters.all_sources') }}" class="w-36">
                <core:select.option value="">{{ __('hub::hub.content_manager.list.filters.all_sources') }}</core:select.option>
                <core:select.option value="native">{{ __('hub::hub.content_manager.list.filters.native') }}</core:select.option>
                <core:select.option value="hostuk">{{ __('hub::hub.content_manager.list.filters.host_uk') }}</core:select.option>
                <core:select.option value="satellite">{{ __('hub::hub.content_manager.list.filters.satellite') }}</core:select.option>
                @if(config('services.content.wordpress_enabled'))
                    <core:select.option value="wordpress">{{ __('hub::hub.content_manager.list.filters.wordpress_legacy') }}</core:select.option>
                @endif
            </core:select>

            <!-- Category Filter -->
            @if(count($this->categories) > 0)
                <core:select wire:model.live="category" placeholder="{{ __('hub::hub.content_manager.list.filters.all_categories') }}" class="w-40">
                    <core:select.option value="">{{ __('hub::hub.content_manager.list.filters.all_categories') }}</core:select.option>
                    @foreach($this->categories as $slug => $name)
                        <core:select.option value="{{ $slug }}">{{ $name }}</core:select.option>
                    @endforeach
                </core:select>
            @endif

            <!-- Clear Filters -->
            @if($search || $type || $status || $syncStatus || $category || $contentType)
                <core:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    {{ __('hub::hub.content_manager.list.filters.clear') }}
                </core:button>
            @endif
        </div>
    </div>
</core:card>

<!-- Content Table -->
<core:card>
    <core:table :paginate="$this->content">
        <core:table.columns>
            <core:table.column
                :sortable="true"
                :sorted="$sort === 'title'"
                :direction="$sort === 'title' ? $dir : null"
                wire:click="setSort('title')"
            >
                {{ __('hub::hub.content_manager.list.columns.title') }}
            </core:table.column>
            <core:table.column class="hidden md:table-cell">{{ __('hub::hub.content_manager.list.columns.type') }}</core:table.column>
            <core:table.column class="hidden md:table-cell">{{ __('hub::hub.content_manager.list.columns.status') }}</core:table.column>
            <core:table.column class="hidden lg:table-cell">{{ __('hub::hub.content_manager.list.columns.sync') }}</core:table.column>
            <core:table.column class="hidden lg:table-cell">{{ __('hub::hub.content_manager.list.columns.categories') }}</core:table.column>
            <core:table.column
                class="hidden xl:table-cell"
                :sortable="true"
                :sorted="$sort === 'wp_created_at'"
                :direction="$sort === 'wp_created_at' ? $dir : null"
                wire:click="setSort('wp_created_at')"
            >
                {{ __('hub::hub.content_manager.list.columns.created') }}
            </core:table.column>
            <core:table.column
                class="hidden xl:table-cell"
                :sortable="true"
                :sorted="$sort === 'synced_at'"
                :direction="$sort === 'synced_at' ? $dir : null"
                wire:click="setSort('synced_at')"
            >
                {{ __('hub::hub.content_manager.list.columns.last_synced') }}
            </core:table.column>
            <core:table.column align="end"></core:table.column>
        </core:table.columns>

        <core:table.rows>
            @forelse($this->content as $item)
                <core:table.row :key="$item->id">
                    <core:table.cell variant="strong">
                        <div class="min-w-0">
                            <button wire:click="selectItem({{ $item->id }})" class="font-medium hover:text-violet-600 dark:hover:text-violet-400 truncate text-left">
                                {{ $item->title }}
                            </button>
                            <core:text size="xs" class="truncate">{{ $item->slug }}</core:text>
                        </div>
                    </core:table.cell>

                    <core:table.cell class="hidden md:table-cell">
                        <x-content.type-badge :type="$item->type" />
                    </core:table.cell>

                    <core:table.cell class="hidden md:table-cell">
                        <x-content.status-badge :status="$item->status" />
                    </core:table.cell>

                    <core:table.cell class="hidden lg:table-cell">
                        <x-content.sync-badge :status="$item->sync_status" />
                    </core:table.cell>

                    <core:table.cell class="hidden lg:table-cell">
                        <div class="flex flex-wrap gap-1">
                            @foreach($item->categories->take(2) as $category)
                                <core:badge color="violet" size="sm">{{ $category->name }}</core:badge>
                            @endforeach
                            @if($item->categories->count() > 2)
                                <core:badge color="zinc" size="sm">+{{ $item->categories->count() - 2 }}</core:badge>
                            @endif
                        </div>
                    </core:table.cell>

                    <core:table.cell class="hidden xl:table-cell">
                        <core:text size="sm">{{ $item->wp_created_at?->format('M j, Y') ?? '-' }}</core:text>
                    </core:table.cell>

                    <core:table.cell class="hidden xl:table-cell">
                        <core:text size="sm">{{ $item->synced_at?->diffForHumans() ?? __('hub::hub.content_manager.list.never') }}</core:text>
                    </core:table.cell>

                    <core:table.cell align="end">
                        <div class="flex items-center gap-1">
                            @if($item->usesFluxEditor())
                                <core:button href="{{ \Website\Hub\Boot::hubRoute('hub.content-editor.edit', ['workspace' => $workspaceSlug, 'id' => $item->id]) }}" variant="ghost" size="sm" icon="pencil" title="{{ __('hub::hub.content_manager.list.edit') }}" />
                            @endif
                            <core:button wire:click="selectItem({{ $item->id }})" variant="ghost" size="sm" icon="eye" title="{{ __('hub::hub.content_manager.list.preview') }}" />
                        </div>
                    </core:table.cell>
                </core:table.row>
            @empty
                <core:table.row>
                    <core:table.cell colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <core:icon name="inbox" class="size-12 text-zinc-300 dark:text-zinc-600 mb-3" />
                            <core:text>{{ __('hub::hub.content_manager.list.no_content') }}</core:text>
                            @if($search || $type || $status || $syncStatus || $category)
                                <core:button wire:click="clearFilters" variant="ghost" size="sm" class="mt-2">
                                    {{ __('hub::hub.content_manager.list.filters.clear_filters') }}
                                </core:button>
                            @endif
                        </div>
                    </core:table.cell>
                </core:table.row>
            @endforelse
        </core:table.rows>
    </core:table>
</core:card>
