<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Menu;

use Closure;
use InvalidArgumentException;

/**
 * Registry for admin menu providers.
 *
 * Modules register AdminMenuProvider implementations here during boot. The
 * registry merges default and module-supplied groups, then resolves menu items
 * ordered by group priority, item priority, and provider priority.
 */
final class AdminMenuRegistry
{
    /**
     * Registered menu providers.
     *
     * @var array<int, AdminMenuProvider>
     */
    private array $providers = [];

    /**
     * Register a menu provider.
     *
     * @example
     * $registry->register(new BlogMenuProvider);
     */
    public function register(AdminMenuProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Register multiple menu providers.
     *
     * @param  array<int, AdminMenuProvider>  $providers
     *
     * @example
     * $registry->registerMany([$blogProvider, $shopProvider]);
     */
    public function registerMany(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Get all registered providers in registration order.
     *
     * @return array<int, AdminMenuProvider>
     *
     * @example
     * $providers = $registry->providers();
     */
    public function providers(): array
    {
        return $this->providers;
    }

    /**
     * Get all known menu groups sorted by priority.
     *
     * @return array<string, MenuGroup>
     *
     * @example
     * $groups = $registry->groups();
     */
    public function groups(): array
    {
        return $this->resolveGroups();
    }

    /**
     * Resolve providers into grouped and sorted menu definitions.
     *
     * @return array<string, array{group: MenuGroup, items: array<int, MenuItem>}>
     *
     * @example
     * $menu = $registry->resolve();
     */
    public function resolve(): array
    {
        $groups = $this->resolveGroups();
        $entries = [];

        foreach ($this->sortedProviders() as $providerIndex => $provider) {
            foreach ($provider->getMenuItems() as $itemIndex => $item) {
                $menuItem = $this->normaliseMenuItem($item);

                if (! isset($groups[$menuItem->group])) {
                    $groups[$menuItem->group] = new MenuGroup(
                        key: $menuItem->group,
                        label: MenuGroup::labelFromKey($menuItem->group),
                    );
                }

                $entries[] = [
                    'item' => $menuItem,
                    'providerPriority' => $provider->getPriority(),
                    'providerIndex' => $providerIndex,
                    'itemIndex' => $itemIndex,
                ];
            }
        }

        $groups = $this->sortGroups($groups);

        usort($entries, function (array $left, array $right) use ($groups): int {
            /** @var MenuItem $leftItem */
            $leftItem = $left['item'];
            /** @var MenuItem $rightItem */
            $rightItem = $right['item'];

            return [
                $groups[$leftItem->group]->priority,
                $leftItem->priority,
                $left['providerPriority'],
                $left['providerIndex'],
                $left['itemIndex'],
                $leftItem->label,
            ] <=> [
                $groups[$rightItem->group]->priority,
                $rightItem->priority,
                $right['providerPriority'],
                $right['providerIndex'],
                $right['itemIndex'],
                $rightItem->label,
            ];
        });

        $resolved = [];

        foreach ($entries as $entry) {
            /** @var MenuItem $item */
            $item = $entry['item'];
            $group = $groups[$item->group];

            if (! isset($resolved[$group->key])) {
                $resolved[$group->key] = [
                    'group' => $group,
                    'items' => [],
                ];
            }

            $resolved[$group->key]['items'][] = $item;
        }

        return $resolved;
    }

    /**
     * Resolve to a flat sorted item list.
     *
     * @return array<int, MenuItem>
     *
     * @example
     * $items = $registry->items();
     */
    public function items(): array
    {
        $items = [];

        foreach ($this->resolve() as $group) {
            array_push($items, ...$group['items']);
        }

        return $items;
    }

    /**
     * Get default RFC groups.
     *
     * @return array<string, MenuGroup>
     *
     * @example
     * $groups = $this->defaultGroups();
     */
    private function defaultGroups(): array
    {
        return [
            'dashboard' => new MenuGroup('dashboard', 'Dashboard', 10),
            'webhost' => new MenuGroup('webhost', 'Web Hosting', 20),
            'services' => new MenuGroup('services', 'Services', 30),
            'settings' => new MenuGroup('settings', 'Settings', 40),
            'admin' => new MenuGroup('admin', 'Admin', 50),
        ];
    }

    /**
     * Resolve default and provider-supplied groups.
     *
     * @return array<string, MenuGroup>
     *
     * @example
     * $groups = $this->resolveGroups();
     */
    private function resolveGroups(): array
    {
        $groups = $this->defaultGroups();

        foreach ($this->sortedProviders() as $provider) {
            foreach ($provider->getMenuGroups() as $group) {
                $menuGroup = $this->normaliseMenuGroup($group);

                if (! isset($groups[$menuGroup->key])) {
                    $groups[$menuGroup->key] = $menuGroup;
                }
            }
        }

        return $this->sortGroups($groups);
    }

    /**
     * Sort groups by priority and key.
     *
     * @param  array<string, MenuGroup>  $groups
     * @return array<string, MenuGroup>
     *
     * @example
     * $groups = $this->sortGroups($groups);
     */
    private function sortGroups(array $groups): array
    {
        uasort($groups, fn (MenuGroup $left, MenuGroup $right): int => [
            $left->priority,
            $left->key,
        ] <=> [
            $right->priority,
            $right->key,
        ]);

        return $groups;
    }

    /**
     * Sort providers by priority while preserving registration order ties.
     *
     * @return array<int, AdminMenuProvider>
     *
     * @example
     * $providers = $this->sortedProviders();
     */
    private function sortedProviders(): array
    {
        $providers = [];

        foreach ($this->providers as $index => $provider) {
            $providers[] = [
                'provider' => $provider,
                'index' => $index,
            ];
        }

        usort($providers, fn (array $left, array $right): int => [
            $left['provider']->getPriority(),
            $left['index'],
        ] <=> [
            $right['provider']->getPriority(),
            $right['index'],
        ]);

        return array_map(
            fn (array $entry): AdminMenuProvider => $entry['provider'],
            $providers
        );
    }

    /**
     * @example
     * $item = $this->normaliseMenuItem(['label' => 'Reports', 'route' => 'admin.reports']);
     */
    private function normaliseMenuItem(mixed $item): MenuItem
    {
        if ($item instanceof Closure) {
            $item = $item();
        }

        if ($item instanceof MenuItem) {
            return $item;
        }

        if (is_array($item)) {
            return MenuItem::fromArray($item);
        }

        throw new InvalidArgumentException(sprintf(
            'Menu providers must return %s instances.',
            MenuItem::class
        ));
    }

    /**
     * @example
     * $group = $this->normaliseMenuGroup(['key' => 'reports', 'label' => 'Reports']);
     */
    private function normaliseMenuGroup(mixed $group): MenuGroup
    {
        if ($group instanceof Closure) {
            $group = $group();
        }

        if ($group instanceof MenuGroup) {
            return $group;
        }

        if (is_array($group)) {
            return MenuGroup::fromArray($group);
        }

        throw new InvalidArgumentException(sprintf(
            'Menu providers must return %s instances from getMenuGroups().',
            MenuGroup::class
        ));
    }
}
