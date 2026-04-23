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

    public function register(): void
    {
        //
    }

    /**
     * Get domains for this website.
     *
     * @return array<string>
     */
    protected function domains(): array
    {
        return app(DomainResolver::class)->domainsFor(self::class);
    }

    /**
     * Register admin panel routes and components.
     */
    public function onAdminPanel(AdminPanelBooting $event): void
    {
        $event->views('hub', __DIR__.'/View/Blade');

        // Load translations (path should point to Lang folder, Laravel adds locale subdirectory)
        $event->translations('hub', dirname(__DIR__, 2).'/Mod/Hub/Lang');

        // Register Livewire components
        $event->livewire('hub.admin.workspace-switcher', \Website\Hub\View\Modal\Admin\WorkspaceSwitcher::class);
        $event->livewire('hub.admin.global-search', \Website\Hub\View\Modal\Admin\GlobalSearch::class);

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
                ->domain($domain)
                ->group(__DIR__.'/Routes/admin.php')));
        }
    }

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

    private static function domainRoutePrefix(string $domain): string
    {
        return strtr($domain, ['.' => '_', '-' => '_']).'.';
    }

    /**
     * Provide admin menu items.
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
                    'href' => route('hub.dashboard'),
                    'active' => request()->routeIs('hub.dashboard'),
                ],
            ],

            // Workspaces
            [
                'group' => 'workspaces',
                'priority' => 10,
                'item' => fn () => [
                    'label' => __('hub::hub.workspaces.title'),
                    'icon' => 'folders',
                    'href' => route('hub.sites'),
                    'active' => request()->routeIs('hub.sites*'),
                ],
            ],

            // Account - Profile
            [
                'group' => 'settings',
                'priority' => 10,
                'item' => fn () => [
                    'label' => __('hub::hub.quick_actions.profile.title'),
                    'icon' => 'user',
                    'href' => route('hub.account'),
                    'active' => request()->routeIs('hub.account') && ! request()->routeIs('hub.account.*'),
                ],
            ],

            // Account - Settings
            [
                'group' => 'settings',
                'priority' => 20,
                'item' => fn () => [
                    'label' => __('hub::hub.settings.title'),
                    'icon' => 'gear',
                    'href' => route('hub.account.settings'),
                    'active' => request()->routeIs('hub.account.settings'),
                ],
            ],

            // Account - Usage
            [
                'group' => 'settings',
                'priority' => 30,
                'item' => fn () => [
                    'label' => __('hub::hub.usage.title'),
                    'icon' => 'chart-pie',
                    'href' => route('hub.account.usage'),
                    'active' => request()->routeIs('hub.account.usage'),
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
                    'href' => route('hub.platform'),
                    'active' => request()->routeIs('hub.platform*'),
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
                    'href' => route('hub.admin.services'),
                    'active' => request()->routeIs('hub.admin.services'),
                ],
            ],
        ];
    }
}
