<?php

declare(strict_types=1);

/**
 * UseCase: Hub Dashboard (Basic Flow)
 *
 * Acceptance test for the Hub admin dashboard.
 * Tests the happy path user journey through the browser.
 *
 * Uses translation keys to get expected values - tests won't break on copy changes.
 */

use Core\Tenant\Models\User;
use Core\Tenant\Models\Workspace;

describe('Hub Dashboard', function () {
    beforeEach(function () {
        // Create user with workspace
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->workspace = Workspace::factory()->create();
        $this->workspace->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);
    });

    it('can login and view the dashboard with all sections', function () {
        // Login
        $page = visit('/login');

        $page->fill('email', 'test@example.com')
             ->fill('password', 'password')
             ->click(__('pages::pages.login.submit'))
             ->assertPathContains('/hub');

        // Verify dashboard title and subtitle (from translations)
        $page->assertSee(__('hub::hub.dashboard.title'))
             ->assertSee(__('hub::hub.dashboard.subtitle'));

        // Verify action button
        $page->assertSee(__('hub::hub.dashboard.actions.edit_content'));

        // Check activity section
        $page->assertSee(__('hub::hub.dashboard.sections.recent_activity'));

        // Check quick actions section
        $page->assertSee(__('hub::hub.quick_actions.manage_workspaces.title'))
             ->assertSee(__('hub::hub.quick_actions.profile.title'));
    });
});
