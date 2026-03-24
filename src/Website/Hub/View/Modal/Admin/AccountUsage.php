<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Front\Admin\AdminMenuRegistry;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Core\Mod\Social\Actions\Common\UpdateOrCreateService;
use Core\Mod\Social\Services\ServiceManager;
use Core\Tenant\Models\Feature;
use Core\Tenant\Models\Workspace;
use Core\Tenant\Services\EntitlementService;

class AccountUsage extends Component
{
    #[Url(as: 'tab')]
    public string $activeSection = 'overview';

    // Usage data (loaded on demand)
    public ?array $usageSummary = null;

    public ?array $activePackages = null;

    public ?array $activeBoosts = null;

    // Boost options (loaded on demand)
    public ?array $boostOptions = null;

    // AI services loaded flag
    protected bool $aiServicesLoaded = false;

    // AI Services
    public string $claudeApiKey = '';

    public string $claudeModel = 'claude-sonnet-4-20250514';

    public bool $claudeActive = false;

    public string $geminiApiKey = '';

    public string $geminiModel = 'gemini-2.0-flash';

    public bool $geminiActive = false;

    public string $openaiSecretKey = '';

    public bool $openaiActive = false;

    public string $activeAiTab = 'claude';

    protected array $claudeModels = [
        'claude-sonnet-4-20250514' => 'Claude Sonnet 4 (Recommended)',
        'claude-opus-4-20250514' => 'Claude Opus 4',
        'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
        'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Fast)',
    ];

    protected array $geminiModels = [
        'gemini-2.0-flash' => 'Gemini 2.0 Flash (Recommended)',
        'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite (Fast)',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
    ];

    protected ServiceManager $serviceManager;

    protected EntitlementService $entitlementService;

    public function boot(ServiceManager $serviceManager, EntitlementService $entitlementService): void
    {
        $this->serviceManager = $serviceManager;
        $this->entitlementService = $entitlementService;
    }

    public function mount(): void
    {
        $this->loadDataForTab($this->activeSection);
    }

    /**
     * Load data when tab changes.
     */
    public function updatedActiveSection(string $tab): void
    {
        $this->loadDataForTab($tab);
    }

    /**
     * Load only the data needed for the active tab.
     */
    protected function loadDataForTab(string $tab): void
    {
        match ($tab) {
            'overview' => $this->loadUsageData(),
            'boosts' => $this->loadBoostOptions(),
            'ai' => $this->loadAiServices(),
            default => null,
        };
    }

    protected function loadUsageData(): void
    {
        if ($this->usageSummary !== null) {
            return; // Already loaded
        }

        $workspace = Auth::user()?->defaultHostWorkspace();

        if (! $workspace) {
            $this->usageSummary = [];
            $this->activePackages = [];
            $this->activeBoosts = [];

            return;
        }

        $this->usageSummary = $this->entitlementService->getUsageSummary($workspace)->toArray();
        $this->activePackages = $this->entitlementService->getActivePackages($workspace)->toArray();
        $this->activeBoosts = $this->entitlementService->getActiveBoosts($workspace)->toArray();
    }

    protected function loadBoostOptions(): void
    {
        if ($this->boostOptions !== null) {
            return; // Already loaded
        }

        $addonMapping = config('services.blesta.addon_mapping', []);

        $this->boostOptions = collect($addonMapping)->map(function ($config, $blestaId) {
            $feature = Feature::where('code', $config['feature_code'])->first();

            return [
                'blesta_id' => $blestaId,
                'feature_code' => $config['feature_code'],
                'feature_name' => $feature?->name ?? $config['feature_code'],
                'boost_type' => $config['boost_type'],
                'limit_value' => $config['limit_value'] ?? null,
                'duration_type' => $config['duration_type'],
                'description' => $this->getBoostDescription($config),
            ];
        })->values()->toArray();
    }

    protected function getBoostDescription(array $config): string
    {
        $type = $config['boost_type'];
        $value = $config['limit_value'] ?? null;
        $duration = $config['duration_type'];

        $description = match ($type) {
            'add_limit' => "+{$value} additional",
            'unlimited' => 'Unlimited access',
            'enable' => 'Feature enabled',
            default => 'Boost',
        };

        $durationText = match ($duration) {
            'cycle_bound' => 'until billing cycle ends',
            'duration' => 'for limited time',
            'permanent' => 'permanently',
            default => '',
        };

        return trim("{$description} {$durationText}");
    }

