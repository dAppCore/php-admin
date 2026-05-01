<div>
    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ __('hub::hub.content.title') }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $currentWorkspace['color'] ?? 'violet' }}-500/20 text-{{ $currentWorkspace['color'] ?? 'violet' }}-600 dark:text-{{ $currentWorkspace['color'] ?? 'violet' }}-400">
                    <core:icon :name="$currentWorkspace['icon'] ?? 'globe'" class="mr-1.5" />
                    {{ $currentWorkspace['name'] ?? 'Hestia Main' }}
                </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">{{ __('hub::hub.content.subtitle') }}</p>
        </div>
        @if($tab !== 'media')
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <button wire:click="createNew" class="btn bg-violet-500 text-white hover:bg-violet-600">
                    <core:icon name="plus" class="mr-2" />
                    <span>{{ $tab === 'posts' ? __('hub::hub.content.new_post') : __('hub::hub.content.new_page') }}</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-x-4" aria-label="Tabs">
                <a href="{{ \Website\Hub\Boot::hubRoute('hub.content', ['workspace' => $currentWorkspace['slug'] ?? 'main', 'type' => 'posts']) }}"
                   class="px-3 py-2.5 text-sm font-medium border-b-2 {{ $tab === 'posts' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <core:icon name="newspaper" class="mr-2" />{{ __('hub::hub.content.tabs.posts') }}
                </a>
                <a href="{{ \Website\Hub\Boot::hubRoute('hub.content', ['workspace' => $currentWorkspace['slug'] ?? 'main', 'type' => 'pages']) }}"
                   class="px-3 py-2.5 text-sm font-medium border-b-2 {{ $tab === 'pages' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <core:icon name="file-lines" class="mr-2" />{{ __('hub::hub.content.tabs.pages') }}
                </a>
                <a href="{{ \Website\Hub\Boot::hubRoute('hub.content', ['workspace' => $currentWorkspace['slug'] ?? 'main', 'type' => 'media']) }}"
                   class="px-3 py-2.5 text-sm font-medium border-b-2 {{ $tab === 'media' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    <core:icon name="images" class="mr-2" />{{ __('hub::hub.content.tabs.media') }}
                </a>
            </nav>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ($this->stats as $stat)
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-4">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $stat['title'] }}</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stat['value'] }}</div>
                <div class="flex items-center gap-1 text-xs font-medium mt-1 {{ $stat['trendUp'] ? 'text-green-500' : 'text-red-500' }}">
                    <core:icon :name="$stat['trendUp'] ? 'arrow-trend-up' : 'arrow-trend-down'" />
                    {{ $stat['trend'] }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 p-4">
            <div class="flex items-center gap-3">
                <!-- Status Filter -->
                <select wire:model.live="status" class="form-select text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <option value="">{{ __('hub::hub.content.filters.all_status') }}</option>
                    <option value="publish">{{ __('hub::hub.content.filters.published') }}</option>
                    <option value="draft">{{ __('hub::hub.content.filters.draft') }}</option>
                    <option value="pending">{{ __('hub::hub.content.filters.pending') }}</option>
                    <option value="private">{{ __('hub::hub.content.filters.private') }}</option>
                </select>

                <!-- Sort Pills -->
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('hub::hub.content.filters.sort') }}:</span>
                    <button wire:click="setSort('date')" class="px-3 py-1 text-sm rounded-full transition {{ $sort === 'date' ? 'bg-violet-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ __('hub::hub.content.filters.date') }} @if($sort === 'date')<core:icon :name="$dir === 'desc' ? 'chevron-down' : 'chevron-up'" class="ml-1 text-xs" />@endif
                    </button>
                    <button wire:click="setSort('title')" class="px-3 py-1 text-sm rounded-full transition {{ $sort === 'title' ? 'bg-violet-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ __('hub::hub.content.filters.title') }} @if($sort === 'title')<core:icon :name="$dir === 'desc' ? 'chevron-down' : 'chevron-up'" class="ml-1 text-xs" />@endif
                    </button>
                    <button wire:click="setSort('status')" class="px-3 py-1 text-sm rounded-full transition {{ $sort === 'status' ? 'bg-violet-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ __('hub::hub.content.filters.status') }} @if($sort === 'status')<core:icon :name="$dir === 'desc' ? 'chevron-down' : 'chevron-up'" class="ml-1 text-xs" />@endif
                    </button>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button wire:click="setView('list')" class="p-2 rounded {{ $view === 'list' ? 'bg-white dark:bg-gray-600 shadow-sm' : '' }}">
                    <core:icon name="list" class="text-gray-600 dark:text-gray-300" />
                </button>
                <button wire:click="setView('grid')" class="p-2 rounded {{ $view === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm' : '' }}">
                    <core:icon name="grid-2" class="text-gray-600 dark:text-gray-300" />
                </button>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    @if($tab === 'media' && $view === 'grid')
        <!-- Media Grid View -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @forelse($this->rows as $item)
                <div class="group relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden cursor-pointer">
                    @if(($item['media_type'] ?? 'image') === 'image')
                        <img src="{{ $item['source_url'] ?? '/images/placeholder.svg' }}" alt="{{ $item['title']['rendered'] ?? '' }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <core:icon name="file" class="text-3xl text-gray-400" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <span class="text-white text-xs px-2 py-1 bg-black/50 rounded truncate max-w-full">{{ $item['title']['rendered'] ?? __('hub::hub.content.untitled') }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-500 dark:text-gray-400">
                    <core:icon name="image" class="text-4xl mb-3 opacity-50" />
                    <p>{{ __('hub::hub.content.no_media') }}</p>
                </div>
            @endforelse
        </div>
    @else
        <!-- Table View -->
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-gray-300">
                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" class="form-checkbox rounded text-violet-500">
                            </th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">{{ __('hub::hub.content.columns.id') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('hub::hub.content.columns.title') }}</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">{{ __('hub::hub.content.columns.status') }}</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">{{ __('hub::hub.content.columns.date') }}</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">{{ __('hub::hub.content.columns.modified') }}</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($this->rows as $row)
                            <tr wire:key="row-{{ $row['id'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/25">
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="form-checkbox rounded text-violet-500">
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="text-gray-500">#{{ $row['id'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($tab === 'media')
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                                @if(($row['media_type'] ?? 'image') === 'image')
                                                    <img src="{{ $row['source_url'] ?? '/images/placeholder.svg' }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <core:icon name="file" class="text-gray-400" />
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-800 dark:text-gray-100 truncate">{{ $row['title']['rendered'] ?? __('hub::hub.content.untitled') }}</div>
                                            @if($tab !== 'media' && !empty($row['excerpt']['rendered']))
                                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit(strip_tags($row['excerpt']['rendered']), 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @php
                                        $status = $row['status'] ?? 'draft';
                                        $statusColors = [
                                            'publish' => 'green',
                                            'draft' => 'yellow',
                                            'pending' => 'blue',
                                            'private' => 'gray',
                                        ];
                                        $color = $statusColors[$status] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-800 dark:text-{{ $color }}-400">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <span class="text-gray-500 text-sm">
                                        {{ isset($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('M j, Y') : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <span class="text-gray-500 text-sm">
                                        {{ isset($row['modified']) ? \Carbon\Carbon::parse($row['modified'])->diffForHumans() : '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <core:icon name="ellipsis" class="text-gray-500" />
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-10">
                                            @if($tab !== 'media')
                                                <button wire:click="edit({{ $row['id'] }})" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <core:icon name="pen-to-square" class="mr-2 text-gray-400" />{{ __('hub::hub.content.actions.edit') }}
                                                </button>
                                            @endif
                                            <button disabled title="Preview coming soon" class="w-full text-left px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                <core:icon name="eye" class="mr-2" />{{ __('hub::hub.content.actions.view') }}
                                            </button>
                                            <button disabled title="Duplicate coming soon" class="w-full text-left px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                <core:icon name="copy" class="mr-2" />{{ __('hub::hub.content.actions.duplicate') }}
                                            </button>
                                            <hr class="my-1 border-gray-100 dark:border-gray-700">
                                            <button wire:click="delete({{ $row['id'] }})" wire:confirm="{{ __('hub::hub.content.actions.delete_confirm') }}" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                                                <core:icon name="trash" class="mr-2" />{{ __('hub::hub.content.actions.delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="text-gray-500">
                                        <core:icon :name="$tab === 'posts' ? 'newspaper' : ($tab === 'pages' ? 'file-lines' : 'image')" class="text-4xl mb-3 opacity-50" />
                                        <p>{{ $tab === 'posts' ? __('hub::hub.content.no_posts') : ($tab === 'pages' ? __('hub::hub.content.no_pages') : __('hub::hub.content.no_media')) }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Pagination -->
    @if($this->paginator->hasPages())
        <div class="mt-6">
            {{ $this->paginator->links() }}
        </div>
    @endif

    <!-- Editor Modal -->
    @if($showEditor)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Backdrop -->
                <div wire:click="closeEditor" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>

                <!-- Modal Panel -->
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            {{ $isCreating ? __('hub::hub.content.editor.new') : __('hub::hub.content.editor.edit') }} {{ $tab === 'posts' ? __('hub::hub.content.tabs.posts') : __('hub::hub.content.tabs.pages') }}
                        </h3>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('hub::hub.content.editor.title_label') }}</label>
                            <input type="text" wire:model="editTitle" class="form-input w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="{{ __('hub::hub.content.editor.title_placeholder') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('hub::hub.content.editor.status_label') }}</label>
                            <select wire:model="editStatus" class="form-select w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option value="draft">{{ __('hub::hub.content.editor.status.draft') }}</option>
                                <option value="publish">{{ __('hub::hub.content.editor.status.publish') }}</option>
                                <option value="pending">{{ __('hub::hub.content.editor.status.pending') }}</option>
                                <option value="private">{{ __('hub::hub.content.editor.status.private') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('hub::hub.content.editor.excerpt_label') }}</label>
                            <textarea wire:model="editExcerpt" rows="2" class="form-textarea w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="{{ __('hub::hub.content.editor.excerpt_placeholder') }}"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('hub::hub.content.editor.content_label') }}</label>
                            <textarea wire:model="editContent" rows="10" class="form-textarea w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 font-mono text-sm" placeholder="{{ __('hub::hub.content.editor.content_placeholder') }}"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button wire:click="closeEditor" class="btn border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-600 dark:text-gray-300">
                            {{ __('hub::hub.content.editor.cancel') }}
                        </button>
                        <button wire:click="save" class="btn bg-violet-500 text-white hover:bg-violet-600">
                            <core:icon name="check" class="mr-2" />
                            {{ $isCreating ? __('hub::hub.content.editor.create') : __('hub::hub.content.editor.update') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>