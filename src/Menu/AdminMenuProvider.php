<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Menu;

/**
 * Contract for CorePHP modules that contribute admin menu entries.
 *
 * Modules register implementations with AdminMenuRegistry from their Boot
 * class so admin navigation can be extended without editing admin directly.
 */
interface AdminMenuProvider
{
    /**
     * Menu items supplied by this provider.
     *
     * @return array<int, MenuItem|array<string, mixed>|callable>
     *
     * @example
     * return [new MenuItem('Reports', 'admin.reports', 'fa-file-lines', 'admin')];
     */
    public function getMenuItems(): array;

    /**
     * Menu groups supplied by this provider.
     *
     * @return array<int, MenuGroup|array<string, mixed>|callable>
     *
     * @example
     * return [new MenuGroup('reports', 'Reports', 25)];
     */
    public function getMenuGroups(): array;

    /**
     * Provider priority used as a deterministic tie-breaker.
     *
     * Lower values resolve earlier. Higher priority appears later.
     *
     * @example
     * return 50;
     */
    public function getPriority(): int;
}
