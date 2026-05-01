<div>
    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center gap-3">
                <core:heading size="xl">{{ __('hub::hub.content_manager.title') }}</core:heading>
                @if($currentWorkspace)
                    <core:badge color="violet" icon="server">
                        {{ $currentWorkspace->name }}
                    </core:badge>
                @endif
            </div>
            <core:subheading>{{ __('hub::hub.content_manager.subtitle') }}</core:subheading>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            @if($syncMessage)
                <core:text size="sm" class="{{ str_contains($syncMessage, 'failed') ? 'text-red-500' : 'text-green-500' }}">
                    {{ $syncMessage }}
                </core:text>
            @endif
            <core:button href="{{ \Website\Hub\Boot::hubRoute('hub.content-editor.create', ['workspace' => $workspaceSlug, 'contentType' => 'hostuk']) }}" variant="primary" icon="plus">
                {{ __('hub::hub.content_manager.actions.new_content') }}
            </core:button>
            <core:button wire:click="syncAll" wire:loading.attr="disabled" icon="arrow-path" :loading="$syncing">
                {{ __('hub::hub.content_manager.actions.sync_all') }}
            </core:button>
            <core:button wire:click="purgeCache" variant="ghost" icon="trash">
                {{ __('hub::hub.content_manager.actions.purge_cdn') }}
            </core:button>
        </div>
    </div>

    <!-- View Tabs -->
    <admin:tabs :tabs="$this->tabs" :selected="$view" />

    <!-- Tab Content -->
    @if($view === 'dashboard')
        @include('hub::admin.content-manager.dashboard')
    @elseif($view === 'kanban')
        @include('hub::admin.content-manager.kanban')
    @elseif($view === 'calendar')
        @include('hub::admin.content-manager.calendar')
    @elseif($view === 'list')
        @include('hub::admin.content-manager.list')
    @elseif($view === 'webhooks')
        @include('hub::admin.content-manager.webhooks')
    @endif

    <!-- Command Palette (Cmd+K) -->
    <core:command class="hidden">
        <core:command.input placeholder="{{ __('hub::hub.content_manager.command.placeholder') }}" />
        <core:command.items>
            <core:command.item icon="arrow-path" wire:click="syncAll">{{ __('hub::hub.content_manager.command.sync_all') }}</core:command.item>
            <core:command.item icon="trash" wire:click="purgeCache">{{ __('hub::hub.content_manager.command.purge_cache') }}</core:command.item>
            <core:command.item icon="arrow-top-right-on-square" href="{{ \Website\Hub\Boot::hubRoute('hub.content', ['workspace' => $workspaceSlug, 'type' => 'posts']) }}">{{ __('hub::hub.content_manager.command.open_wordpress') }}</core:command.item>
        </core:command.items>
        <core:command.empty>{{ __('hub::hub.content_manager.command.no_results') }}</core:command.empty>
    </core:command>

    <!-- Preview Slide-over -->
    <core:modal name="content-preview" variant="flyout" class="max-w-2xl">
        @if($this->selectedItem)
            <!-- Header -->
            <div class="mb-6">
                <core:heading size="lg">{{ $this->selectedItem->title }}</core:heading>
            </div>

            <!-- Body -->
            <div class="space-y-6">
                <!-- Meta Badges -->
                <div class="flex flex-wrap gap-2">
                    <x-content.status-badge :status="$this->selectedItem->status" />
                    <x-content.type-badge :type="$this->selectedItem->type" />
                    <x-content.sync-badge :status="$this->selectedItem->sync_status">
                        {{ __('hub::hub.content_manager.preview.sync_label') }}: {{ ucfirst($this->selectedItem->sync_status) }}
                    </x-content.sync-badge>
                </div>

                <!-- Author -->
                @if($this->selectedItem->author)
                    <div class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        @if($this->selectedItem->author->avatar_url)
                            <core:avatar src="{{ $this->selectedItem->author->avatar_url }}" />
                        @else
                            <core:avatar>{{ substr($this->selectedItem->author->name, 0, 1) }}</core:avatar>
                        @endif
                        <div>
                            <core:heading size="sm">{{ $this->selectedItem->author->name }}</core:heading>
                            <core:subheading size="xs">{{ __('hub::hub.content_manager.preview.author') }}</core:subheading>
                        </div>
                    </div>
                @endif

                <!-- Excerpt -->
                @if($this->selectedItem->excerpt)
                    <div>
                        <core:label>{{ __('hub::hub.content_manager.preview.excerpt') }}</core:label>
                        <core:text class="mt-1">{{ $this->selectedItem->excerpt }}</core:text>
                    </div>
                @endif

                <!-- Content Preview -->
                <div>
                    <core:label>{{ __('hub::hub.content_manager.preview.content_clean_html') }}</core:label>
                    <div class="mt-2 prose dark:prose-invert prose-sm max-w-none p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg max-h-96 overflow-y-auto">
                        {!! $this->selectedItem->content_html_clean ?: $this->selectedItem->content_html_original !!}
                    </div>
                </div>

                <!-- Categories & Tags -->
                @if($this->selectedItem->categories->isNotEmpty() || $this->selectedItem->tags->isNotEmpty())
                    <div>
                        <core:label>{{ __('hub::hub.content_manager.preview.taxonomies') }}</core:label>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($this->selectedItem->categories as $category)
                                <core:badge color="violet">{{ $category->name }}</core:badge>
                            @endforeach
                            @foreach($this->selectedItem->tags as $tag)
                                <core:badge color="zinc">#{{ $tag->name }}</core:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Structured JSON -->
                @if($this->selectedItem->content_json)
                    <div>
                        <core:label>{{ __('hub::hub.content_manager.preview.structured_content') }}</core:label>
                        <div class="mt-2 text-xs font-mono p-4 bg-zinc-900 text-zinc-100 rounded-lg max-h-64 overflow-y-auto">
                            <pre>{{ json_encode($this->selectedItem->content_json, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif

                <!-- Timestamps -->
                <core:separator />

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <core:text class="text-zinc-500">{{ __('hub::hub.content_manager.preview.created') }}:</core:text>
                        <core:text>{{ $this->selectedItem->wp_created_at?->format('M j, Y H:i') ?? '-' }}</core:text>
                    </div>
                    <div>
                        <core:text class="text-zinc-500">{{ __('hub::hub.content_manager.preview.modified') }}:</core:text>
                        <core:text>{{ $this->selectedItem->wp_modified_at?->format('M j, Y H:i') ?? '-' }}</core:text>
                    </div>
                    <div>
                        <core:text class="text-zinc-500">{{ __('hub::hub.content_manager.preview.last_synced') }}:</core:text>
                        <core:text>{{ $this->selectedItem->synced_at?->diffForHumans() ?? __('hub::hub.content_manager.preview.never') }}</core:text>
                    </div>
                    <div>
                        <core:text class="text-zinc-500">{{ __('hub::hub.content_manager.preview.wordpress_id') }}:</core:text>
                        <core:text>#{{ $this->selectedItem->wp_id }}</core:text>
                    </div>
                </div>
            </div>
        @endif
    </core:modal>
</div>
