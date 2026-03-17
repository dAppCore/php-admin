<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Content\Enums\ContentType;
use Core\Mod\Agentic\Services\AgenticManager;
use Core\Mod\Content\Models\ContentItem;
use Core\Mod\Content\Models\ContentMedia;
use Core\Mod\Content\Models\ContentRevision;
use Core\Mod\Content\Models\ContentTaxonomy;
use Core\Mod\Agentic\Models\Prompt;
use Core\Tenant\Models\Workspace;
use Core\Tenant\Services\EntitlementService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * ContentEditor - Full-featured content editing component.
 *
 * Phase 2 of TASK-004: Content Editor Enhancements.
 *
 * Features:
 * - Rich text editing with Flux Editor (AC7)
 * - Media/image upload (AC8)
 * - Category/tag management (AC9)
 * - SEO fields (AC10)
 * - Scheduling with publish_at (AC11)
 * - Revision history (AC12)
 */
class ContentEditor extends Component
{
    use WithFileUploads;

    // Content data
    public ?int $contentId = null;

    public ?int $workspaceId = null;

    public string $contentType = 'native';

    public string $type = 'page';

    public string $status = 'draft';

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    // Scheduling (AC11)
    public ?string $publishAt = null;

    public bool $isScheduled = false;

    // SEO fields (AC10)
    public string $seoTitle = '';

    public string $seoDescription = '';

    public string $seoKeywords = '';

    public ?string $ogImage = null;

    // Categories and tags (AC9)
    public array $selectedCategories = [];

    public array $selectedTags = [];

    public string $newTag = '';

    // Media (AC8)
    public ?int $featuredMediaId = null;

    public $featuredImageUpload = null;

    // Revisions (AC12)
    public bool $showRevisions = false;

    public array $revisions = [];

    // AI Command palette
    public bool $showCommand = false;

    public string $commandSearch = '';

    public ?int $selectedPromptId = null;

    public array $promptVariables = [];

    public bool $aiProcessing = false;

    public ?string $aiResult = null;

    // Editor state
    public bool $isDirty = false;

    public ?string $lastSaved = null;

    public int $revisionCount = 0;

    // Sidebar state
    public string $activeSidebar = 'settings'; // settings, seo, media, revisions

    protected AgenticManager $ai;

    protected EntitlementService $entitlements;

    protected $rules = [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'excerpt' => 'nullable|string|max:500',
        'content' => 'required|string',
        'type' => 'required|in:page,post',
        'status' => 'required|in:draft,publish,pending,future,private',
        'contentType' => 'required|in:native,hostuk,satellite,wordpress',
        'publishAt' => 'nullable|date',
        'seoTitle' => 'nullable|string|max:70',
        'seoDescription' => 'nullable|string|max:160',
        'seoKeywords' => 'nullable|string|max:255',
        'featuredImageUpload' => 'nullable|image|max:5120', // 5MB max
    ];

    public function boot(AgenticManager $ai, EntitlementService $entitlements): void
    {
        $this->ai = $ai;
        $this->entitlements = $entitlements;
    }

    public function mount(): void
    {
        $workspace = request()->route('workspace', 'main');
        $id = request()->route('id');
        $contentType = request()->route('contentType', 'native');

        $workspaceModel = Workspace::where('slug', $workspace)->first();
        $this->workspaceId = $workspaceModel?->id;
        $this->contentType = $contentType === 'hostuk' ? 'native' : $contentType;

        if ($id) {
            $this->loadContent((int) $id);
        }
    }

