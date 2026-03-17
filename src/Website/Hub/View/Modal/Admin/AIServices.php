<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Social\Actions\Common\UpdateOrCreateService;
use Core\Mod\Social\Services\ServiceManager;
use Livewire\Component;

class AIServices extends Component
{
    // Claude configuration
    public string $claudeApiKey = '';

    public string $claudeModel = 'claude-sonnet-4-20250514';

    public bool $claudeActive = false;

    // Gemini configuration
    public string $geminiApiKey = '';

    public string $geminiModel = 'gemini-2.0-flash';

    public bool $geminiActive = false;

    // OpenAI configuration
    public string $openaiSecretKey = '';

    public bool $openaiActive = false;

    // UI state
    public string $activeTab = 'claude';

    public string $savedMessage = '';

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

    public function boot(ServiceManager $serviceManager): void
    {
        $this->serviceManager = $serviceManager;
    }

    public function mount(): void
    {
        $this->loadServices();
    }

    protected function loadServices(): void
    {
        // Load Claude
        try {
            $claude = $this->serviceManager->get('claude');
            $this->claudeApiKey = $claude['configuration']['api_key'] ?? '';
            $this->claudeModel = $claude['configuration']['model'] ?? 'claude-sonnet-4-20250514';
            $this->claudeActive = $claude['active'] ?? false;
        } catch (\Exception $e) {
            // Service not configured yet
        }

        // Load Gemini
        try {
            $gemini = $this->serviceManager->get('gemini');
            $this->geminiApiKey = $gemini['configuration']['api_key'] ?? '';
            $this->geminiModel = $gemini['configuration']['model'] ?? 'gemini-2.0-flash';
            $this->geminiActive = $gemini['active'] ?? false;
        } catch (\Exception $e) {
            // Service not configured yet
        }

        // Load OpenAI
        try {
            $openai = $this->serviceManager->get('openai');
            $this->openaiSecretKey = $openai['configuration']['secret_key'] ?? '';
            $this->openaiActive = $openai['active'] ?? false;
        } catch (\Exception $e) {
            // Service not configured yet
        }
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

        // Clear the cache so changes take effect
        $this->serviceManager->forget('claude');

        $this->savedMessage = 'Claude settings saved.';
        $this->dispatch('service-saved');
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

        $this->savedMessage = 'Gemini settings saved.';
        $this->dispatch('service-saved');
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

        $this->savedMessage = 'OpenAI settings saved.';
        $this->dispatch('service-saved');
    }

    public function getClaudeModelsProperty(): array
    {
        return $this->claudeModels;
    }

    public function getGeminiModelsProperty(): array
    {
        return $this->geminiModels;
    }

    public function render()
    {
        return view('hub::admin.ai-services')
            ->layout('hub::admin.layouts.app', ['title' => 'AI Services']);
    }
}
