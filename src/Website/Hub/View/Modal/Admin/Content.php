<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Tenant\Services\WorkspaceService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Content management component.
 *
 * Native content system - no longer uses WordPress.
 */
class Content extends Component
{
    use WithPagination;

    public string $tab = 'posts';

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'date';

    #[Url]
    public string $dir = 'desc';

    public string $view = 'list';

    public ?int $editingId = null;

    public string $editTitle = '';

    public string $editContent = '';

    public string $editStatus = 'draft';

    public string $editExcerpt = '';

    public bool $showEditor = false;

    public bool $isCreating = false;

    public array $items = [];

    public int $total = 0;

    public int $perPage = 15;

    public array $currentWorkspace = [];

    protected WorkspaceService $workspaceService;

    public function boot(WorkspaceService $workspaceService): void
    {
        $this->workspaceService = $workspaceService;
    }

    public function mount(string $workspace = 'main', string $type = 'posts'): void
    {
        $this->tab = $type;

        // Set workspace from URL
        $this->workspaceService->setCurrent($workspace);
        $this->currentWorkspace = $this->workspaceService->current();

        $this->loadContent();
    }

    #[On('workspace-changed')]
    public function handleWorkspaceChange(string $workspace): void
    {
        $this->currentWorkspace = $this->workspaceService->current();
        $this->resetPage();
        $this->loadContent();
    }

    #[Computed]
    public function stats(): array
    {
        $published = collect($this->items)->where('status', 'publish')->count();
        $drafts = collect($this->items)->where('status', 'draft')->count();

        return [
            [
                'title' => 'Total '.ucfirst($this->tab),
                'value' => (string) $this->total,
                'trend' => '+12%',
                'trendUp' => true,
                'icon' => $this->tab === 'posts' ? 'newspaper' : ($this->tab === 'pages' ? 'file-lines' : 'images'),
            ],
            [
                'title' => 'Published',
                'value' => (string) $published,
                'trend' => '+8%',
                'trendUp' => true,
                'icon' => 'check-circle',
            ],
            [
                'title' => 'Drafts',
                'value' => (string) $drafts,
                'trend' => '-3%',
                'trendUp' => false,
                'icon' => 'pencil',
            ],
            [
                'title' => 'This Week',
                'value' => (string) collect($this->items)->filter(fn ($i) => \Carbon\Carbon::parse($i['date'] ?? $i['modified'] ?? now())->isCurrentWeek())->count(),
                'trend' => '+24%',
                'trendUp' => true,
                'icon' => 'calendar',
            ],
        ];
    }

    #[Computed]
    public function paginator(): LengthAwarePaginator
    {
        $page = $this->getPage();

        return new LengthAwarePaginator(
            items: array_slice($this->items, ($page - 1) * $this->perPage, $this->perPage),
            total: $this->total,
            perPage: $this->perPage,
            currentPage: $page,
            options: ['path' => request()->url()]
        );
    }

    #[Computed]
    public function rows(): array
    {
        return $this->paginator()->items();
    }

    public function loadContent(): void
    {
        // Load demo data - native content system to be implemented
        $this->loadDemoData();

        // Apply sorting
        $this->applySorting();
    }

    protected function applySorting(): void
    {
        $items = collect($this->items);

        $items = match ($this->sort) {
            'title' => $items->sortBy(fn ($i) => $i['title']['rendered'] ?? '', SORT_REGULAR, $this->dir === 'desc'),
            'status' => $items->sortBy('status', SORT_REGULAR, $this->dir === 'desc'),
            'modified' => $items->sortBy('modified', SORT_REGULAR, $this->dir === 'desc'),
            default => $items->sortBy('date', SORT_REGULAR, $this->dir === 'desc'),
        };

        $this->items = $items->values()->all();
    }

    protected function loadDemoData(): void
    {
        $workspaceName = $this->currentWorkspace['name'] ?? 'Host UK';
        $workspaceSlug = $this->currentWorkspace['slug'] ?? 'main';

        if ($this->tab === 'posts') {
            $this->items = [];
            for ($i = 1; $i <= 25; $i++) {
                $this->items[] = [
                    'id' => $i,
                    'title' => ['rendered' => "{$workspaceName} Post #{$i}"],
                    'content' => ['rendered' => "<p>Content for post {$i} in {$workspaceName}.</p>"],
                    'status' => $i % 3 === 0 ? 'draft' : 'publish',
                    'date' => now()->subDays($i)->toIso8601String(),
                    'modified' => now()->subDays($i - 1)->toIso8601String(),
                    'excerpt' => ['rendered' => "Excerpt for post {$i}"],
                ];
            }
            $this->total = 25;
        } elseif ($this->tab === 'pages') {
            $pageNames = ['Home', 'About', 'Services', 'Contact', 'Privacy', 'Terms', 'FAQ', 'Blog', 'Portfolio', 'Team'];
            $this->items = [];
            foreach ($pageNames as $i => $name) {
                $this->items[] = [
                    'id' => $i + 10,
                    'title' => ['rendered' => $name],
                    'content' => ['rendered' => "<p>{$workspaceName} {$name} page content.</p>"],
                    'status' => 'publish',
                    'date' => now()->subMonths($i)->toIso8601String(),
                    'modified' => now()->subDays($i)->toIso8601String(),
                    'excerpt' => ['rendered' => ''],
                ];
            }
            $this->total = count($pageNames);
        } else {
            $this->items = [];
            for ($i = 1; $i <= 12; $i++) {
                $this->items[] = [
                    'id' => 100 + $i,
                    'title' => ['rendered' => "{$workspaceSlug}-image-{$i}.jpg"],
                    'media_type' => 'image',
                    'source_url' => '/images/placeholder.jpg',
                    'date' => now()->subDays($i)->toIso8601String(),
                ];
            }
            $this->total = 12;
        }
    }

    public function setSort(string $sort): void
    {
        if ($this->sort === $sort) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $sort;
            $this->dir = 'desc';
        }
        $this->loadContent();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
        $this->loadContent();
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function createNew(): void
    {
        $this->isCreating = true;
        $this->editingId = null;
        $this->editTitle = '';
        $this->editContent = '';
        $this->editStatus = 'draft';
        $this->editExcerpt = '';
        $this->showEditor = true;
    }

    public function edit(int $id): void
    {
        $this->isCreating = false;
        $this->editingId = $id;

        $item = collect($this->items)->firstWhere('id', $id);
        if ($item) {
            $this->editTitle = $item['title']['rendered'] ?? '';
            $this->editContent = $item['content']['rendered'] ?? '';
            $this->editStatus = $item['status'] ?? 'draft';
            $this->editExcerpt = $item['excerpt']['rendered'] ?? '';
        }

        $this->showEditor = true;
    }

    public function save(): void
    {
        // Native content save - to be implemented
        // For now, just close editor and dispatch event

        $this->closeEditor();
        $this->dispatch('content-saved');
    }

    public function delete(int $id): void
    {
        // Native content delete - to be implemented
        // For demo, just remove from items
        $this->items = array_values(array_filter($this->items, fn ($p) => $p['id'] !== $id));
        $this->total = count($this->items);
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->editingId = null;
        $this->isCreating = false;
    }

    public function render()
    {
        return view('hub::admin.content')
            ->layout('hub::admin.layouts.app', ['title' => 'Content']);
    }
}