    /**
     * Load existing content for editing.
     */
    public function loadContent(int $id): void
    {
        $item = ContentItem::with(['taxonomies', 'revisions'])->findOrFail($id);

        $this->contentId = $item->id;
        $this->workspaceId = $item->workspace_id;
        $this->contentType = $item->content_type instanceof ContentType
            ? $item->content_type->value
            : ($item->content_type ?? 'native');
        $this->type = $item->type;
        $this->status = $item->status;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->excerpt = $item->excerpt ?? '';
        $this->content = $item->content_html ?? $item->content_markdown ?? '';
        $this->lastSaved = $item->updated_at?->diffForHumans();
        $this->revisionCount = $item->revision_count ?? 0;

        // Scheduling
        $this->publishAt = $item->publish_at?->format('Y-m-d\TH:i');
        $this->isScheduled = $item->status === 'future' && $item->publish_at !== null;

        // SEO
        $seoMeta = $item->seo_meta ?? [];
        $this->seoTitle = $seoMeta['title'] ?? '';
        $this->seoDescription = $seoMeta['description'] ?? '';
        $this->seoKeywords = $seoMeta['keywords'] ?? '';
        $this->ogImage = $seoMeta['og_image'] ?? null;

        // Taxonomies
        $this->selectedCategories = $item->categories->pluck('id')->toArray();
        $this->selectedTags = $item->tags->pluck('id')->toArray();

        // Media
        $this->featuredMediaId = $item->featured_media_id;
    }