    protected function loadAiServices(): void
    {
        if ($this->aiServicesLoaded) {
            return; // Already loaded
        }

        try {
            $claude = $this->serviceManager->get('claude');
            $this->claudeApiKey = $claude['configuration']['api_key'] ?? '';
            $this->claudeModel = $claude['configuration']['model'] ?? 'claude-sonnet-4-20250514';
            $this->claudeActive = $claude['active'] ?? false;
        } catch (\Exception) {
        }

        try {
            $gemini = $this->serviceManager->get('gemini');
            $this->geminiApiKey = $gemini['configuration']['api_key'] ?? '';
            $this->geminiModel = $gemini['configuration']['model'] ?? 'gemini-2.0-flash';
            $this->geminiActive = $gemini['active'] ?? false;
        } catch (\Exception) {
        }

        try {
            $openai = $this->serviceManager->get('openai');
            $this->openaiSecretKey = $openai['configuration']['secret_key'] ?? '';
            $this->openaiActive = $openai['active'] ?? false;
        } catch (\Exception) {
        }

        $this->aiServicesLoaded = true;
    }

    public function purchaseBoost(string $blestaId): void
    {
        $blestaUrl = config('services.blesta.url', 'https://billing.host.uk.com');
        $this->redirect("{$blestaUrl}/order/addon/{$blestaId}");
    }

    public function saveClaude(): void
    {
        $this->validate([
            'claudeApiKey' => 'required_if:claudeActive,true',
            'claudeModel' => 'required|in:'.implode(',', array_keys($this->claudeModels)),
        ], [
            'claudeApiKey.required_if' => 'API key is required when the service is active.',
        ]);

        (new UpdateOrCreateService)(
            name: 'claude',
            configuration: [
                'api_key' => $this->claudeApiKey,
                'model' => $this->claudeModel,
            ],
            active: $this->claudeActive
        );

        $this->serviceManager->forget('claude');
        Flux::toast(text: 'Claude settings saved.', variant: 'success');
    }

    public function saveGemini(): void
    {
        $this->validate([
            'geminiApiKey' => 'required_if:geminiActive,true',
            'geminiModel' => 'required|in:'.implode(',', array_keys($this->geminiModels)),
        ], [
            'geminiApiKey.required_if' => 'API key is required when the service is active.',
        ]);

        (new UpdateOrCreateService)(
            name: 'gemini',
            configuration: [
                'api_key' => $this->geminiApiKey,
                'model' => $this->geminiModel,
            ],
            active: $this->geminiActive
        );

        $this->serviceManager->forget('gemini');
        Flux::toast(text: 'Gemini settings saved.', variant: 'success');
    }

    public function saveOpenAI(): void
    {
        $this->validate([
            'openaiSecretKey' => 'required_if:openaiActive,true',
        ], [
            'openaiSecretKey.required_if' => 'API key is required when the service is active.',
        ]);

        (new UpdateOrCreateService)(
            name: 'openai',
            configuration: [
                'secret_key' => $this->openaiSecretKey,
            ],
            active: $this->openaiActive
        );

        $this->serviceManager->forget('openai');
        Flux::toast(text: 'OpenAI settings saved.', variant: 'success');
    }

    #[Computed]
    public function claudeModelsComputed(): array
    {
        return $this->claudeModels;
    }

    #[Computed]
    public function geminiModelsComputed(): array
    {
        return $this->geminiModels;
    }

    /**
     * Get all features grouped by category for entitlements display.
     */
    #[Computed]
    public function allFeatures(): array
    {
        return Feature::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    /**
     * Get all user workspaces with subscription and cost information.
     */
    #[Computed]
    public function userWorkspaces(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $registry = app(AdminMenuRegistry::class);
        $isHades = $user->isHades();

        return $user->workspaces()
            ->orderBy('name')
            ->get()
            ->map(function (Workspace $workspace) use ($registry, $isHades) {
                $subscription = $workspace->activeSubscription();
                $services = $registry->getAllServiceItems($workspace, $isHades);

                return [
                    'workspace' => $workspace,
                    'subscription' => $subscription,
                    'plan' => $subscription?->workspacePackage?->package?->name ?? 'Free',
                    'status' => $subscription?->status ?? 'inactive',
                    'renewsAt' => $subscription?->current_period_end,
                    'price' => $subscription?->workspacePackage?->package?->price ?? 0,
                    'currency' => $subscription?->workspacePackage?->package?->currency ?? 'GBP',
                    'services' => $services,
                    'serviceCount' => count($services),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('hub::admin.account-usage')
            ->layout('hub::admin.layouts.app', ['title' => 'Usage & Billing']);
    }
}
