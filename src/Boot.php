<?php

declare(strict_types=1);

namespace Core\Admin;

use Core\Admin\Forms\View\Components\Button;
use Core\Admin\Forms\View\Components\Checkbox;
use Core\Admin\Forms\View\Components\FormGroup;
use Core\Admin\Forms\View\Components\Input;
use Core\Admin\Forms\View\Components\Select;
use Core\Admin\Forms\View\Components\Textarea;
use Core\Admin\Forms\View\Components\Toggle;
use Core\Admin\Search\Providers\AdminPageSearchProvider;
use Core\Admin\Search\SearchProviderRegistry;
use Core\ModuleRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Core Admin Package Bootstrap.
 *
 * Registers package paths with the module scanner and initializes
 * admin-specific services like the search provider registry.
 */
class Boot extends ServiceProvider
{
    public function register(): void
    {
        // Register our Website modules with the scanner
        app(ModuleRegistry::class)->addPaths([
            __DIR__.'/Website',
        ]);

        // Register the search provider registry as a singleton
        $this->app->singleton(SearchProviderRegistry::class);
        $this->app->singleton(\Core\Admin\Menu\AdminMenuRegistry::class);
    }

    public function boot(): void
    {
        // Load Hub translations
        $this->loadTranslationsFrom(__DIR__.'/Mod/Hub/Lang', 'hub');

        // Register form components
        $this->registerFormComponents();

        // Register the default search providers
        $this->registerSearchProviders();
    }

    /**
     * Register form components with authorization support.
     *
     * Components are registered with the 'core-forms' prefix:
     * - <x-core-forms.input />
     * - <x-core-forms.textarea />
     * - <x-core-forms.select />
     * - <x-core-forms.checkbox />
     * - <x-core-forms.button />
     * - <x-core-forms.toggle />
     * - <x-core-forms.form-group />
     */
    protected function registerFormComponents(): void
    {
        // Register views namespace for form component templates
        $this->loadViewsFrom(dirname(__DIR__).'/resources/views', 'core-forms');

        // Register class-backed form components
        Blade::component('core-forms.input', Input::class);
        Blade::component('core-forms.textarea', Textarea::class);
        Blade::component('core-forms.select', Select::class);
        Blade::component('core-forms.checkbox', Checkbox::class);
        Blade::component('core-forms.button', Button::class);
        Blade::component('core-forms.toggle', Toggle::class);
        Blade::component('core-forms.form-group', FormGroup::class);
    }

    /**
     * Register the default search providers.
     */
    protected function registerSearchProviders(): void
    {
        $registry = $this->app->make(SearchProviderRegistry::class);

        // Register the built-in admin page search provider
        $registry->register($this->app->make(AdminPageSearchProvider::class));
    }
}
