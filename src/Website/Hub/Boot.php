<?php

declare(strict_types=1);

namespace Website\Hub;

use Core\Events\AdminPanelBooting;
use Core\Events\DomainResolving;
use Core\Front\Admin\AdminMenuRegistry;
use Core\Front\Admin\Concerns\HasMenuPermissions;
use Core\Front\Admin\Contracts\AdminMenuProvider;
use Core\Website\DomainResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Website\Hub\View\Modal\Admin\GlobalSearch;
use Website\Hub\View\Modal\Admin\WorkspaceSwitcher;

/**
 * Hub Website - Admin dashboard.
 *
 * The authenticated admin panel for managing workspaces.
 * Uses the event-driven $listens pattern for lazy loading.
 */
class Boot extends ServiceProvider implements AdminMenuProvider
{
    use HasMenuPermissions;

    /**
     * Domain patterns this website responds to.
     * Listed separately so DomainResolver can expand them.
     *
     * @var array<string>
     */
    public static array $domains = [
        '/^core\.(test|localhost)$/',
        '/^hub\.core\.(test|localhost)$/',
    ];

    /**
     * Events this module listens to for lazy loading.
     *
     * @var array<class-string, string>
     */
    public static array $listens = [
        DomainResolving::class => 'onDomainResolving',
        AdminPanelBooting::class => 'onAdminPanel',
    ];

    /**
     * Handle domain resolution - register if we match.
     *
     * @example
     * $boot->onDomainResolving($event);
     */
    public function onDomainResolving(DomainResolving $event): void
    {
        foreach (static::$domains as $pattern) {
            if ($event->matches($pattern)) {
                $event->register(static::class);

                return;
            }
        }
    }

    /**
     * Register service bindings for the Hub website.
     *
     * @example
     * $boot->register();
     */
    public function register(): void
    {
        //
    }

    /**
     * Get domains for this website.
     *
     * @return array<string>
     *
     * @example
     * $domains = $this->domains();
     */
    protected function domains(): array
    {
        return app(DomainResolver::class)->domainsFor(self::class);
    }

    /**
     * Register admin panel routes and components.
     *
     * @example
     * $boot->onAdminPanel($event);
     */
    public function onAdminPanel(AdminPanelBooting $event): void
    {
        $event->views('hub', __DIR__.'/View/Blade');

        // Load translations (path should point to Lang folder, Laravel adds locale subdirectory)
        $event->translations('hub', dirname(__DIR__, 2).'/Mod/Hub/Lang');

        // Register Livewire components
        $event->livewire('hub.admin.workspace-switcher', WorkspaceSwitcher::class);
        $event->livewire('hub.admin.global-search', GlobalSearch::class);

        // Register menu provider
        app(AdminMenuRegistry::class)->register($this);

        // Register routes for configured domains
        $primary = true;

        foreach ($this->domains() as $domain) {
            if ($primary) {
                $event->routes(fn () => Route::prefix('hub')
                    ->name('hub.')
                    ->domain($domain)
                    ->group(__DIR__.'/Routes/admin.php'));

                $primary = false;

                continue;
            }

            $event->routes(fn () => $this->prefixSecondaryDomainRoutes($domain, fn () => Route::prefix('hub')
                ->name('hub.')
                ->domain($domain)
                ->group(__DIR__.'/Routes/admin.php')));
        }
    }

    /**
     * Register secondary-domain routes and prefix their names after registration.
     *
     * @example
     * $this->prefixSecondaryDomainRoutes('hub.core.test', fn () => require __DIR__.'/Routes/admin.php');
     */
    private function prefixSecondaryDomainRoutes(string $domain, callable $register): void
    {
        $routes = Route::getRoutes();
        $existingRoutes = [];
        foreach ($routes->getRoutes() as $route) {
            $existingRoutes[spl_object_id($route)] = true;
        }
        $register();
        foreach ($routes->getRoutes() as $route) {
            if (isset($existingRoutes[spl_object_id($route)])) {
                continue;
            }
            $name = $route->getName();
            if ($name === null) {
                continue;
            }
            $route->action['as'] = self::domainRoutePrefix($domain).$name;
        }
        $routes->refreshNameLookups();
    }

    /**
     * Create an injective, route-safe prefix for a secondary domain.
     *
     * @example
     * self::domainRoutePrefix('hub.core.test'); // "domain_6875622e636f72652e74657374."
     */
    private static function domainRoutePrefix(string $domain): string
    {
        return 'domain_'.bin2hex(strtolower($domain)).'.';
    }

