<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Admin\Menu\AdminMenuProvider;
use Core\Admin\Menu\AdminMenuRegistry;
use Core\Admin\Menu\MenuGroup;
use Core\Admin\Menu\MenuItem;

function createAdminMenuRegistryProvider(
    array $items = [],
    array $groups = [],
    int $priority = 50,
): AdminMenuProvider {
    return new class($items, $groups, $priority) implements AdminMenuProvider
    {
        public function __construct(
            private readonly array $items,
            private readonly array $groups,
            private readonly int $priority,
        ) {}

        public function getMenuItems(): array
        {
            return $this->items;
        }

        public function getMenuGroups(): array
        {
            return $this->groups;
        }

        public function getPriority(): int
        {
            return $this->priority;
        }
    };
}

describe('AdminMenuRegistry _Good', function () {
    it('registers providers and resolves grouped menu items by group and item priority', function () {
        $registry = new AdminMenuRegistry;

        $registry->register(createAdminMenuRegistryProvider(
            items: [
                new MenuItem('Settings', 'admin.settings', 'fa-cog', 'settings', 10),
                new MenuItem('Analytics', 'admin.analytics', 'fa-chart-line', 'dashboard', 20),
                new MenuItem('Biolinks', 'admin.bio.index', 'fa-link', 'services', 30),
                new MenuItem('Reports', 'admin.reports', 'fa-file-lines', 'reports', 10),
            ],
            groups: [
                new MenuGroup('reports', 'Reports', 25),
            ],
        ));

        $menu = $registry->resolve();

        expect(array_keys($menu))->toBe(['dashboard', 'reports', 'services', 'settings'])
            ->and($menu['dashboard']['group']->label)->toBe('Dashboard')
            ->and($menu['reports']['group']->label)->toBe('Reports')
            ->and($menu['dashboard']['items'][0]->label)->toBe('Analytics')
            ->and($menu['reports']['items'][0]->label)->toBe('Reports')
            ->and($menu['services']['items'][0]->label)->toBe('Biolinks')
            ->and($menu['settings']['items'][0]->label)->toBe('Settings');
    });

    it('uses provider priority as a deterministic tie-breaker', function () {
        $registry = new AdminMenuRegistry;

        $registry->register(createAdminMenuRegistryProvider(
            items: [
                new MenuItem('Later Provider', 'admin.later', 'fa-clock', 'services', 10),
            ],
            priority: 80,
        ));
        $registry->register(createAdminMenuRegistryProvider(
            items: [
                new MenuItem('Earlier Provider', 'admin.earlier', 'fa-bolt', 'services', 10),
            ],
            priority: 10,
        ));

        $labels = array_map(
            fn (MenuItem $item): string => $item->label,
            $registry->resolve()['services']['items'],
        );

        expect($labels)->toBe(['Earlier Provider', 'Later Provider']);
    });
});

describe('AdminMenuRegistry _Bad', function () {
    it('returns an empty menu when no providers are registered', function () {
        $registry = new AdminMenuRegistry;

        expect($registry->providers())->toBe([])
            ->and($registry->resolve())->toBe([]);
    });

    it('rejects malformed provider menu items', function () {
        $registry = new AdminMenuRegistry;

        $registry->register(createAdminMenuRegistryProvider(
            items: [
                ['label' => 'Missing Route', 'icon' => 'fa-triangle-exclamation', 'group' => 'admin'],
            ],
        ));

        expect(fn () => $registry->resolve())
            ->toThrow(InvalidArgumentException::class, 'Menu item route must be a string or closure.');
    });

    it('rejects malformed provider menu groups', function () {
        $registry = new AdminMenuRegistry;

        $registry->register(createAdminMenuRegistryProvider(
            groups: [
                ['label' => 'Missing Key', 'priority' => 15],
            ],
        ));

        expect(fn () => $registry->groups())
            ->toThrow(InvalidArgumentException::class, 'Menu group key cannot be empty.');
    });

    it('normalises menu group key and label whitespace', function () {
        $group = MenuGroup::fromArray([
            'key' => ' reports ',
            'label' => ' Reports ',
        ]);

        expect($group->key)->toBe('reports')
            ->and($group->label)->toBe('Reports');
    });
});

describe('AdminMenuRegistry _Ugly', function () {
    it('creates missing groups for items while keeping lazy routes unresolved until rendering', function () {
        $registry = new AdminMenuRegistry;
        $routeWasResolved = false;

        $registry->register(createAdminMenuRegistryProvider(
            items: [
                new MenuItem(
                    label: 'Custom Tool',
                    route: function () use (&$routeWasResolved): string {
                        $routeWasResolved = true;

                        return 'admin.custom.tool';
                    },
                    icon: 'fa-screwdriver-wrench',
                    group: 'custom-tools',
                    priority: 10,
                ),
            ],
        ));

        $menu = $registry->resolve();

        expect($routeWasResolved)->toBeFalse()
            ->and(array_keys($menu))->toBe(['custom-tools'])
            ->and($menu['custom-tools']['group']->label)->toBe('Custom Tools')
            ->and($menu['custom-tools']['items'][0]->toArray()['route'])->toBe('admin.custom.tool')
            ->and($routeWasResolved)->toBeTrue();
    });

    it('accepts the RFC lazy item wrapper shape for module registration', function () {
        $registry = new AdminMenuRegistry;

        $registry->register(createAdminMenuRegistryProvider(
            items: [
                [
                    'group' => 'services',
                    'priority' => 10,
                    'item' => fn (): array => [
                        'label' => 'Biolinks',
                        'icon' => 'fa-link',
                        'href' => 'admin.bio.index',
                    ],
                ],
            ],
        ));

        $item = $registry->resolve()['services']['items'][0];

        expect($item)->toBeInstanceOf(MenuItem::class)
            ->and($item->label)->toBe('Biolinks')
            ->and($item->route)->toBe('admin.bio.index');
    });
});
