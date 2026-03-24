<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Host Hub Routes
|--------------------------------------------------------------------------
|
| Core routes for the Host Hub admin/customer panel.
| Note: The 'hub' prefix and 'hub.' name prefix are added by Boot.php
|
*/

Route::get('/', \Website\Hub\View\Modal\Admin\Dashboard::class)->name('dashboard');
Route::redirect('/dashboard', '/hub')->name('dashboard.redirect');
Route::get('/content/{workspace}/{type}', \Website\Hub\View\Modal\Admin\Content::class)->name('content')
    ->where('type', 'posts|pages|media');
Route::get('/content-manager/{workspace}/{view?}', \Website\Hub\View\Modal\Admin\ContentManager::class)->name('content-manager')
    ->where('view', 'dashboard|kanban|calendar|list|webhooks');
Route::get('/content-editor/{workspace}/new/{contentType?}', \Website\Hub\View\Modal\Admin\ContentEditor::class)->name('content-editor.create');
Route::get('/content-editor/{workspace}/{id}', \Website\Hub\View\Modal\Admin\ContentEditor::class)->name('content-editor.edit')
    ->where('id', '[0-9]+');
// /hub/workspaces redirects to current workspace settings (workspace switcher handles selection)
Route::get('/workspaces', \Website\Hub\View\Modal\Admin\Sites::class)->name('sites');
Route::redirect('/sites', '/hub/workspaces');
Route::get('/console', \Website\Hub\View\Modal\Admin\Console::class)->name('console');
Route::get('/databases', \Website\Hub\View\Modal\Admin\Databases::class)->name('databases');
// Account section
Route::get('/account', \Website\Hub\View\Modal\Admin\Profile::class)->name('account');
Route::get('/account/settings', \Website\Hub\View\Modal\Admin\Settings::class)->name('account.settings');
Route::get('/account/usage', \Website\Hub\View\Modal\Admin\AccountUsage::class)->name('account.usage');
Route::redirect('/profile', '/hub/account');
Route::redirect('/settings', '/hub/account/settings');
Route::redirect('/usage', '/hub/account/usage');
Route::redirect('/boosts', '/hub/account/usage?tab=boosts');
Route::redirect('/ai-services', '/hub/account/usage?tab=ai');
// Route::get('/config/{path?}', \Core\Config\View\Modal\Admin\WorkspaceConfig::class)
//     ->where('path', '.*')
//     ->name('workspace.config');
// Route::redirect('/workspace/config', '/hub/config');
Route::get('/workspaces/{workspace}/{tab?}', \Website\Hub\View\Modal\Admin\SiteSettings::class)
    ->where('tab', 'services|general|deployment|environment|ssl|backups|danger')
    ->name('sites.settings');
Route::get('/deployments', \Website\Hub\View\Modal\Admin\Deployments::class)->name('deployments');
Route::get('/platform', \Website\Hub\View\Modal\Admin\Platform::class)->name('platform');
Route::get('/platform/user/{id}', \Website\Hub\View\Modal\Admin\PlatformUser::class)->name('platform.user')
    ->where('id', '[0-9]+');
Route::get('/prompts', \Website\Hub\View\Modal\Admin\PromptManager::class)->name('prompts');

// Entitlement management (admin only)
Route::get('/entitlements', \Website\Hub\View\Modal\Admin\Entitlement\Dashboard::class)->name('entitlements');
Route::get('/entitlements/packages', \Website\Hub\View\Modal\Admin\Entitlement\PackageManager::class)->name('entitlements.packages');
Route::get('/entitlements/features', \Website\Hub\View\Modal\Admin\Entitlement\FeatureManager::class)->name('entitlements.features');

// Waitlist management (admin only - Hades tier)
Route::get('/admin/waitlist', \Website\Hub\View\Modal\Admin\WaitlistManager::class)->name('admin.waitlist');

// Workspace management (admin only - Hades tier)
// Route::get('/admin/workspaces', \Core\Tenant\View\Modal\Admin\WorkspaceManager::class)->name('admin.workspaces');
// Route::get('/admin/workspaces/{id}', \Core\Tenant\View\Modal\Admin\WorkspaceDetails::class)->name('admin.workspaces.details')
//     ->where('id', '[0-9]+');

// Service management (admin only - Hades tier)
Route::get('/admin/services', \Website\Hub\View\Modal\Admin\ServiceManager::class)->name('admin.services');

// Services - workspace admin for Bio, Social, Analytics, Notify, Trust, Support, Commerce
Route::get('/services/{service?}/{tab?}', \Website\Hub\View\Modal\Admin\ServicesAdmin::class)
    ->where('service', 'bio|social|analytics|notify|trust|support|commerce')
    ->where('tab', 'dashboard|pages|channels|projects|accounts|posts|websites|goals|subscribers|campaigns|notifications|inbox|settings|orders|subscriptions|coupons')
    ->name('services');

// Security - Honeypot monitoring
Route::get('/honeypot', \Website\Hub\View\Modal\Admin\Honeypot::class)->name('honeypot');