    /**
     * Generate a Hub URL for the current primary or secondary domain.
     *
     * @example
     * $url = Boot::hubRoute('hub.dashboard');
     */
    public static function hubRoute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(self::hubRouteName($name), $parameters, $absolute);
    }

    /**
     * Resolve the current-domain route name for a canonical Hub route.
     *
     * @example
     * $routeName = Boot::hubRouteName('hub.dashboard');
     */
    public static function hubRouteName(string $name): string
    {
        $name = ltrim($name, '.');
        $prefix = self::currentHubRoutePrefix();

        if ($prefix !== null && Route::has($prefix.$name)) {
            return $prefix.$name;
        }

        if (Route::has($name)) {
            return $name;
        }

        return $prefix !== null ? $prefix.$name : $name;
    }

    /**
     * Check a Hub route pattern against canonical and current-domain route names.
     *
     * @example
     * Boot::hubRouteIs('hub.sites*');
     */
    public static function hubRouteIs(string $pattern): bool
    {
        if (request()->routeIs($pattern)) {
            return true;
        }

        $prefix = self::currentHubRoutePrefix();

        return $prefix !== null && request()->routeIs($prefix.$pattern);
    }

    /**
     * Extract the secondary-domain prefix from the active route name.
     *
     * @example
     * $prefix = self::currentHubRoutePrefix();
     */
    private static function currentHubRoutePrefix(): ?string
    {
        $routeName = request()->route()?->getName();

        if (! is_string($routeName)) {
            return null;
        }

        $position = strpos($routeName, 'hub.');

        if ($position === false || $position === 0) {
            return null;
        }

        return substr($routeName, 0, $position);
    }

    /**
     * Provide admin menu items.
     *
     * @example
     * $items = $boot->adminMenuItems();
     */
    public function adminMenuItems(): array
    {
        return [
            // Dashboard - standalone group
            [
                'group' => 'dashboard',
                'priority' => 10,
                'item' => fn () => [
                    'label' => __('hub::hub.dashboard.title'),
                    'icon' => 'house',
                    'href' => self::hubRoute('hub.dashboard'),
                    'active' => self::hubRouteIs('hub.dashboard'),
                ],
            ],

            // Workspaces
            [
                'group' => 'workspaces',
                'priority' => 10,
                'item' => fn () => [
                    'label' => __('hub::hub.workspaces.title'),
                    'icon' => 'folders',
                    'href' => self::hubRoute('hub.sites'),
                    'active' => self::hubRouteIs('hub.sites*'),
                ],
            ],

            // Account - Profile
            [
                'group' => 'settings',
                'priority' => 10,
                'item' => fn () => [
                    'label' => __('hub::hub.quick_actions.profile.title'),
                    'icon' => 'user',
                    'href' => self::hubRoute('hub.account'),
                    'active' => self::hubRouteIs('hub.account') && ! self::hubRouteIs('hub.account.*'),
                ],
            ],

            // Account - Settings
            [
                'group' => 'settings',
                'priority' => 20,
                'item' => fn () => [
                    'label' => __('hub::hub.settings.title'),
                    'icon' => 'gear',
                    'href' => self::hubRoute('hub.account.settings'),
                    'active' => self::hubRouteIs('hub.account.settings'),
                ],
            ],

            // Account - Usage
            [
                'group' => 'settings',
                'priority' => 30,
                'item' => fn () => [
                    'label' => __('hub::hub.usage.title'),
                    'icon' => 'chart-pie',
                    'href' => self::hubRoute('hub.account.usage'),
                    'active' => self::hubRouteIs('hub.account.usage'),
                ],
            ],

            // Admin - Platform (Hades only)
            [
                'group' => 'admin',
                'priority' => 10,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Platform',
                    'icon' => 'server',
                    'href' => self::hubRoute('hub.platform'),
                    'active' => self::hubRouteIs('hub.platform*'),
                ],
            ],

            // Admin - Services (Hades only)
            [
                'group' => 'admin',
                'priority' => 20,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Services',
                    'icon' => 'puzzle-piece',
                    'href' => self::hubRoute('hub.admin.services'),
                    'active' => self::hubRouteIs('hub.admin.services'),
                ],
            ],
        ];
    }
}