    /**
     * Get available categories for this workspace.
     */
    #[Computed]
    public function categories(): array
    {
        if (! $this->workspaceId) {
            return [];
        }

        return ContentTaxonomy::where('workspace_id', $this->workspaceId)
            ->where('type', 'category')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get available tags for this workspace.
     */
    #[Computed]
    public function tags(): array
    {
        if (! $this->workspaceId) {
            return [];
        }

        return ContentTaxonomy::where('workspace_id', $this->workspaceId)
            ->where('type', 'tag')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get available media for this workspace.
     */
    #[Computed]
    public function mediaLibrary(): array
    {
        if (! $this->workspaceId) {
            return [];
        }

        return ContentMedia::where('workspace_id', $this->workspaceId)
            ->images()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->toArray();
    }

    /**
     * Get the featured media object.
     */
    #[Computed]
    public function featuredMedia(): ?ContentMedia
    {
        if (! $this->featuredMediaId) {
            return null;
        }

        return ContentMedia::find($this->featuredMediaId);
    }

    /**
     * Generate slug from title.
     */
    public function updatedTitle(string $value): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->title)) {
            $this->slug = Str::slug($value);
        }
        $this->isDirty = true;
    }

    /**
     * Mark as dirty when content changes.
     */
    public function updatedContent(): void
    {
        $this->isDirty = true;
    }

    /**
     * Handle scheduling toggle.
     */
    public function updatedIsScheduled(bool $value): void
    {
        if ($value) {
            $this->status = 'future';
            if (empty($this->publishAt)) {
                // Default to tomorrow at 9am
                $this->publishAt = now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i');
            }
        } else {
            if ($this->status === 'future') {
                $this->status = 'draft';
            }
            $this->publishAt = null;
        }
        $this->isDirty = true;
    }

    /**
     * Add a new tag.
     */
    public function addTag(): void
    {
        if (empty($this->newTag) || ! $this->workspaceId) {
            return;
        }

        $slug = Str::slug($this->newTag);

        // Check if tag exists
        $existing = ContentTaxonomy::where('workspace_id', $this->workspaceId)
            ->where('type', 'tag')
            ->where('slug', $slug)
            ->first();

        if ($existing) {
            if (! in_array($existing->id, $this->selectedTags)) {
                $this->selectedTags[] = $existing->id;
            }
        } else {
            // Create new tag
            $tag = ContentTaxonomy::create([
                'workspace_id' => $this->workspaceId,
                'type' => 'tag',
                'name' => $this->newTag,
                'slug' => $slug,
            ]);
            $this->selectedTags[] = $tag->id;
        }

        $this->newTag = '';
        $this->isDirty = true;
    }

    /**
     * Remove a tag.
     */
    public function removeTag(int $tagId): void
    {
        $this->selectedTags = array_values(array_filter(
            $this->selectedTags,
            fn ($id) => $id !== $tagId
        ));
        $this->isDirty = true;
    }

    /**
     * Toggle a category.
     */
    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_filter(
                $this->selectedCategories,
                fn ($id) => $id !== $categoryId
            ));
        } else {
            $this->selectedCategories[] = $categoryId;
        }
        $this->isDirty = true;
    }

    /**
     * Set featured image from media library.
     */
    public function setFeaturedMedia(int $mediaId): void
    {
        $this->featuredMediaId = $mediaId;
        $this->isDirty = true;
    }

    /**
     * Remove featured image.
     */
    public function removeFeaturedMedia(): void
    {
        $this->featuredMediaId = null;
        $this->isDirty = true;
    }

    /**
     * Upload featured image.
     */
    public function uploadFeaturedImage(): void
    {
        $this->validate([
            'featuredImageUpload' => 'required|image|max:5120',
        ]);

        if (! $this->workspaceId) {
            $this->dispatch('notify', message: 'No workspace selected', type: 'error');

            return;
        }

        // Store the file
        $path = $this->featuredImageUpload->store('content-media', 'public');

        // Create media record
        $media = ContentMedia::create([
            'workspace_id' => $this->workspaceId,
            'type' => 'image',
            'title' => pathinfo($this->featuredImageUpload->getClientOriginalName(), PATHINFO_FILENAME),
            'source_url' => asset('storage/'.$path),
            'alt_text' => $this->title,
            'mime_type' => $this->featuredImageUpload->getMimeType(),
        ]);

        $this->featuredMediaId = $media->id;
        $this->featuredImageUpload = null;
        $this->isDirty = true;

        $this->dispatch('notify', message: 'Image uploaded', type: 'success');
    }

    /**
     * Load revision history.
     */
    public function loadRevisions(): void
    {
        if (! $this->contentId) {
            $this->revisions = [];

            return;
        }

        $this->revisions = ContentRevision::forContentItem($this->contentId)
            ->withoutAutosaves()
            ->latestFirst()
            ->with('user')
            ->take(20)
            ->get()
            ->toArray();

        $this->showRevisions = true;
        $this->activeSidebar = 'revisions';
    }

    /**
     * Restore a revision.
     */
    public function restoreRevision(int $revisionId): void
    {
        $revision = ContentRevision::findOrFail($revisionId);

        if ($revision->content_item_id !== $this->contentId) {
            $this->dispatch('notify', message: 'Invalid revision', type: 'error');

            return;
        }

        // Load revision data into form
        $this->title = $revision->title;
        $this->excerpt = $revision->excerpt ?? '';
        $this->content = $revision->content_html ?? $revision->content_markdown ?? '';

        // Restore SEO if available
        if ($revision->seo_meta) {
            $this->seoTitle = $revision->seo_meta['title'] ?? '';
            $this->seoDescription = $revision->seo_meta['description'] ?? '';
            $this->seoKeywords = $revision->seo_meta['keywords'] ?? '';
        }

        $this->isDirty = true;
        $this->showRevisions = false;

        $this->dispatch('notify', message: "Restored revision #{$revision->revision_number}", type: 'success');
    }

    /**
     * Save the content.
     */
    public function save(string $changeType = ContentRevision::CHANGE_EDIT): void
    {
        $this->validate();

        // Build SEO meta
        $seoMeta = [
            'title' => $this->seoTitle,
            'description' => $this->seoDescription,
            'keywords' => $this->seoKeywords,
            'og_image' => $this->ogImage,
        ];

        $data = [
            'workspace_id' => $this->workspaceId,
            'content_type' => $this->contentType,
            'type' => $this->type,
            'status' => $this->status,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content_html' => $this->content,
            'content_markdown' => $this->content,
            'seo_meta' => $seoMeta,
            'featured_media_id' => $this->featuredMediaId,
            'publish_at' => $this->isScheduled && $this->publishAt ? $this->publishAt : null,
            'last_edited_by' => auth()->id(),
            'sync_status' => 'synced',
            'synced_at' => now(),
        ];

        $isNew = ! $this->contentId;

        if ($this->contentId) {
            $item = ContentItem::findOrFail($this->contentId);
            $item->update($data);
        } else {
            $item = ContentItem::create($data);
            $this->contentId = $item->id;
        }

        // Sync taxonomies
        $taxonomyIds = array_merge($this->selectedCategories, $this->selectedTags);
        $item->taxonomies()->sync($taxonomyIds);

        // Create revision (except for autosaves on new content)
        if (! $isNew || $changeType !== ContentRevision::CHANGE_AUTOSAVE) {
            $item->createRevision(auth()->user(), $changeType);
            $this->revisionCount = $item->fresh()->revision_count ?? 0;
        }

        $this->isDirty = false;
        $this->lastSaved = 'just now';

        $this->dispatch('content-saved', id: $item->id);
        $this->dispatch('notify', message: 'Content saved successfully', type: 'success');
    }

    /**
     * Autosave the content (called periodically).
     */
    public function autosave(): void
    {
        if (! $this->isDirty || empty($this->title) || empty($this->content)) {
            return;
        }

        $this->save(ContentRevision::CHANGE_AUTOSAVE);
    }

    /**
     * Publish the content.
     */
    public function publish(): void
    {
        $this->status = 'publish';
        $this->isScheduled = false;
        $this->publishAt = null;
        $this->save(ContentRevision::CHANGE_PUBLISH);
    }

    /**
     * Schedule the content.
     */
    public function schedule(): void
    {
        if (empty($this->publishAt)) {
            $this->dispatch('notify', message: 'Please set a publish date', type: 'error');

            return;
        }

        $this->status = 'future';
        $this->isScheduled = true;
        $this->save(ContentRevision::CHANGE_SCHEDULE);
    }

    /**
     * Get available prompts for AI command palette.
     */
    #[Computed]
    public function prompts(): array
    {
        $query = Prompt::active();

        if ($this->commandSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->commandSearch}%")
                    ->orWhere('description', 'like', "%{$this->commandSearch}%")
                    ->orWhere('category', 'like', "%{$this->commandSearch}%");
            });
        }

        return $query->orderBy('category')->orderBy('name')->get()->groupBy('category')->toArray();
    }

    /**
     * Get quick AI actions.
     */
    #[Computed]
    public function quickActions(): array
    {
        return [
            [
                'name' => 'Improve writing',
                'description' => 'Enhance clarity and flow',
                'icon' => 'sparkles',
                'prompt' => 'content-refiner',
                'variables' => ['instruction' => 'Improve clarity, flow, and readability while maintaining the original meaning.'],
            ],
            [
                'name' => 'Fix grammar',
                'description' => 'Correct spelling and grammar',
                'icon' => 'check-circle',
                'prompt' => 'content-refiner',
                'variables' => ['instruction' => 'Fix any spelling, grammar, or punctuation errors using UK English conventions.'],
            ],
            [
                'name' => 'Make shorter',
                'description' => 'Condense the content',
                'icon' => 'arrows-pointing-in',
                'prompt' => 'content-refiner',
                'variables' => ['instruction' => 'Make this content more concise without losing important information.'],
            ],
            [
                'name' => 'Make longer',
                'description' => 'Expand with more detail',
                'icon' => 'arrows-pointing-out',
                'prompt' => 'content-refiner',
                'variables' => ['instruction' => 'Expand this content with more detail, examples, and explanation.'],
            ],
            [
                'name' => 'Generate SEO',
                'description' => 'Create meta title and description',
                'icon' => 'magnifying-glass',
                'prompt' => 'seo-title-optimizer',
                'variables' => [],
            ],
        ];
    }

    /**
     * Open the AI command palette.
     */
    public function openCommand(): void
    {
        $this->showCommand = true;
        $this->commandSearch = '';
        $this->selectedPromptId = null;
        $this->promptVariables = [];
    }

    /**
     * Close the AI command palette.
     */
    public function closeCommand(): void
    {
        $this->showCommand = false;
        $this->aiResult = null;
    }

    /**
     * Select a prompt from the command palette.
     */
    public function selectPrompt(int $promptId): void
    {
        $this->selectedPromptId = $promptId;

        $prompt = Prompt::find($promptId);
        if ($prompt && ! empty($prompt->variables)) {
            foreach ($prompt->variables as $name => $config) {
                $this->promptVariables[$name] = $config['default'] ?? '';
            }
        }
    }

    /**
     * Execute a quick action.
     */
    public function executeQuickAction(string $promptName, array $variables = []): void
    {
        $prompt = Prompt::where('name', $promptName)->first();

        if (! $prompt) {
            $this->dispatch('notify', message: 'Prompt not found', type: 'error');

            return;
        }

        $variables['content'] = $this->content;
        $this->runAiPrompt($prompt, $variables);
    }

    /**
     * Execute the selected prompt.
     */
    public function executePrompt(): void
    {
        if (! $this->selectedPromptId) {
            return;
        }

        $prompt = Prompt::find($this->selectedPromptId);
        if (! $prompt) {
            return;
        }

        $variables = $this->promptVariables;
        $variables['content'] = $this->content;
        $variables['title'] = $this->title;
        $variables['excerpt'] = $this->excerpt;

        $this->runAiPrompt($prompt, $variables);
    }

    /**
     * Run an AI prompt and display results.
     */
    protected function runAiPrompt(Prompt $prompt, array $variables): void
    {
        $this->aiProcessing = true;
        $this->aiResult = null;

        try {
            $workspace = $this->workspaceId ? Workspace::find($this->workspaceId) : null;

            if ($workspace) {
                $result = $this->entitlements->can($workspace, 'ai.credits');
                if ($result->isDenied()) {
                    $this->dispatch('notify', message: $result->message, type: 'error');
                    $this->aiProcessing = false;

                    return;
                }
            }

            $provider = $this->ai->provider($prompt->model);
            $userPrompt = $this->interpolateVariables($prompt->user_template, $variables);

            $response = $provider->generate(
                $prompt->system_prompt,
                $userPrompt,
                $prompt->model_config ?? []
            );

            $this->aiResult = $response->content;

            if ($workspace) {
                $this->entitlements->recordUsage(
                    $workspace,
                    'ai.credits',
                    quantity: 1,
                    user: auth()->user(),
                    metadata: [
                        'prompt_id' => $prompt->id,
                        'model' => $response->model,
                        'tokens_input' => $response->inputTokens,
                        'tokens_output' => $response->outputTokens,
                        'estimated_cost' => $response->estimateCost(),
                    ]
                );
            }

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'AI request failed: '.$e->getMessage(), type: 'error');
        }

        $this->aiProcessing = false;
    }

    /**
     * Apply AI result to content.
     */
    public function applyAiResult(): void
    {
        if ($this->aiResult) {
            $this->content = $this->aiResult;
            $this->isDirty = true;
            $this->closeCommand();
            $this->dispatch('notify', message: 'AI suggestions applied', type: 'success');
        }
    }

    /**
     * Insert AI result at cursor (append for now).
     */
    public function insertAiResult(): void
    {
        if ($this->aiResult) {
            $this->content .= "\n\n".$this->aiResult;
            $this->isDirty = true;
            $this->closeCommand();
            $this->dispatch('notify', message: 'AI content inserted', type: 'success');
        }
    }

    /**
     * Interpolate template variables.
     */
    protected function interpolateVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        $template = preg_replace_callback(
            '/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/s',
            function ($matches) use ($variables) {
                $key = $matches[1];
                $content = $matches[2];

                return ! empty($variables[$key]) ? $content : '';
            },
            $template
        );

        $template = preg_replace_callback(
            '/\{\{#each\s+(\w+)\}\}(.*?)\{\{\/each\}\}/s',
            function ($matches) use ($variables) {
                $key = $matches[1];
                $content = $matches[2];
                if (empty($variables[$key]) || ! is_array($variables[$key])) {
                    return '';
                }
                $result = '';
                foreach ($variables[$key] as $item) {
                    $result .= str_replace('{{this}}', $item, $content);
                }

                return $result;
            },
            $template
        );

        return $template;
    }

    /**
     * Handle keyboard shortcut to open command.
     */
    #[On('open-ai-command')]
    public function handleOpenCommand(): void
    {
        $this->openCommand();
    }

    public function render()
    {
        return view('hub::admin.content-editor')
            ->layout('hub::admin.layouts.app', [
                'title' => $this->contentId ? 'Edit Content' : 'New Content',
            ]);
    }
}
