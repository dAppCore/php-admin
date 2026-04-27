<div
        x-data="{
        showCommand: @entangle('showCommand'),
        activeSidebar: @entangle('activeSidebar'),
        init() {
            // Ctrl+Space to open AI command palette
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.code === 'Space') {
                    e.preventDefault();
                    $wire.openCommand();
                }
                // Escape to close
                if (e.key === 'Escape' && this.showCommand) {
                    $wire.closeCommand();
                }
                // Ctrl+S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    $wire.save();
                }
            });

            // Autosave every 60 seconds
            setInterval(() => {
                if ($wire.isDirty) {
                    $wire.autosave();
                }
            }, 60000);
        }
    }"
        class="min-h-screen flex flex-col"
>
    {{-- Header --}}
    <div class="sticky top-0 z-30 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-6 py-3">
            <div class="flex items-center gap-4">
                <a href="{{ \Website\Hub\Boot::hubRoute('hub.content-manager', ['workspace' => $workspaceId ? \Core\Tenant\Models\Workspace::find($workspaceId)?->slug : 'main']) }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <core:icon name="arrow-left" class="w-5 h-5"/>
                </a>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $contentId ? __('hub::hub.content_editor.title.edit') : __('hub::hub.content_editor.title.new') }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if($lastSaved)
                            {{ __('hub::hub.content_editor.save_status.last_saved', ['time' => $lastSaved]) }}
                        @else
                            {{ __('hub::hub.content_editor.save_status.not_saved') }}
                        @endif
                        @if($isDirty)
                            <span class="text-amber-500">• {{ __('hub::hub.content_editor.save_status.unsaved_changes') }}</span>
                        @endif
                        @if($revisionCount > 0)
                            <span class="text-gray-400">• {{ trans_choice('hub::hub.content_editor.save_status.revisions', $revisionCount, ['count' => $revisionCount]) }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- AI Command Button --}}
                <core:button
                        wire:click="openCommand"
                        variant="ghost"
                        size="sm"
                        icon="sparkles"
                        kbd="Ctrl+Space"
                >
                    {{ __('hub::hub.content_editor.actions.ai_assist') }}
                </core:button>

                {{-- Status --}}
                <core:select wire:model.live="status" size="sm" class="w-32">
                    <core:select.option value="draft">{{ __('hub::hub.content_editor.status.draft') }}</core:select.option>
                    <core:select.option value="pending">{{ __('hub::hub.content_editor.status.pending') }}</core:select.option>
                    <core:select.option value="publish">{{ __('hub::hub.content_editor.status.publish') }}</core:select.option>
                    <core:select.option value="future">{{ __('hub::hub.content_editor.status.future') }}</core:select.option>
                    <core:select.option value="private">{{ __('hub::hub.content_editor.status.private') }}</core:select.option>
                </core:select>

                {{-- Save --}}
                <core:button wire:click="save" variant="ghost" size="sm" kbd="Ctrl+S">
                    {{ __('hub::hub.content_editor.actions.save_draft') }}
                </core:button>

                {{-- Schedule/Publish --}}
                @if($isScheduled)
                    <core:button wire:click="schedule" variant="primary" size="sm" icon="calendar">
                        {{ __('hub::hub.content_editor.actions.schedule') }}
                    </core:button>
                @else
                    <core:button wire:click="publish" variant="primary" size="sm">
                        {{ __('hub::hub.content_editor.actions.publish') }}
                    </core:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex-1 flex">
        {{-- Editor Panel --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-4xl mx-auto px-6 py-8">
                <div class="space-y-6">
                    {{-- Title --}}
                    <div>
                        <core:input
                                wire:model.live.debounce.500ms="title"
                                placeholder="{{ __('hub::hub.content_editor.fields.title_placeholder') }}"
                                class="text-3xl font-bold border-none shadow-none focus:ring-0 px-0"
                        />
                    </div>

                    {{-- Slug & Type Row --}}
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <core:input
                                    wire:model="slug"
                                    label="{{ __('hub::hub.content_editor.fields.url_slug') }}"
                                    prefix="/"
                                    size="sm"
                            />
                        </div>
                        <div class="w-32">
                            <core:select wire:model="type" label="{{ __('hub::hub.content_editor.fields.type') }}" size="sm">
                                <core:select.option value="page">{{ __('hub::hub.content_editor.fields.type_page') }}</core:select.option>
                                <core:select.option value="post">{{ __('hub::hub.content_editor.fields.type_post') }}</core:select.option>
                            </core:select>
                        </div>
                    </div>

                    {{-- Excerpt --}}
                    <div>
                        <core:textarea
                                wire:model="excerpt"
                                label="{{ __('hub::hub.content_editor.fields.excerpt') }}"
                                description="{{ __('hub::hub.content_editor.fields.excerpt_description') }}"
                                rows="2"
                        />
                    </div>

                    {{-- Main Editor (AC7 - Rich Text) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('hub::hub.content_editor.fields.content') }}
                        </label>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <core:editor
                                    wire:model="content"
                                    toolbar="heading | bold italic underline strike | bullet ordered blockquote | link image code | align ~ undo redo"
                                    placeholder="{{ __('hub::hub.content_editor.fields.content_placeholder') }}"
                                    class="min-h-[400px]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="w-80 border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 overflow-y-auto">
            {{-- Sidebar Tabs --}}
            <div class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex">
                    <button
                            @click="activeSidebar = 'settings'"
                            :class="activeSidebar === 'settings' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                    >
                        {{ __('hub::hub.content_editor.sidebar.settings') }}
                    </button>
                    <button
                            @click="activeSidebar = 'seo'"
                            :class="activeSidebar === 'seo' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                    >
                        {{ __('hub::hub.content_editor.sidebar.seo') }}
                    </button>
                    <button
                            @click="activeSidebar = 'media'"
                            :class="activeSidebar === 'media' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                    >
                        {{ __('hub::hub.content_editor.sidebar.media') }}
                    </button>
                    <button
                            @click="activeSidebar = 'revisions'; $wire.loadRevisions()"
                            :class="activeSidebar === 'revisions' ? 'border-violet-500 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                    >
                        {{ __('hub::hub.content_editor.sidebar.history') }}
                    </button>
                </div>
            </div>

            <div class="p-4 space-y-6">
                {{-- Settings Panel --}}
                <div x-show="activeSidebar === 'settings'" x-cloak>
                    {{-- Scheduling (AC11) --}}
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.scheduling.title') }}</h3>

                        <core:checkbox
                                wire:model.live="isScheduled"
                                label="{{ __('hub::hub.content_editor.scheduling.schedule_later') }}"
                                description="{{ __('hub::hub.content_editor.scheduling.schedule_description') }}"
                        />

                        @if($isScheduled)
                            <core:input
                                    wire:model="publishAt"
                                    type="datetime-local"
                                    label="{{ __('hub::hub.content_editor.scheduling.publish_date') }}"
                            />
                        @endif
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    {{-- Categories (AC9) --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.categories.title') }}</h3>

                        @if(count($this->categories) > 0)
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach($this->categories as $category)
                                    <core:checkbox
                                            wire:click="toggleCategory({{ $category['id'] }})"
                                            :checked="in_array($category['id'], $selectedCategories)"
                                            :label="$category['name']"
                                    />
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('hub::hub.content_editor.categories.none') }}</p>
                        @endif
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    {{-- Tags (AC9) --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.tags.title') }}</h3>

                        {{-- Selected Tags --}}
                        @if(count($selectedTags) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->tags as $tag)
                                    @if(in_array($tag['id'], $selectedTags))
                                        <core:badge
                                                color="violet"
                                                size="sm"
                                                removable
                                                wire:click="removeTag({{ $tag['id'] }})"
                                        >
                                            {{ $tag['name'] }}
                                        </core:badge>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Add New Tag --}}
                        <div class="flex gap-2">
                            <core:input
                                    wire:model="newTag"
                                    wire:keydown.enter="addTag"
                                    placeholder="{{ __('hub::hub.content_editor.tags.add_placeholder') }}"
                                    size="sm"
                                    class="flex-1"
                            />
                            <core:button wire:click="addTag" size="sm" variant="ghost" icon="plus"/>
                        </div>

                        {{-- Existing Tags to Select --}}
                        @if(count($this->tags) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($this->tags as $tag)
                                    @if(!in_array($tag['id'], $selectedTags))
                                        <button
                                                wire:click="$set('selectedTags', [...$selectedTags, {{ $tag['id'] }}])"
                                                class="text-xs text-gray-500 hover:text-violet-600 hover:bg-violet-50 px-2 py-1 rounded transition"
                                        >
                                            + {{ $tag['name'] }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SEO Panel (AC10) --}}
                <div x-show="activeSidebar === 'seo'" x-cloak class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.seo.title') }}</h3>

                    <core:input
                            wire:model="seoTitle"
                            label="{{ __('hub::hub.content_editor.seo.meta_title') }}"
                            description="{{ __('hub::hub.content_editor.seo.meta_title_description') }}"
                            placeholder="{{ $title ?: __('hub::hub.content_editor.seo.meta_title_placeholder') }}"
                    />
                    <div class="text-xs text-gray-500">
                        {{ __('hub::hub.content_editor.seo.characters', ['count' => strlen($seoTitle), 'max' => 70]) }}
                    </div>

                    <core:textarea
                            wire:model="seoDescription"
                            label="{{ __('hub::hub.content_editor.seo.meta_description') }}"
                            description="{{ __('hub::hub.content_editor.seo.meta_description_description') }}"
                            rows="3"
                            placeholder="{{ __('hub::hub.content_editor.seo.meta_description_placeholder') }}"
                    />
                    <div class="text-xs text-gray-500">
                        {{ __('hub::hub.content_editor.seo.characters', ['count' => strlen($seoDescription), 'max' => 160]) }}
                    </div>

                    <core:input
                            wire:model="seoKeywords"
                            label="{{ __('hub::hub.content_editor.seo.focus_keywords') }}"
                            placeholder="{{ __('hub::hub.content_editor.seo.focus_keywords_placeholder') }}"
                    />

                    {{-- SEO Preview --}}
                    <div class="mt-6 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 mb-2">{{ __('hub::hub.content_editor.seo.preview_title') }}</p>
                        <div class="text-blue-600 text-lg truncate">
                            {{ $seoTitle ?: $title ?: __('hub::hub.content_editor.seo.meta_title_placeholder') }}
                        </div>
                        <div class="text-green-700 text-sm truncate">
                            example.com/{{ $slug ?: 'page-url' }}
                        </div>
                        <div class="text-gray-600 text-sm line-clamp-2">
                            {{ $seoDescription ?: $excerpt ?: __('hub::hub.content_editor.seo.preview_description_fallback') }}
                        </div>
                    </div>
                </div>

                {{-- Media Panel (AC8) --}}
                <div x-show="activeSidebar === 'media'" x-cloak class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.media.featured_image') }}</h3>

                    {{-- Current Featured Image --}}
                    @if($this->featuredMedia)
                        <div class="relative">
                            <img
                                    src="{{ $this->featuredMedia->cdn_url ?? $this->featuredMedia->source_url }}"
                                    alt="{{ $this->featuredMedia->alt_text }}"
                                    class="w-full aspect-video object-cover rounded-lg"
                            >
                            <button
                                    wire:click="removeFeaturedMedia"
                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                            >
                                <core:icon name="x-mark" class="w-4 h-4"/>
                            </button>
                        </div>
                    @else
                        {{-- Upload Zone --}}
                        <div
                                x-data="{ isDragging: false }"
                                @dragover.prevent="isDragging = true"
                                @dragleave="isDragging = false"
                                @drop.prevent="isDragging = false; $wire.uploadFeaturedImage($event.dataTransfer.files[0])"
                                :class="isDragging ? 'border-violet-500 bg-violet-50' : 'border-gray-300'"
                                class="border-2 border-dashed rounded-lg p-6 text-center transition"
                        >
                            <core:icon name="photo" class="w-8 h-8 mx-auto text-gray-400 mb-2"/>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ __('hub::hub.content_editor.media.drag_drop') }}
                            </p>
                            <label class="cursor-pointer">
                                <span class="text-violet-600 hover:text-violet-700 font-medium">{{ __('hub::hub.content_editor.media.browse') }}</span>
                                <input
                                        type="file"
                                        wire:model="featuredImageUpload"
                                        accept="image/*"
                                        class="hidden"
                                >
                            </label>
                        </div>

                        @if($featuredImageUpload)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 flex-1 truncate">
                                    {{ $featuredImageUpload->getClientOriginalName() }}
                                </span>
                                <core:button wire:click="uploadFeaturedImage" size="sm" variant="primary">
                                    {{ __('hub::hub.content_editor.media.upload') }}
                                </core:button>
                            </div>
                        @endif
                    @endif

                    {{-- Media Library --}}
                    @if(count($this->mediaLibrary) > 0)
                        <div class="mt-6">
                            <h4 class="text-xs font-medium text-gray-500 mb-2">{{ __('hub::hub.content_editor.media.select_from_library') }}</h4>
                            <div class="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                                @foreach($this->mediaLibrary as $media)
                                    <button
                                            wire:click="setFeaturedMedia({{ $media['id'] }})"
                                            class="aspect-square rounded overflow-hidden border-2 transition {{ $featuredMediaId === $media['id'] ? 'border-violet-500' : 'border-transparent hover:border-gray-300' }}"
                                    >
                                        <img
                                                src="{{ $media['cdn_url'] ?? $media['source_url'] }}"
                                                alt="{{ $media['alt_text'] ?? '' }}"
                                                class="w-full h-full object-cover"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Revisions Panel (AC12) --}}
                <div x-show="activeSidebar === 'revisions'" x-cloak class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('hub::hub.content_editor.revisions.title') }}</h3>

                    @if($contentId)
                        @if(count($revisions) > 0)
                            <div class="space-y-2">
                                @foreach($revisions as $revision)
                                    <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between mb-1">
                                            <core:badge
                                                    :color="match($revision['change_type']) {
                                                    'publish' => 'green',
                                                    'edit' => 'blue',
                                                    'restore' => 'orange',
                                                    'schedule' => 'violet',
                                                    default => 'zinc'
                                                }"
                                                    size="sm"
                                            >
                                                {{ ucfirst($revision['change_type']) }}
                                            </core:badge>
                                            <span class="text-xs text-gray-500">
                                                #{{ $revision['revision_number'] }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-900 dark:text-white truncate">
                                            {{ $revision['title'] }}
                                        </p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($revision['created_at'])->diffForHumans() }}
                                            </span>
                                            <core:button
                                                    wire:click="restoreRevision({{ $revision['id'] }})"
                                                    size="xs"
                                                    variant="ghost"
                                            >
                                                {{ __('hub::hub.content_editor.revisions.restore') }}
                                            </core:button>
                                        </div>
                                        @if($revision['word_count'])
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ number_format($revision['word_count']) }} words
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('hub::hub.content_editor.revisions.no_revisions') }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">{{ __('hub::hub.content_editor.revisions.save_first') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- AI Command Palette Modal --}}
    <core:modal wire:model.self="showCommand" variant="bare" class="w-full max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
            {{-- Search Input --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <core:command>
                    <core:command.input
                            wire:model.live.debounce.300ms="commandSearch"
                            placeholder="{{ __('hub::hub.content_editor.ai.command_placeholder') }}"
                            autofocus
                    />
                </core:command>
            </div>

            {{-- Quick Actions --}}
            @if(empty($commandSearch) && !$selectedPromptId)
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                        {{ __('hub::hub.content_editor.ai.quick_actions') }}
                    </h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($this->quickActions as $action)
                            <button
                                    wire:click="executeQuickAction('{{ $action['prompt'] }}', {{ json_encode($action['variables']) }})"
                                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-left transition"
                            >
                                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400">
                                    <core:icon :name="$action['icon']" class="w-4 h-4"/>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $action['name'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $action['description'] }}
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Prompt List --}}
            @if(!$selectedPromptId)
                <div class="max-h-80 overflow-y-auto">
                    @foreach($this->prompts as $category => $categoryPrompts)
                        <div class="p-2">
                            <h3 class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ ucfirst($category) }}
                            </h3>
                            @foreach($categoryPrompts as $prompt)
                                <core:command.item
                                        wire:click="selectPrompt({{ $prompt['id'] }})"
                                        icon="sparkles"
                                >
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $prompt['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $prompt['description'] }}</div>
                                    </div>
                                    <core:badge size="sm"
                                                color="{{ $prompt['model'] === 'claude' ? 'orange' : 'blue' }}">
                                        {{ $prompt['model'] }}
                                    </core:badge>
                                </core:command.item>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Prompt Variables Form --}}
            @if($selectedPromptId)
                @php $selectedPrompt = \App\Models\Prompt::find($selectedPromptId); @endphp
                <div class="p-4 space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <button wire:click="$set('selectedPromptId', null)" class="text-gray-400 hover:text-gray-600">
                            <core:icon name="arrow-left" class="w-5 h-5"/>
                        </button>
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedPrompt->name }}
                            </h3>
                            <p class="text-sm text-gray-500">{{ $selectedPrompt->description }}</p>
                        </div>
                    </div>

                    @if($selectedPrompt->variables)
                        @foreach($selectedPrompt->variables as $name => $config)
                            @if($name !== 'content')
                                <div>
                                    @if(($config['type'] ?? 'string') === 'string')
                                        <core:input
                                                wire:model="promptVariables.{{ $name }}"
                                                label="{{ ucfirst(str_replace('_', ' ', $name)) }}"
                                                description="{{ $config['description'] ?? '' }}"
                                        />
                                    @elseif(($config['type'] ?? 'string') === 'boolean')
                                        <core:checkbox
                                                wire:model="promptVariables.{{ $name }}"
                                                label="{{ ucfirst(str_replace('_', ' ', $name)) }}"
                                                description="{{ $config['description'] ?? '' }}"
                                        />
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <div class="flex justify-end gap-2 pt-4">
                        <core:button wire:click="closeCommand" variant="ghost">
                            {{ __('hub::hub.content_editor.ai.cancel') }}
                        </core:button>
                        <core:button
                                wire:click="executePrompt"
                                variant="primary"
                                wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="executePrompt">{{ __('hub::hub.content_editor.ai.run') }}</span>
                            <span wire:loading wire:target="executePrompt">{{ __('hub::hub.content_editor.ai.processing') }}</span>
                        </core:button>
                    </div>
                </div>
            @endif

            {{-- AI Result --}}
            @if($aiResult)
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('hub::hub.content_editor.ai.result_title') }}
                    </h3>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg max-h-60 overflow-y-auto">
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! nl2br(e($aiResult)) !!}
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <core:button wire:click="$set('aiResult', null)" variant="ghost" size="sm">
                            {{ __('hub::hub.content_editor.ai.discard') }}
                        </core:button>
                        <core:button wire:click="insertAiResult" variant="ghost" size="sm">
                            {{ __('hub::hub.content_editor.ai.insert') }}
                        </core:button>
                        <core:button wire:click="applyAiResult" variant="primary" size="sm">
                            {{ __('hub::hub.content_editor.ai.replace_content') }}
                        </core:button>
                    </div>
                </div>
            @endif

            {{-- Processing Indicator --}}
            @if($aiProcessing)
                <div class="p-8 text-center">
                    <div class="inline-flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-300">{{ __('hub::hub.content_editor.ai.thinking') }}</span>
                    </div>
                </div>
            @endif

            {{-- Footer --}}
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center justify-between">
                    <span>{!! __('hub::hub.content_editor.ai.footer_close', ['key' => '<kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">Esc</kbd>']) !!}</span>
                    <span>{{ __('hub::hub.content_editor.ai.footer_powered') }}</span>
                </div>
            </div>
        </div>
    </core:modal>
</div>
